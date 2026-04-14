<?php
$conn = new mysqli("localhost:3307", "root", "", "ma_base");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>