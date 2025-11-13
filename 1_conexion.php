<?php
$host = "localhost";
$user = "root";     // cambia si tienes otro usuario
$pass = "";         // cambia si tu MySQL tiene contraseña
$db   = "sistema_login";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}
?>
