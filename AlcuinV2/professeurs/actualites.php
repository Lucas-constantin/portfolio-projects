<?php
session_start();
if (!isset($_SESSION['id_prof'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$actualites = $conn->query("SELECT * FROM actualites ORDER BY date_publication DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualités</title>
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
            <li><a href="accueil.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏠</span><span class="hidden group-hover:inline">Accueil</span></a></li>
            <li><a href="actualites.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-white bg-blue-500 hover:bg-blue-600 transition"><span>📰</span><span class="hidden group-hover:inline">Actualités</span></a></li>
            <li><a href="agenda.html" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📅</span><span class="hidden group-hover:inline">Agenda</span></a></li>
            <li><a href="notes.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📝</span><span class="hidden group-hover:inline">Notes</span></a></li>
            <li><a href="correction_stage.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏢</span><span class="hidden group-hover:inline">Stage</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition"><span>🚪</span><span class="hidden group-hover:inline">Déconnexion</span></a></li>
        </ul>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-1 p-6 overflow-y-auto">
        <header class="bg-blue-600 text-white p-4 shadow text-center rounded-lg mb-6">
            <h1 class="text-2xl font-bold">Actualités</h1>
        </header>

        <section class="bg-white shadow-md rounded-lg p-6 max-w-4xl mx-auto text-lg">
            <?php if (!empty($actualites)): ?>
                <?php foreach ($actualites as $actu): ?>
                    <article class="border-b pb-4 mb-6">
                        <h2 class="text-xl font-semibold text-blue-700"><?= htmlspecialchars($actu['titre']) ?></h2>
                        <p class="text-gray-800 mt-2"><?= nl2br(htmlspecialchars($actu['contenu'])) ?></p>
                        <p class="text-sm text-gray-500 mt-2"><?= date('d/m/Y H:i', strtotime($actu['date_publication'])) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center">Aucune actualité disponible pour le moment.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>