<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

$success = "";
$error = "";

//Ajouter un utilisateur
if (isset($_POST['ajouter'])) {
    $username = $_POST['username'];
    $passwd = $_POST['passwd'];
    $role = $_POST['role'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $classe = $_POST['classe'] ?? null;

    $stmt = $conn->prepare("INSERT INTO user (username, passwd, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $passwd, $role);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        if ($role === 'eleve') {
            $stmt2 = $conn->prepare("INSERT INTO eleves (id_user, prenom, nom, classe) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $prenom, $nom, $classe);
        } elseif ($role === 'professeur') {
            $stmt2 = $conn->prepare("INSERT INTO professeur (id_user, prenom, nom) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $prenom, $nom);
        }
        if (isset($stmt2) && $stmt2->execute()) {
            $success = "Utilisateur ajouté avec succès.";
        } else {
            $error = "Erreur lors de l'ajout dans la table associée.";
        }
    } else {
        $error = "Erreur lors de l'ajout de l'utilisateur.";
    }
}

//Modification élève
if (isset($_POST['update_eleve'])) {
    $id = $_POST['id'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $classe = $_POST['classe'];

    $stmt = $conn->prepare("UPDATE eleves SET prenom = ?, nom = ?, classe = ? WHERE id = ?");
    $stmt->bind_param("sssi", $prenom, $nom, $classe, $id);
    if ($stmt->execute()) $success = "Élève mis à jour.";
    else $error = "Erreur lors de la mise à jour.";
}

//Modification professeur
if (isset($_POST['update_prof'])) {
    $id = $_POST['id'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];

    $stmt = $conn->prepare("UPDATE professeur SET prenom = ?, nom = ? WHERE id = ?");
    $stmt->bind_param("ssi", $prenom, $nom, $id);
    if ($stmt->execute()) $success = "Professeur mis à jour.";
    else $error = "Erreur lors de la mise à jour.";
}

//Suppression user
if (isset($_POST['delete_user'])) {
    $id_user = $_POST['id_user'];

    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    if ($stmt->execute()) $success = "Utilisateur supprimé.";
    else $error = "Erreur lors de la suppression.";
}

//Récupération users
$eleves = $conn->query("SELECT eleves.id, eleves.id_user, eleves.nom, eleves.prenom, eleves.classe FROM eleves");
$profs = $conn->query("SELECT professeur.id, professeur.id_user, professeur.nom, professeur.prenom FROM professeur");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="flex justify-between items-center mb-6">
        <a href="accueil.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">← Retour à l'accueil</a>
        <h1 class="text-3xl font-bold mb-6">Gestion des utilisateurs</h1>
    </div>

    <?php if ($success): ?>
        <p class="text-green-600 mb-4"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="text-red-600 mb-4"><?= $error ?></p>
    <?php endif; ?>

    <!--Formulaire ajouter user-->
    <form method="POST" class="grid grid-cols-6 gap-4 bg-white p-4 rounded shadow mb-8">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required class="border p-2 col-span-1">
        <input type="text" name="passwd" placeholder="Mot de passe" required class="border p-2 col-span-1">
        <select name="role" required class="border p-2 col-span-1" onchange="document.getElementById('classe').style.display = this.value === 'eleve' ? 'block' : 'none';">
            <option value="">-- Rôle --</option>
            <option value="eleve">Élève</option>
            <option value="professeur">Professeur</option>
        </select>
        <input type="text" name="prenom" placeholder="Prénom" required class="border p-2 col-span-1">
        <input type="text" name="nom" placeholder="Nom" required class="border p-2 col-span-1">
        <input type="text" name="classe" id="classe" placeholder="Classe (élève)" class="border p-2 col-span-1">
        <button name="ajouter" class="bg-blue-600 text-white p-2 rounded col-span-6">Ajouter</button>
    </form>

    <!--Liste élèves-->
    <h2 class="text-xl font-bold mb-2">Élèves</h2>
    <?php while ($eleve = $eleves->fetch_assoc()): ?>
        <form method="POST" class="grid grid-cols-6 gap-2 mb-2 bg-white p-2 rounded shadow">
            <input type="hidden" name="id_user" value="<?= $eleve['id_user'] ?>">
            <input type="hidden" name="id" value="<?= $eleve['id'] ?>">
            <input type="text" name="prenom" value="<?= htmlspecialchars($eleve['prenom']) ?>" class="border p-1">
            <input type="text" name="nom" value="<?= htmlspecialchars($eleve['nom']) ?>" class="border p-1">
            <input type="text" name="classe" value="<?= htmlspecialchars($eleve['classe']) ?>" class="border p-1">
            <button name="update_eleve" class="bg-green-500 text-white p-1 rounded">Mettre à jour</button>
            <button name="delete_user" value="1" onclick="return confirm('Supprimer cet utilisateur ?')" class="bg-red-600 text-white p-1 rounded">Supprimer</button>
        </form>
    <?php endwhile; ?>

    <!--Liste professeurs-->
    <h2 class="text-xl font-bold mt-6 mb-2">Professeurs</h2>
    <?php while ($prof = $profs->fetch_assoc()): ?>
        <form method="POST" class="grid grid-cols-5 gap-2 mb-2 bg-white p-2 rounded shadow">
            <input type="hidden" name="id_user" value="<?= $prof['id_user'] ?>">
            <input type="hidden" name="id" value="<?= $prof['id'] ?>">
            <input type="text" name="prenom" value="<?= htmlspecialchars($prof['prenom']) ?>" class="border p-1">
            <input type="text" name="nom" value="<?= htmlspecialchars($prof['nom']) ?>" class="border p-1">
            <button name="update_prof" class="bg-green-500 text-white p-1 rounded">Mettre à jour</button>
            <button name="delete_user" value="1" onclick="return confirm('Supprimer cet utilisateur ?')" class="bg-red-600 text-white p-1 rounded">Supprimer</button>
        </form>
    <?php endwhile; ?>
</body>
</html>