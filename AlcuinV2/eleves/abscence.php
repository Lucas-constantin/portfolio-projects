<?php
session_start();
if (!isset($_SESSION['id_eleve'])) {
    header("Location: ../index.php");
    exit();
}
$id_eleve = $_SESSION['id_eleve'];      //Recuperation de l'id de l'élève depuis index.php
require_once '../includes/db.php';

//Requête sql
$sql = "SELECT date_absence, motif, justifie FROM absences WHERE id_eleve = ? ORDER BY date_absence DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_eleve);
$stmt->execute();
$result = $stmt->get_result();

//On ajoute dans une liste toutes les absences
$absences = [];
while ($row = $result->fetch_assoc()) {
    $absences[] = $row;
}

//Fermeture de la requête et de la connexion a la bdd
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absences</title>
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
            <li><a href="abscence.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-white bg-blue-500 hover:bg-blue-600 transition"><span>🚫</span><span class="hidden group-hover:inline">Absences</span></a></li>
            <li><a href="notes.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📝</span><span class="hidden group-hover:inline">Notes</span></a></li>
            <li><a href="stage.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏢</span><span class="hidden group-hover:inline">Stage</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition"><span>🚪</span><span class="hidden group-hover:inline">Déconnexion</span></a></li>
        </ul>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-1 p-6 overflow-y-auto">
        <header class="bg-blue-600 text-white shadow p-4 text-center rounded-lg mb-6">
            <h1 class="text-2xl font-bold">Mes Absences</h1>
        </header>

        <section class="bg-white shadow-md rounded-lg p-6 max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold mb-4">Historique des absences</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border">Date</th>
                            <th class="px-4 py-2 border">Motif</th>
                            <th class="px-4 py-2 border">Justifiée</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($absences) > 0):
                            foreach ($absences as $absence): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border text-center"><?= htmlspecialchars($absence['date_absence']) ?></td>
                                    <td class="px-4 py-2 border text-center"><?= htmlspecialchars($absence['motif'] ?? 'Non spécifié') ?></td>
                                    <td class="px-4 py-2 border text-center">
                                    <?= $absence['justifie'] ? 'Oui' : 'Non' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-4">Aucune absence enregistrée.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>