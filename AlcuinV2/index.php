<?php
session_start();

$erreur = "";

$host = 'localhost:3307';
$user = 'root';
$password = '';
$dbname = 'ma_base';

$conn = new mysqli($host, $user, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if ($password === $user['passwd']) { // À sécuriser avec password_hash à terme
                $userId = $user['id'];
                $role = $user['role'];

                switch ($role) {
                    case 'eleve':
                        $stmt2 = $conn->prepare("SELECT id FROM eleves WHERE id_user = ?");
                        $stmt2->bind_param("i", $userId);
                        $stmt2->execute();
                        $res2 = $stmt2->get_result();
                        if ($eleve = $res2->fetch_assoc()) {
                            $_SESSION['id_eleve'] = $eleve['id'];
                            $_SESSION['role'] = 'eleve';
                            header("Location: eleves/accueil.php");
                            exit();
                        } else {
                            $erreur = "Élève introuvable.";
                        }
                        break;

                    case 'professeur':
                        $stmt2 = $conn->prepare("SELECT id FROM professeur WHERE id_user = ?");
                        $stmt2->bind_param("i", $userId);
                        $stmt2->execute();
                        $res2 = $stmt2->get_result();
                        if ($prof = $res2->fetch_assoc()) {
                            $_SESSION['id_prof'] = $prof['id'];
                            $_SESSION['role'] = 'professeur';
                            header("Location: professeurs/accueil.php");
                            exit();
                        } else {
                            $erreur = "Professeur introuvable.";
                        }
                        break;

                    case 'admin':
                        $_SESSION['id_admin'] = $user['id'];
                        $_SESSION['role'] = 'admin';
                        header("Location: admin/accueil.php");
                        exit();

                    default:
                        $erreur = "Rôle inconnu.";
                }
            } else {
                $erreur = "Mot de passe incorrect.";
            }
        } else {
            $erreur = "Utilisateur introuvable.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form method="POST" class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-2xl font-bold mb-4 text-center">Connexion</h2>

        <?php if ($erreur): ?>
            <p class="text-red-600 text-sm mb-4"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <label class="block mb-2 text-sm font-medium">Nom d'utilisateur</label>
        <input type="text" name="username" class="w-full p-2 border rounded mb-4" required>

        <label class="block mb-2 text-sm font-medium">Mot de passe</label>
        <input type="password" name="password" class="w-full p-2 border rounded mb-4" required>

        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Se connecter</button>
    </form>
</body>
</html>