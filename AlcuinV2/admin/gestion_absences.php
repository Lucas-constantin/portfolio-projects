<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}

$conn = new mysqli("localhost:3307", "root", "", "ma_base");

// Ajouter une absence
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_eleve'], $_POST['date_absence'], $_POST['motif'])) {
    $id_eleve = intval($_POST['id_eleve']);
    $date_absence = $_POST['date_absence'];
    $motif = htmlspecialchars($_POST['motif']);
    $justifie = isset($_POST['justifie']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO absences (id_eleve, date_absence, motif, justifie) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $id_eleve, $date_absence, $motif, $justifie);
    $stmt->execute();
    $stmt->close();
}

// Supprimer une absence
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM absences WHERE id = $id");
    header("Location: admin_absences.php");
    exit();
}

// Récupérer les élèves
$eleves = $conn->query("SELECT id, prenom, nom FROM eleves ORDER BY nom");

// Récupérer les absences
$absences = $conn->query("
    SELECT a.*, e.nom, e.prenom 
    FROM absences a 
    JOIN eleves e ON a.id_eleve = e.id 
    ORDER BY a.date_absence DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Absences</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex min-h-screen">
    <!-- Contenu principal -->
    <main class="flex-1 p-8">
        <div class="flex justify-between items-center mb-6">
            <a href="accueil.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">← Retour à l'accueil</a>
            <h1 class="text-2xl font-bold">Gestion des Absences</h1>
        </div>

        <form method="POST" class="bg-white p-6 rounded shadow-md mb-8">
            <h2 class="text-lg font-semibold mb-4">Ajouter une absence</h2>
            <label class="block mb-2">Élève</label>
            <select name="id_eleve" class="w-full p-2 border rounded mb-4" required>
                <option value="">-- Sélectionner --</option>
                <?php while ($e = $eleves->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></option>
                <?php endwhile; ?>
            </select>

            <label class="block mb-2">Date d'absence</label>
            <input type="date" name="date_absence" class="w-full p-2 border rounded mb-4" required>

            <label class="block mb-2">Motif</label>
            <input type="text" name="motif" class="w-full p-2 border rounded mb-4" required>

            <label class="inline-flex items-center mb-4">
                <input type="checkbox" name="justifie" class="mr-2">
                Absence justifiée
            </label>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Ajouter</button>
        </form>

        <h2 class="text-xl font-semibold mb-4">Liste des absences</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded shadow-md">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-2 text-left">Élève</th>
                        <th class="p-2 text-left">Date</th>
                        <th class="p-2 text-left">Motif</th>
                        <th class="p-2 text-left">Justifiée</th>
                        <th class="p-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($a = $absences->fetch_assoc()): ?>
                        <tr class="border-t">
                            <td class="p-2"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></td>
                            <td class="p-2"><?= $a['date_absence'] ?></td>
                            <td class="p-2"><?= htmlspecialchars($a['motif']) ?></td>
                            <td class="p-2"><?= $a['justifie'] ? '✅ Oui' : '❌ Non' ?></td>
                            <td class="p-2 text-center">
                                <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Supprimer cette absence ?')" class="text-red-500 hover:underline">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>