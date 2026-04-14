<?php
session_start();
if (!isset($_SESSION['id_prof'])) {
    header("Location: ../index.php");
    exit();
}
$id_prof = $_SESSION['id_prof'];

require_once '../includes/db.php';
$matiereChoisie = isset($_POST['matiere']) ? intval($_POST['matiere']) : (isset($_POST['matiere_submit']) ? intval($_POST['matiere_submit']) : null);
$message = "";

//On établi chaque action action
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' && isset($_POST['eleve'], $_POST['note'], $_POST['matiere_submit'])) {
        $id_eleve = intval($_POST['eleve']);
        $note = intval($_POST['note']);
        $id_matiere = intval($_POST['matiere_submit']);

        $stmt = $conn->prepare("INSERT INTO notes (note, id_matiere, id_prof, id_eleve) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $note, $id_matiere, $id_prof, $id_eleve);
        if ($stmt->execute()) {
            $message = "✅ Note ajoutée avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout de la note.";
        }
        $stmt->close();
    }

    if ($action === 'update' && isset($_POST['note_id'], $_POST['note'])) {
        $note_id = intval($_POST['note_id']);
        $new_note = intval($_POST['note']);
        $stmt = $conn->prepare("UPDATE notes SET note = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_note, $note_id);
        if ($stmt->execute()) {
            $message = "✅ Note modifiée.";
        } else {
            $message = "❌ Échec de la modification.";
        }
        $stmt->close();
    }

    if ($action === 'delete' && isset($_POST['note_id'])) {
        $note_id = intval($_POST['note_id']);
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->bind_param("i", $note_id);
        if ($stmt->execute()) {
            $message = "✅ Note supprimée.";
        } else {
            $message = "❌ Échec de la suppression.";
        }
        $stmt->close();
    }
}

//Récupération des matières
$stmt = $conn->prepare("
    SELECT DISTINCT m.id, m.matiere 
    FROM matieres m
    JOIN classe_prof_matiere cpm ON cpm.id_matiere = m.id
    WHERE cpm.id_prof = ?");
$stmt->bind_param("i", $id_prof);
$stmt->execute();
$matiere_result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
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
            <li><a href="actualites.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📰</span><span class="hidden group-hover:inline">Actualités</span></a></li>
            <li><a href="agenda.html" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>📅</span><span class="hidden group-hover:inline">Agenda</span></a></li>
            <li><a href="notes.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-white bg-blue-500 hover:bg-blue-600 transition"><span>📝</span><span class="hidden group-hover:inline">Notes</span></a></li>
            <li><a href="correction_stage.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg border-l-4 border-transparent hover:border-white bg-blue-500 hover:bg-blue-600 transition"><span>🏢</span><span class="hidden group-hover:inline">Stage</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition"><span>🚪</span><span class="hidden group-hover:inline">Déconnexion</span></a></li>
        </ul>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-1 p-6 overflow-y-auto">
        <header class="bg-blue-600 text-white p-4 shadow text-center rounded-lg mb-6">
            <h1 class="text-2xl font-bold">Notes</h1>
        </header>

        <section class="bg-white shadow-md rounded-lg p-6 max-w-4xl mx-auto">
            <?php if ($message): ?>
                <div class="text-center text-green-600 font-semibold mb-4"><?= $message ?></div>
            <?php endif; ?>

            <!--Formulaire de selection des matière-->
            <form method="POST" class="mb-6">
                <label for="matiere" class="block text-sm font-medium text-gray-700 mb-1">Choisir une matière :</label>
                <select name="matiere" onchange="this.form.submit()" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm">
                    <option value="">-- Sélectionner --</option>
                    <?php while ($row = $matiere_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($matiereChoisie == $row['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['matiere']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>

            <?php if ($matiereChoisie): ?>
                <!--Formulaire pour ajouter une note-->
                <form method="POST" class="mb-6">
                    <input type="hidden" name="matiere_submit" value="<?= $matiereChoisie ?>">
                    <input type="hidden" name="action" value="add">

                    <label for="eleve" class="block text-sm font-medium text-gray-700 mb-1">Choisir un élève :</label>
                    <select name="eleve" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm mb-4">
                        <?php
                        $stmt = $conn->prepare("SELECT e.id, e.prenom, e.nom FROM eleves e
                            JOIN classes c ON e.classe = c.classe
                            JOIN classe_prof_matiere cpm ON c.id = cpm.id_classe
                            WHERE cpm.id_prof = ? AND cpm.id_matiere = ?");
                        $stmt->bind_param("ii", $id_prof, $matiereChoisie);
                        $stmt->execute();
                        $eleves = $stmt->get_result();
                        while ($eleve = $eleves->fetch_assoc()):
                        ?>
                            <option value="<?= $eleve['id'] ?>"><?= htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']) ?></option>
                        <?php endwhile; $stmt->close(); ?>
                    </select>

                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note :</label>
                    <input type="number" name="note" min="0" max="20" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm mb-4">

                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow hover:bg-blue-700">Ajouter la note</button>
                </form>

                <!--Liste des notes existantes-->
                <h2 class="text-lg font-semibold mb-2">Notes attribuées :</h2>
                <table class="w-full border border-gray-300 rounded-lg text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">Élève</th>
                            <th class="p-2 border">Note</th>
                            <th class="p-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT n.id as note_id, n.note, e.nom, e.prenom FROM notes n
                        JOIN eleves e ON e.id = n.id_eleve
                        WHERE n.id_matiere = ? AND n.id_prof = ?");
                    $stmt->bind_param("ii", $matiereChoisie, $id_prof);
                    $stmt->execute();
                    $notes = $stmt->get_result();
                    while ($n = $notes->fetch_assoc()):
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-2 border"><?= htmlspecialchars($n['prenom'] . ' ' . $n['nom']) ?></td>
                            <td class="p-2 border">
                                <form method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="note_id" value="<?= $n['note_id'] ?>">
                                    <input type="hidden" name="matiere_submit" value="<?= $matiereChoisie ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="number" name="note" value="<?= $n['note'] ?>" min="0" max="20" class="w-16 p-1 border rounded">
                                    <button class="text-blue-600 font-semibold hover:underline">Modifier</button>
                                </form>
                            </td>
                            <td class="p-2 border text-center">
                                <form method="POST" onsubmit="return confirm('Supprimer cette note ?')">
                                    <input type="hidden" name="note_id" value="<?= $n['note_id'] ?>">
                                    <input type="hidden" name="matiere_submit" value="<?= $matiereChoisie ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="text-red-600 font-semibold hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; $stmt->close(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>