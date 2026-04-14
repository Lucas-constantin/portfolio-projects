<?php
session_start();
$id_eleve = $_SESSION['id_eleve'];
$conn = new mysqli("localhost:3307", "root", "", "ma_base");
if (!isset($_SESSION['id_eleve'])) {
    header("Location: ../index.php");
    exit();
}
//Suppression d’un rapport
$uploadMessage = "";
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    //On vérifie que le rapport appartient bien à l’élève
    $stmt = $conn->prepare("SELECT fichier_pdf FROM stages WHERE id = ? AND id_eleve = ?");
    $stmt->bind_param('ii', $id_to_delete, $id_eleve);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (file_exists($row['fichier_pdf'])) {
            unlink($row['fichier_pdf']); //Supprime le fichier PDF
        }

        //Supprime aussi dans la base
        $stmt_del = $conn->prepare("DELETE FROM stages WHERE id = ?");
        $stmt_del->bind_param('i', $id_to_delete);
        $stmt_del->execute();
        $stmt_del->close();

        $uploadMessage = "✅ Rapport supprimé avec succès.";
    }
    $stmt->close();
}

//Envoi d’un rapport
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['rapport'])) {
    $fichier = $_FILES['rapport'];

    if ($fichier['error'] === 0 && pathinfo($fichier['name'], PATHINFO_EXTENSION) === 'pdf') {    //Il faut que se soit un pdf
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $nomFichier = uniqid('rapport_') . '.pdf';
        $cheminComplet = $uploadDir . $nomFichier;

        if (move_uploaded_file($fichier['tmp_name'], $cheminComplet)) {    //Ajout dans la bdd
            $stmt = $conn->prepare("INSERT INTO stages (id_eleve, fichier_pdf) VALUES (?, ?)");
            $stmt->bind_param('is', $id_eleve, $cheminComplet);
            $stmt->execute();
            $stmt->close();

            $uploadMessage = "✅ Rapport déposé avec succès.";
        } else {
            $uploadMessage = "❌ Erreur lors du dépôt du fichier.";
        }
    } else {
        $uploadMessage = "❌ Seuls les fichiers PDF sont autorisés.";
    }
}

//Récupération des rapports
$stages = [];
$stmt = $conn->prepare("SELECT id, fichier_pdf, commentaire_prof, date_depot FROM stages WHERE id_eleve = ? ORDER BY date_depot DESC");
$stmt->bind_param('i', $id_eleve);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stages[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 text-gray-900 h-screen overflow-hidden">
    <!-- Barre de navigation latérale -->
    <nav class="group flex flex-col bg-blue-600 text-white w-20 hover:w-64 transition-all duration-300 overflow-hidden shadow-lg">
        <div class="p-4 flex items-center space-x-4">
            <img src="logo_esaip.jpg" alt="ESAIP" class="w-10 h-10 rounded-full">
            <span class="text-xl font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 delay-150 hidden group-hover:inline transition-opacity duration-300">Portail Élève</span>
        </div>
        <ul class="mt-6 space-y-2 px-2">
            <li><a href="accueil.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏠</span><span class="hidden group-hover:inline">Accueil</span></a></li>
            <li><a href="actualites.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📰</span><span class="hidden group-hover:inline">Actualités</span></a></li>
            <li><a href="agenda.html" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📅</span><span class="hidden group-hover:inline">Agenda</span></a></li>
            <li><a href="abscence.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🚫</span><span class="hidden group-hover:inline">Absences</span></a></li>
            <li><a href="notes.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📝</span><span class="hidden group-hover:inline">Notes</span></a></li>
            <li><a href="stage.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏢</span><span class="hidden group-hover:inline">Stage</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition"><span>🚪</span><span class="hidden group-hover:inline">Déconnexion</span></a></li>
        </ul>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-1 p-6 overflow-y-auto">
        <header class="bg-blue-600 text-white p-4 shadow text-center rounded-lg mb-6">
            <h1 class="text-2xl font-bold">Déposer mon rapport de stage</h1>
        </header>
        <section class="bg-white shadow-md rounded-lg p-6 max-w-4xl mx-auto">
            <?php if (!empty($uploadMessage)): ?>
                <div class="mb-6 p-4 rounded bg-blue-100 text-blue-800 font-medium shadow">
                    <?= $uploadMessage ?>
                </div>
            <?php endif; ?>

            <form action="stage.php" method="POST" enctype="multipart/form-data" class="mb-8">
                <label class="block mb-4">
                    <span class="text-gray-700 font-medium">Fichier PDF :</span>
                    <input type="file" name="rapport" accept=".pdf" required class="block w-full mt-2 border rounded p-2">
                </label>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">📤 Envoyer</button>
            </form>

            <h2 class="text-xl font-semibold mb-4">Mes rapports déposés</h2>

            <?php if (count($stages) > 0): ?>
                <ul class="space-y-4">
                    <?php foreach ($stages as $stage): ?>        <!--Affichage des rapports de stage déposés-->
                        <li class="bg-gray-50 border p-4 rounded shadow hover:shadow-md transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">📅 <strong>Date :</strong> <?= $stage['date_depot'] ?></p>
                                    <p><a href="<?= $stage['fichier_pdf'] ?>" target="_blank" class="text-blue-600 hover:underline font-medium">📄 Voir le rapport</a></p>
                                    <?php if ($stage['commentaire_prof']): ?>
                                        <p class="mt-2 text-green-700 font-medium">📝 <?= htmlspecialchars($stage['commentaire_prof']) ?></p>
                                    <?php else: ?>
                                        <p class="mt-2 text-gray-600 italic">En attente de correction.</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="stage.php?delete=<?= $stage['id'] ?>"
                                       class="text-red-600 hover:text-red-800 font-semibold flex items-center gap-1"
                                       onclick="return confirm('Supprimer ce rapport ?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-600">Aucun rapport déposé pour l’instant.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>