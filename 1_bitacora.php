<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: 1_login.php");
    exit;
}

include("1_conexion.php"); // conexión en $conn

// Consultar registros de bitácora ordenados por ID descendente
$sql = "SELECT id, TRIM(usuario) AS usuario, TRIM(accion) AS accion, fecha 
        FROM bitacora 
        ORDER BY id DESC"; // ✅ orden descendente por ID

$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta de bitácora: " . $conn->error);
}

// Traer todos los registros en un array
$bitacora = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Bitácora del sistema</title>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #000000ff;
    display: flex;
    justify-content: center;
    padding: 30px;
  }
  .contenedor {
    width: 90%;
    max-width: 900px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }
  h2 {
    text-align: center;
    margin-bottom: 20px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
  }
  th {
    background: #000000ff;
    color: white;
  }
  tr:nth-child(even) {
    background: #f9f9f9;
  }
  a {
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
    color: #007BFF;
  }
  a:hover {
    color: #0056b3;
  }
</style>
</head>
<body>
<div class="contenedor">
  <h2>Bitácora del sistema</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Usuario</th>
      <th>Acción</th>
      <th>Fecha</th>
    </tr>
    <?php
    if (!empty($bitacora)) {
        foreach ($bitacora as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($row['accion']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay registros en la bitácora</td></tr>";
    }
    ?>
  </table>
  <br>
  <a href="1_principal.php">⬅ Volver al menú</a>
</div>
</body>
</html>
