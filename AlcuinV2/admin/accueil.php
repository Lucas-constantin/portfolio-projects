<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <header class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Bienvenue sur le tableau de bord administrateur</h1>
        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Déconnexion</a>
    </header>

    <div class="grid gap-6">
        <a href="gestion_absences.php" class="bg-white p-4 rounded shadow hover:bg-gray-200">📋 Gérer les absences</a>
        <a href="gestion_actualite.php" class="bg-white p-4 rounded shadow hover:bg-gray-200">📰 Gérer les actualités</a>
        <a href="gestion_agenda.php" class="bg-white p-4 rounded shadow hover:bg-gray-200">🗓️ Gérer l'agenda</a>
        <a href="gestion_utilisateurs.php" class="bg-white p-4 rounded shadow hover:bg-gray-200">👥 Gérer les utilisateurs</a>
        <a href="gestion_classes.php" class="bg-white p-4 rounded shadow hover:bg-gray-200">🏫 Gérer les classes</a>
        <a href="gestion_matieres.php" class="bg-white p-4 rounded shadow hover:bg-gray-200">📘 Gérer les matières</a>
    </div>
</body>
</html>