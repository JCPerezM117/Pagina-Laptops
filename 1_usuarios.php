<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: 1_login.php");
    exit;
}

include("1_conexion.php");

date_default_timezone_set('America/Mexico_City');

// --- Crear usuario ---
if (isset($_POST['crear'])) {
    $usuario = trim($_POST['usuario']);
    $clave = $_POST['clave'];
    $rol = $_POST['rol'];
    $fecha_registro = date("Y-m-d H:i:s");

    $imagen = null;
    if (!empty($_FILES['imagen']['tmp_name'])) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
    }

    if (!empty($usuario) && !empty($clave)) {
        // 'b' en bind_param indica BLOB
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, clave, rol, imagen, fecha_registro) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssbs", $usuario, $clave, $rol, $dummy = null, $fecha_registro);

        if ($imagen !== null) {
            $stmt->send_long_data(3, $imagen); // posición 3 = columna imagen
        }

        $stmt->execute();
        $stmt->close();

        header("Location: 1_usuarios.php");
        exit;
    }
}

// --- Consultar usuarios ---
$sql = "SELECT id, usuario, clave, rol, imagen, fecha_registro FROM usuarios ORDER BY id ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios</title>
<style>
body { margin:0; font-family:Arial,sans-serif; background:  black; display:flex; 
    justify-content:center; padding:30px; }
.contenedor { width:95%; height: 700px; max-width:1000px; background:white; padding:20px;
     border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.2); }
h2 { text-align:center; margin-bottom:20px; }
form { margin-bottom:20px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
input, select, button { padding:8px; font-size:14px; }
table { width:100%; border-collapse:collapse; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#000; color:white; }
tr:nth-child(even) { background:#f9f9f9; }
img { width:50px; height:50px; border-radius:50%; }
.volver { display:inline-block; margin-top:15px; text-decoration:none; color:#000; }
</style>
</head>
<body>
<div class="contenedor">
    <h2>Gestión de Usuarios</h2>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="text" name="clave" placeholder="Clave" required>
        <select name="rol" required>
            <option value="usuario">Usuario</option>
            <option value="admin">Administrador</option>
        </select>
        <input type="file" name="imagen" accept="image/*">
        <button type="submit" name="crear">➕ Crear</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Clave</th>
            <th>Rol</th>
            <th>Imagen</th>
            <th>Hora/Fecha</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['usuario']."</td>";
                echo "<td>".$row['clave']."</td>";
                echo "<td>".$row['rol']."</td>";
                echo "<td>";
                if (!empty($row['imagen'])) {
                    echo "<img src='data:image/jpeg;base64,".base64_encode($row['imagen'])."' alt='perfil'>";
                } else {
                    echo "Sin imagen";
                }
                echo "</td>";
                echo "<td>".$row['fecha_registro']."</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No hay usuarios registrados</td></tr>";
        }
        ?>
    </table>

    <br>
    <a class="volver" href="1_principal.php">⬅️ Volver al menú</a>
</div>
</body>
</html>
