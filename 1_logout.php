<?php
session_start();
date_default_timezone_set("America/Belize"); // misma zona horaria que el login
include("1_conexion.php"); // conexión en $conn

if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    $accion = "Cierre de sesion";
    $fecha = date("Y-m-d H:i:s");

    // Guardar cierre de sesión en bitácora
    $bit = $conn->prepare("INSERT INTO bitacora (usuario, accion, fecha) VALUES (?, ?, ?)");
    $bit->bind_param("sss", $usuario, $accion, $fecha);
    $bit->execute();
    $bit->close();
}

// Destruir sesión
session_destroy();

// Redirigir al login
header("Location: 1_login.php");
exit;
