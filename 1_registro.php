<?php  
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: 1_login.php");
    exit;
}

include("1_conexion.php"); // conexión a MySQL

// --- Cargar productos desde la base de datos ---
$sql = "SELECT * FROM productos";
$result = $conn->query($sql);
$productos = array();

while ($row = $result->fetch_assoc()) {
    $productos[$row['id']] = $row;
}

// --- Inicializar carrito ---
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// --- Agregar al carrito ---
if (isset($_GET['agregar'])) {
    $id = intval($_GET['agregar']);
    if (isset($productos[$id]) && $productos[$id]['stock'] > 0) {
        if (isset($_SESSION['carrito'][$id])) {
            // No permitir más de lo disponible
            if ($_SESSION['carrito'][$id]['cantidad'] < $productos[$id]['stock']) {
                $_SESSION['carrito'][$id]['cantidad']++;
            }
        } else {
            $_SESSION['carrito'][$id] = array(
                "nombre" => $productos[$id]['nombre'],
                "precio" => $productos[$id]['precio'],
                "cantidad" => 1
            );
        }
    }
    header("Location: 1_principal.php");
    exit;
}

// --- Eliminar producto del carrito ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    unset($_SESSION['carrito'][$id]);
    header("Location: 1_principal.php");
    exit;
}

// --- Finalizar compra ---
if (isset($_POST['finalizar'])) {
    if (!empty($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $id => $item) {
            $cantidad = $item['cantidad'];
            $conn->query("UPDATE productos SET stock = stock - $cantidad WHERE id = $id");
        }
        $_SESSION['carrito'] = array();
        $mensaje = "✅ ¡Compra finalizada con éxito! Gracias por tu compra.";
    } else {
        $mensaje = "⚠️ No hay productos en el carrito.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carrito de Compras</title>
<style>
  body { margin: 0; padding: 20px; font-family: Arial, sans-serif; background: #f4f4f4; }
  .contenedor { max-width: 1000px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
  h2, h3 { text-align: center; margin-bottom: 15px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
  th { background: #000; color: white; }
  a { text-decoration: none; color: #007BFF; }
  a:hover { color: #0056b3; }
  img { width: 80px; border-radius: 8px; }
  .resumen { background: #f0f0f0; padding: 10px; border-radius: 8px; text-align: center; }
  .logout { display:block; text-align:center; margin-top:20px; }
  .btn-finalizar {
    background-color: #28a745; color: white; padding: 10px 18px;
    border: none; border-radius: 6px; cursor: pointer; font-size: 16px;
  }
  .btn-finalizar:hover { background-color: #218838; }
  .mensaje { text-align:center; background:#e8ffe8; border:1px solid #28a745;
    padding:10px; margin-bottom:15px; border-radius:8px; font-weight:bold; color:#155724;
  }
</style>
</head>
<body>
<div class="contenedor">
  <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>
  <p style="text-align:center;">Rol: <strong><?php echo $_SESSION['rol']; ?></strong></p>
  <p style="text-align:center;">Hora de acceso: <?php echo $_SESSION['hora_acceso']; ?></p>

  <?php if (isset($mensaje)) echo "<div class='mensaje'>$mensaje</div>"; ?>

  <?php if ($_SESSION['rol'] === 'admin'): ?> 
    <h3>Panel de administración</h3> 
    <a href='1_usuarios.php'>👥 Usuarios</a><br> 
    <a href='crud_productos.php'>📦 Productos</a><br> 
    <a href='1_bitacora.php'>📋 Ver Bitácora</a><br><br>
  <?php endif; ?>

  <?php if ($_SESSION['rol'] === 'usuario'): ?> 
    <h3>🛒 Productos disponibles</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Imagen</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Acción</th>
      </tr>
      <?php if (count($productos) > 0): ?>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td><?php echo $p['id']; ?></td>
            <td>
              <?php if (!empty($p['imagen'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($p['imagen']); ?>" alt="imagen">
              <?php else: ?>
                <img src="img/noimg.png" alt="Sin imagen">
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
            <td>$<?php echo number_format($p['precio'], 2); ?></td>
            <td><?php echo $p['stock']; ?></td>
            <td>
              <?php if ($p['stock'] > 0): ?>
                <a href="?agregar=<?php echo $p['id']; ?>">🛍️ Agregar</a>
              <?php else: ?>
                <span style="color:red;">Agotado</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No hay productos disponibles</td></tr>
      <?php endif; ?>
    </table>
  <?php endif; ?>

  <?php if ($_SESSION['rol'] === 'usuario'): ?> 
    <h3>🧾 Carrito de compras</h3>
    <?php if (!empty($_SESSION['carrito'])): ?>
      <table>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
          <th>Acción</th>
        </tr>
        <?php 
          $subtotal_general = 0;
          foreach ($_SESSION['carrito'] as $id => $item):
            $subtotal = $item['precio'] * $item['cantidad'];
            $subtotal_general += $subtotal;
        ?>
          <tr>
            <td><?php echo $item['nombre']; ?></td>
            <td>$<?php echo number_format($item['precio'], 2); ?></td>
            <td><?php echo $item['cantidad']; ?></td>
            <td>$<?php echo number_format($subtotal, 2); ?></td>
            <td><a href="?eliminar=<?php echo $id; ?>">❌ Eliminar</a></td>
          </tr>
        <?php endforeach; ?>
        <?php 
          $iva = $subtotal_general * 0.16;
          $total = $subtotal_general + $iva;
        ?>
      </table>

      <div class="resumen">
        <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal_general, 2); ?></p>
        <p><strong>IVA (16%):</strong> $<?php echo number_format($iva, 2); ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($total, 2); ?></p>

        <form method="post">
          <button type="submit" name="finalizar" class="btn-finalizar">💳 Finalizar compra</button>
        </form>
      </div>
    <?php else: ?>
      <p style="text-align:center;">🛒 El carrito está vacío</p>
    <?php endif; ?>
  <?php endif; ?>

  <a class="logout" href='1_logout.php'>🚪 Cerrar sesión</a>
</div>
</body>
</html>
