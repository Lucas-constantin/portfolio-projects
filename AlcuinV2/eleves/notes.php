<?php
session_start();
if (!isset($_SESSION['id_eleve'])) {
    header("Location: ../index.php");
    exit();
}
$id_eleve = $_SESSION['id_eleve'];
$conn = new mysqli("localhost:3307", "root", "", "ma_base");

$sql = "SELECT m.matiere, n.note
        FROM notes n
        JOIN matieres m ON n.id_matiere = m.id
        WHERE n.id_eleve = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_eleve);
$stmt->execute();
$result = $stmt->get_result();

//Tableau de toutes les notes par matières
$notes_par_matiere = [];
while ($row = $result->fetch_assoc()) {
    $notes_par_matiere[$row['matiere']][] = $row['note'];
}

$stmt->close();
$conn->close();

//On cherche la matière avec le plus de notes
$max_notes = 0;
foreach ($notes_par_matiere as $notes) {
    $max_notes = max($max_notes, count($notes));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Notes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <li><a href="notes.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-white bg-blue-500 hover:bg-blue-600 transition"><span>📝</span><span class="hidden group-hover:inline">Notes</span></a></li>
            <li><a href="stage.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏢</span><span class="hidden group-hover:inline">Stage</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition"><span>🚪</span><span class="hidden group-hover:inline">Déconnexion</span></a></li>
        </ul>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-1 p-6 overflow-y-auto">
        <header class="bg-blue-600 text-white text-center py-4 rounded-lg shadow mb-6">
            <h1 class="text-3xl font-bold">Mes Notes</h1>
        </header>

        <section class="bg-white p-6 rounded-lg shadow max-w-5xl mx-auto">
            <h2 class="text-2xl font-semibold mb-4 text-blue-700">Notes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border border-gray-300 rounded-md">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border px-4 py-2 text-left">Matière</th>
                            <?php for ($i = 1; $i <= $max_notes; $i++): ?>    <!--On fait autant de colone que de notes-->
                                <th class="border px-4 py-2 text-center">Note <?= $i ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notes_par_matiere as $matiere => $notes): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border px-4 py-2 font-medium"><?= htmlspecialchars($matiere) ?></td>    <!--Nom de la matière-->
                                <?php for ($i = 0; $i < $max_notes; $i++): ?>
                                    <td class="border px-4 py-2 text-center">
                                        <?= isset($notes[$i]) ? htmlspecialchars($notes[$i]) . ' / 20' : '-' ?>    <!--On affiche la note ou '-'si il n'y en a pas-->
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>