<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

//Ajouter une matière
if (isset($_POST['ajouter']) && !empty($_POST['nouvelle_matiere'])) {
    $nom = htmlspecialchars($_POST['nouvelle_matiere']);
    $stmt = $conn->prepare("INSERT INTO matieres (matiere) VALUES (?)");
    $stmt->bind_param("s", $nom);
    $stmt->execute();
    $stmt->close();
}

//Supprimer une matière
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $conn->prepare("DELETE FROM matieres WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

//Récupération des matières
$result = $conn->query("SELECT * FROM matieres");
$matieres = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des matières</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <main class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-6">
            <a href="accueil.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">← Retour à l'accueil</a>
            <h1 class="text-2xl font-bold mb-4">Gestion des matières</h1>
        </div>

        <form method="POST" class="mb-6">
            <input type="text" name="nouvelle_matiere" placeholder="Nom de la matière" required class="p-2 border rounded w-3/4">
            <button type="submit" name="ajouter" class="bg-blue-600 text-white px-4 py-2 rounded ml-2">Ajouter</button>
        </form>

        <table class="w-full table-auto">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 text-left">ID</th>
                    <th class="p-2 text-left">Nom</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matieres as $matiere): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= $matiere['id'] ?></td>
                        <td class="p-2"><?= htmlspecialchars($matiere['matiere']) ?></td>
                        <td class="p-2">
                            <a href="?supprimer=<?= $matiere['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Supprimer cette matière ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($matieres)): ?>
                    <tr><td colspan="3" class="p-2 text-center text-gray-500">Aucune matière trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>