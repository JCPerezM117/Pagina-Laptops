<?php
include("1_conexion.php");

// Verificar que llega un ID
if (!isset($_GET['id'])) {
    echo "⚠️ No se especificó un producto.";
    exit;
}

$id = intval($_GET['id']);

// Buscar el producto
$stmt = $conn->prepare("SELECT nombre, descripcion, precio, stock, imagen FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo "❌ Producto no encontrado.";
    exit;
}

$stmt->bind_result($nombre, $descripcion, $precio, $stock, $imagen);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($nombre); ?></title>
<style>
body {
  background-color: #000;
  color: #fff;
  font-family: Arial, sans-serif;
  padding: 20px;
}
.card {
  background: #fff;
  color: #000;
  padding: 20px;
  border-radius: 10px;
  max-width: 400px;
  margin: 40px auto;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
img {
  width: 100%;
  border-radius: 8px;
  margin-bottom: 10px;
}
h2 { text-align: center; }
</style>
</head>
<body>
<div class="card">
  <?php if (!empty($imagen)): ?>
    <img src="data:image/jpeg;base64,<?php echo base64_encode($imagen); ?>" alt="Producto">
  <?php endif; ?>

  <h2><?php echo htmlspecialchars($nombre); ?></h2>
  <p><strong>Descripción:</strong> <?php echo htmlspecialchars($descripcion); ?></p>
  <p><strong>Precio:</strong> $<?php echo number_format($precio, 2); ?></p>
  <p><strong>Stock disponible:</strong> <?php echo $stock; ?></p>

  <p style="text-align:center;"><a href="1_principal.php">⬅️ Volver</a></p>
</div>
</body>
</html>
