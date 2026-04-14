<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

//Ajouter une actualité
if (isset($_POST['ajouter']) && !empty($_POST['titre']) && !empty($_POST['contenu'])) {
    $titre = htmlspecialchars($_POST['titre']);
    $contenu = htmlspecialchars($_POST['contenu']);

    $stmt = $conn->prepare("INSERT INTO actualites (titre, contenu) VALUES (?, ?)");
    $stmt->bind_param("ss", $titre, $contenu);
    $stmt->execute();
    $stmt->close();
}

//Supprimer une actualité
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $conn->query("DELETE FROM actualites WHERE id = $id");
}

//Récupération des actualités
$actualites = $conn->query("SELECT * FROM actualites ORDER BY date_publication DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des actualités</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <main class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-6">
            <a href="accueil.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">← Retour à l'accueil</a>
            <h1 class="text-2xl font-bold mb-6">Gestion des actualités</h1>
        </div>

        <!--Formulaire pour ajouter-->
        <form method="POST" class="space-y-4 mb-6">
            <input type="text" name="titre" placeholder="Titre de l'actualité" required class="w-full p-2 border rounded">
            <textarea name="contenu" placeholder="Contenu..." rows="5" required class="w-full p-2 border rounded"></textarea>
            <button type="submit" name="ajouter" class="bg-blue-600 text-white px-4 py-2 rounded">Ajouter</button>
        </form>

        <!--Liste des actualités-->
        <h2 class="text-xl font-semibold mb-4">Liste des actualités</h2>
        <ul>
            <?php foreach ($actualites as $actu): ?>
                <li class="border-b py-4">
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($actu['titre']) ?></h3>
                    <p class="text-gray-700"><?= nl2br(htmlspecialchars($actu['contenu'])) ?></p>
                    <p class="text-sm text-gray-500 mt-2"><?= date('d/m/Y H:i', strtotime($actu['date_publication'])) ?></p>
                    <a href="?supprimer=<?= $actu['id'] ?>" class="text-red-600 hover:underline text-sm" onclick="return confirm('Supprimer cette actualité ?')">Supprimer</a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($actualites)): ?>
                <li class="text-gray-500">Aucune actualité enregistrée.</li>
            <?php endif; ?>
        </ul>
    </main>
</body>
</html>