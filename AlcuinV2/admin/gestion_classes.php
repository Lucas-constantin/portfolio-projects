<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

//Ajouter une classe
if (isset($_POST['ajouter_classe']) && !empty($_POST['classe'])) {
    $classe = htmlspecialchars(trim($_POST['classe']));

    //On vérifie que la classe n'existe pas déjà
    $verif = $conn->prepare("SELECT id FROM classes WHERE classe = ?");
    $verif->bind_param("s", $classe);
    $verif->execute();
    $verif->store_result();

    if ($verif->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO classes (classe) VALUES (?)");
        $stmt->bind_param("s", $classe);
        $stmt->execute();
        $stmt->close();
    }

    $verif->close();
}

//Supprimer une classe
if (isset($_GET['supprimer_classe'])) {
    $id_classe = intval($_GET['supprimer_classe']);
    $conn->query("DELETE FROM classe_prof_matiere WHERE id_classe = $id_classe");
    $conn->query("DELETE FROM classes WHERE id = $id_classe");
}

//Ajouter une association
if (isset($_POST['associer']) && !empty($_POST['classe_assoc']) && !empty($_POST['prof']) && !empty($_POST['matiere'])) {
    $id_classe = intval($_POST['classe_assoc']);
    $id_prof = intval($_POST['prof']);
    $id_matiere = intval($_POST['matiere']);

    $stmt = $conn->prepare("INSERT INTO classe_prof_matiere (id_classe, id_prof, id_matiere) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_classe, $id_prof, $id_matiere);
    $stmt->execute();
    $stmt->close();
}

//Supprimer une association
if (isset($_GET['supprimer_association'])) {
    $id = intval($_GET['supprimer_association']);
    $conn->query("DELETE FROM classe_prof_matiere WHERE id = $id");
}

//Récupération des données
$classes = $conn->query("SELECT * FROM classes")->fetch_all(MYSQLI_ASSOC);
$professeurs = $conn->query("SELECT id, prenom, nom FROM professeur")->fetch_all(MYSQLI_ASSOC);
$matieres = $conn->query("SELECT id, matiere FROM matieres")->fetch_all(MYSQLI_ASSOC);
$associations = $conn->query("
    SELECT cpm.id, classes.classe, professeur.prenom, professeur.nom, matieres.matiere
    FROM classe_prof_matiere cpm
    JOIN classes ON cpm.id_classe = classes.id
    JOIN professeur ON cpm.id_prof = professeur.id
    JOIN matieres ON cpm.id_matiere = matieres.id
    ORDER BY classes.classe
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <main class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-6">
            <a href="accueil.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">← Retour à l'accueil</a>
            <h1 class="text-2xl font-bold mb-6">Gestion des classes</h1>
        </div>

        <!--Formulaire ajouter classe-->
        <form method="POST" class="flex gap-4 items-center mb-6">
            <input type="text" name="classe" placeholder="Nom de la classe" required class="p-2 border rounded w-1/2">
            <button name="ajouter_classe" class="bg-blue-600 text-white px-4 py-2 rounded">Ajouter classe</button>
        </form>

        <!--Liste des classes-->
        <h2 class="text-xl font-semibold mb-2">Liste des classes</h2>
        <ul class="mb-6">
            <?php foreach ($classes as $c): ?>
                <li class="flex justify-between items-center border-b py-2">
                    <span><?= htmlspecialchars($c['classe']) ?></span>
                    <a href="?supprimer_classe=<?= $c['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Supprimer cette classe ?')">Supprimer</a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($classes)): ?>
                <li class="text-gray-500">Aucune classe enregistrée.</li>
            <?php endif; ?>
        </ul>

        <!--Formulaire d'association-->
        <h2 class="text-xl font-semibold mb-2">Associer professeurs et matières aux classes</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <select name="classe_assoc" required class="p-2 border rounded">
                <option value="">Classe</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['classe']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="prof" required class="p-2 border rounded">
                <option value="">Professeur</option>
                <?php foreach ($professeurs as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="matiere" required class="p-2 border rounded">
                <option value="">Matière</option>
                <?php foreach ($matieres as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['matiere']) ?></option>
                <?php endforeach; ?>
            </select>

            <button name="associer" class="bg-green-600 text-white px-4 py-2 rounded">Associer</button>
        </form>

        <!--Associations-->
        <h2 class="text-xl font-semibold mb-2">Associations existantes</h2>
        <table class="w-full table-auto border mb-6">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">Classe</th>
                    <th class="p-2">Professeur</th>
                    <th class="p-2">Matière</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($associations as $a): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= htmlspecialchars($a['classe']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($a['matiere']) ?></td>
                        <td class="p-2">
                            <a href="?supprimer_association=<?= $a['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Supprimer cette association ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($associations)): ?>
                    <tr><td colspan="4" class="text-center text-gray-500 p-2">Aucune association trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>