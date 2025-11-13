<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: 1_login.php");
    exit;
}

include("1_conexion.php");

// --- AGREGAR PRODUCTO ---
if (isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion']; 
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    if (!empty($_FILES['imagen']['tmp_name'])) {
        $imagen = addslashes(file_get_contents($_FILES['imagen']['tmp_name']));
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen) 
                VALUES ('$nombre', '$descripcion', '$precio', '$stock', '$imagen')";
    } else {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock) 
                VALUES ('$nombre', '$descripcion', '$precio', '$stock')";
    }

    $conn->query($sql);
    header("Location: crud_productos.php");
    exit;
}

// --- ELIMINAR PRODUCTO ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM productos WHERE id = $id");
    header("Location: crud_productos.php");
    exit;
}

// --- EDITAR PRODUCTO ---
if (isset($_POST['editar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion']; 
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    if (!empty($_FILES['imagen']['tmp_name'])) {
        $imagen = addslashes(file_get_contents($_FILES['imagen']['tmp_name']));
        $sql = "UPDATE productos SET nombre='$nombre', descripcion='$descripcion', precio='$precio', stock='$stock', imagen='$imagen' WHERE id=$id";
    } else {
        $sql = "UPDATE productos SET nombre='$nombre', descripcion='$descripcion', precio='$precio', stock='$stock' WHERE id=$id";
    }

    $conn->query($sql);
    header("Location: crud_productos.php");
    exit;
}

// --- OBTENER TODOS LOS PRODUCTOS ---
$result = $conn->query("SELECT * FROM productos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administrar Productos</title>
<style>
body { background:black; font-family:Arial; padding:20px; }
.contenedor { max-width:1000px; margin:auto; background:white; padding:20px; 
    border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.2); }
h2, h3 { text-align:center; }
table { width:100%; border-collapse:collapse; margin-bottom:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#000; color:white; }
form { margin-bottom:20px; text-align:center; }
input[type=text], input[type=number], input[type=file] { padding:8px; margin:5px; width:200px; }
button { background:#007bff; color:white; padding:8px 14px; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
img { width:80px; border-radius:8px; }
.volver { display:block; text-align:center; margin-top:10px; }
</style>
</head>
<body>
<div class="contenedor">
<h2>📦 Panel de Productos (Administrador)</h2>

<h3>Agregar nuevo producto</h3>
<form method="post" enctype="multipart/form-data">
  <input type="text" name="nombre" placeholder="Nombre" required>
  <input type="text" name="descripcion" placeholder="Descripción">
  <input type="number" name="precio" placeholder="Precio" required>
  <input type="number" name="stock" placeholder="Stock" required>
  <input type="file" name="imagen">
  <button type="submit" name="agregar">Agregar</button>
</form>

<h3>Lista de productos</h3>
<table>
<tr>
  <th>ID</th>
  <th>Imagen</th>
  <th>Nombre</th>
  <th>Descripción</th>
  <th>Precio</th>
  <th>Stock</th>
  <th>Acción</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?php echo $row['id']; ?></td>
  <td>
    <?php if (!empty($row['imagen'])): ?>
      <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" alt="">
    <?php else: ?>
      <img src="img/noimg.png" alt="Sin imagen">
    <?php endif; ?>
  </td>
  <td><?php echo htmlspecialchars($row['nombre']); ?></td>
  <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
  <td>$<?php echo number_format($row['precio'], 2); ?></td>
  <td><?php echo $row['stock']; ?></td>
  <td>
    <form method="post" enctype="multipart/form-data" style="display:inline;">
      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
      <input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required>
      <input type="text" name="descripcion" value="<?php echo htmlspecialchars($row['descripcion']); ?>">
      <input type="number" name="precio" value="<?php echo $row['precio']; ?>" required>
      <input type="number" name="stock" value="<?php echo $row['stock']; ?>" required>
      <input type="file" name="imagen">
      <button type="submit" name="editar">Guardar</button>
    </form>
    <a href="?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar este producto?')">❌</a>
  </td>
</tr>
<?php endwhile; ?>
</table>

<a class="volver" href="1_principal.php">⬅️ Volver al inicio</a>
</div>
</body>
</html>
