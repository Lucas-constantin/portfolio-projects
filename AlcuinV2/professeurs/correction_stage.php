<?php
session_start();
if (!isset($_SESSION['id_prof'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

//Supprimer un rapport
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT fichier_pdf FROM stages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($fichier);
    if ($stmt->fetch() && file_exists($fichier)) unlink($fichier);
    $stmt->close();

    $conn->query("DELETE FROM stages WHERE id = $id");
    header("Location: correction.php");
    exit();
}

//Modifier un commentaire
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['commentaire'], $_POST['id_stage'])) {
    $id_stage = intval($_POST['id_stage']);
    $commentaire = htmlspecialchars($_POST['commentaire']);
    $stmt = $conn->prepare("UPDATE stages SET commentaire_prof = ? WHERE id = ?");
    $stmt->bind_param("si", $commentaire, $id_stage);
    $stmt->execute();
    $stmt->close();
}

// Récupération des rapports
$result = $conn->query("SELECT s.*, e.prenom, e.nom FROM stages s 
                        JOIN eleves e ON s.id_eleve = e.id
                        ORDER BY s.date_depot DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction des rapports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 text-gray-900 h-screen">
    <!-- Barre de navigation latérale -->
    <nav class="group flex flex-col bg-blue-600 text-white w-20 hover:w-64 transition-all duration-300 overflow-hidden shadow-lg">
        <div class="p-4 flex items-center space-x-4">
            <img src="logo_esaip.jpg" alt="ESAIP" class="w-10 h-10 rounded-full">
            <span class="text-xl font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 delay-150 hidden group-hover:inline transition-opacity duration-300">Portail Prof</span>
        </div>
        <ul class="mt-6 space-y-2 px-2">
            <li><a href="accueil.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏠</span><span class="hidden group-hover:inline">Accueil</span></a></li>
            <li><a href="actualites.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📰</span><span class="hidden group-hover:inline">Actualités</span></a></li>
            <li><a href="agenda.html" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📅</span><span class="hidden group-hover:inline">Agenda</span></a></li>
            <li><a href="notes.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📝</span><span class="hidden group-hover:inline">Notes</span></a></li>
            <li><a href="correction_stage.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏢</span><span class="hidden group-hover:inline">Stage</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition"><span>🚪</span><span class="hidden group-hover:inline">Déconnexion</span></a></li>
        </ul>
    </nav>

    <main class="flex-1 p-6 overflow-y-auto">
        <header class="bg-blue-700 text-white p-4 shadow rounded-lg mb-6 text-center">
            <h2 class="text-2xl font-bold">Correction des rapports de stage</h2>
        </header>

        <section>
            <?php while ($row = $result->fetch_assoc()):
                $chemin_pdf_base = "../uploads/" . basename($row['fichier_pdf']);
            ?>
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <p class="mb-2 font-semibold">Élève : <?= htmlspecialchars($row['prenom'] . ' ' . $row['nom']) ?></p>
                    <p class="mb-2">Date de dépôt : <?= htmlspecialchars($row['date_depot']) ?></p>
                    <a href="<?= htmlspecialchars($row['fichier_pdf']) ?>" target="_blank" class="text-blue-600 underline mb-4 inline-block">📄 Voir le rapport</a>

                    <form method="POST" class="mb-2">
                        <input type="hidden" name="id_stage" value="<?= $row['id'] ?>">
                        <label class="block text-sm mb-1">Commentaire :</label>
                        <?php $commentaire = isset($row['commentaire_prof']) ? htmlspecialchars($row['commentaire_prof']) : ''; ?>
                        <textarea name="commentaire" rows="2" class="w-full p-2 border rounded mb-2"><?= $commentaire ?></textarea>
                        <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">💾 Enregistrer</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </section>
    </main>
</body>
</html>
