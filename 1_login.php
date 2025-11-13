<?php
session_start();
date_default_timezone_set("America/Belize"); // Hora correcta de CDMX
include("1_conexion.php"); // conexión mysqli en $conn

$mensaje = "";

// ---------- INICIO DE SESIÓN ----------
if (isset($_POST['ingresar'])) {
    $usuario = trim($_POST['usuario']);
    $clave = $_POST['clave'];

    // Preparar y ejecutar consulta
    $stmt = $conn->prepare("SELECT clave, rol FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($claveDB, $rol);
        $stmt->fetch();

        if ($clave === $claveDB) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['rol'] = $rol;
            $_SESSION['hora_acceso'] = date("d-m-Y H:i:s");

            // Registrar inicio de sesión en bitácora
            $accion = "Inicio de sesión";
            $fecha = date("Y-m-d H:i:s");

            $bit = $conn->prepare("INSERT INTO bitacora (usuario, accion, fecha) VALUES (TRIM(?), TRIM(?), ?)");
            $bit->bind_param("sss", $usuario, $accion, $fecha);

            if (!$bit->execute()) {
                die("Error al registrar bitácora: " . $conn->error);
            }
            $bit->close();

            header("Location: 1_principal.php");
            exit;
        } else {
            $mensaje = "❌ Contraseña incorrecta";
        }
    } else {
        $mensaje = "❌ Usuario no encontrado";
    }
    $stmt->close();
}

// ---------- REGISTRO DE USUARIO ----------
if (isset($_POST['registrar'])) {
    $usuario = trim($_POST['usuario']);
    $clave = $_POST['clave'];
    $rol = "usuario";

    if (!empty($usuario) && !empty($clave)) {
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, clave, rol) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $usuario, $clave, $rol);

        if ($stmt->execute()) {
            // Registrar acción en bitácora
            $accion = "Registro de usuario ($rol)";
            $fecha = date("Y-m-d H:i:s");

            $bit = $conn->prepare("INSERT INTO bitacora (usuario, accion, fecha) VALUES (TRIM(?), TRIM(?), ?)");
            $bit->bind_param("sss", $usuario, $accion, $fecha);

            if (!$bit->execute()) {
                die("Error al registrar bitácora: " . $conn->error);
            }
            $bit->close();

            $mensaje = "✅ Usuario registrado correctamente";
        } else {
            $mensaje = "⚠️ Error al registrar usuario: " . $conn->error;
        }
        $stmt->close();
    } else {
        $mensaje = "⚠️ Debes ingresar usuario y contraseña";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login / Registro</title>
<style>
body {
  margin: 0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: Arial, sans-serif;
  background-color: #050303ff;
}
.contenedor {
    width: 500px;
 height: 700px;
  text-align: center;
  background: white;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
h2 { margin-bottom: 20px; }
input { padding: 8px; margin: 5px 0; width: 80%; }
button { padding: 10px 20px; margin: 10px; border: none; border-radius: 5px; background: #007BFF; color: white; cursor: pointer; }
button:hover { background: #0056b3; }
</style>
</head>
<body>
<div class="contenedor">
  <h2>Iniciar Sesión / Registrar</h2>
  <form method="post">
    <input type="text" name="usuario" placeholder="Usuario" required><br>
    <input type="password" name="clave" placeholder="Contraseña" required><br>
    <button type="submit" name="ingresar">Ingresar</button>
    <button type="submit" name="registrar">Registrar</button>
  </form>
  <?php if (!empty($mensaje)) echo "<p><b>$mensaje</b></p>"; ?>
</div>
</body>
</html>
