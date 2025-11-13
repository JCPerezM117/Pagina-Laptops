<?php 
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: 1_login.php");
    exit;
}

include("1_conexion.php"); // conexión a la BD

// --- Cargar productos desde la base de datos ---
$sql = "SELECT id, nombre, descripcion, precio, stock, imagen FROM productos ORDER BY id ASC";
$result = $conn->query($sql);

$productos = array();
while ($row = $result->fetch_assoc()) {
    $productos[$row['id']] = $row;
}

// --- Generar y guardar QR si no existe ---
require 'vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

foreach ($productos as $id => $p) {
    $check = $conn->prepare("SELECT qr FROM productos WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    $check->bind_result($qrData);
    $check->fetch();

    // Si no hay QR en la base, generarlo
    if (empty($qrData)) {
        // Generar un enlace que lleva a la página detalle del producto
        
        // Crear el código QR con esa URL
        $qr = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->size(200)
            ->margin(5)
            ->build();

        $qrBinary = $qr->getString();

        // Guardar QR como blob
        $update = $conn->prepare("UPDATE productos SET qr = ? WHERE id = ?");
        $update->bind_param("bi", $qrBinary, $id);
        $update->send_long_data(0, $qrBinary);
        $update->execute();
        $update->close();
    }

    $check->close();
}

// --- Inicializar carrito ---
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// --- Agregar al carrito ---
if (isset($_GET['agregar'])) {
    $id = intval($_GET['agregar']);
    if (isset($productos[$id])) {
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad']++;
        } else {
            $_SESSION['carrito'][$id] = array(
                "nombre" => $productos[$id]['nombre'],
                "precio" => $productos[$id]['precio'],
                "cantidad" => 1,
                "imagen" => base64_encode($productos[$id]['imagen'])
            );
        }
    }

    header("Location: 1_principal.php");
    exit;
}

// --- Eliminar producto del carrito ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if (isset($_SESSION['carrito'][$id])) {
        unset($_SESSION['carrito'][$id]);
    }
    header("Location: 1_principal.php");
    exit;
}

// --- Finalizar compra ---
if (isset($_POST['finalizar'])) {
    if (!empty($_SESSION['carrito'])) {
        $_SESSION['ultimo_ticket'] = $_SESSION['carrito'];
        $_SESSION['carrito'] = array();
        header("Location: ticket.php");
        exit;
    } else {
        $mensaje = "⚠️ No hay productos en el carrito para finalizar.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Página Principal</title>
<style>
body {
  margin: 0;
  padding: 20px;
  font-family: Arial, sans-serif;
  background-color: #000;
  color: #fff;
}
.main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}
.productos, .carrito {
  background: #fff;
  color: #000;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.productos { flex: 3; }
.carrito { flex: 1.3; position: sticky; top: 20px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
th { background: linear-gradient(90deg, #007BFF, #000); color: white; padding: 8px; }
td { border: 1px solid #ccc; padding: 8px; text-align: center; }
tr:nth-child(even) { background-color: #f9f9f9; }
tr:hover { background-color: #e6f0ff; }
img { width: 100px; height: auto; border-radius: 6px; }
.btn-finalizar { background-color: #28a745; color: white; padding: 10px 18px; 
  border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin-top: 10px; }
.btn-finalizar:hover { background-color: #218838; }
.logout { display:block; text-align:center; margin-top:20px; color:white; }
.mensaje { text-align:center; background:#e8ffe8; border:1px solid #28a745; 
  padding:10px; margin-bottom:15px; border-radius:8px; font-weight:bold; color:#155724; }
.admin-menu { text-align:center; margin-bottom:20px; }
.admin-menu a { margin:0 10px; color:#ffd700; text-decoration:none; font-weight:bold; }
.admin-menu a:hover { text-decoration:underline; }
</style>
</head>
<body>
<h2 style="text-align:center;">Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>
<p style="text-align:center;">Hora de acceso: <?php echo $_SESSION['hora_acceso']; ?></p>

<?php if (isset($mensaje)) echo "<div class='mensaje'>$mensaje</div>"; ?>

<?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
  <div class="admin-menu">
    <a href="1_bitacora.php">📜 Bitácora</a>
    <a href="1_usuarios.php">👥 Usuarios</a>
    <a href="crud_productos.php">🛍️ CRUD Productos</a>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'usuario'): ?>
  <div class="main">
    <div class="productos">
      <h3>🛒 Productos disponibles</h3>
      <table>
        <tr>
          <th>ID</th>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>QR</th>
          <th>Acción</th>
        </tr>
        <?php if (count($productos) > 0): ?>
          <?php foreach ($productos as $id => $p): ?>
            <tr>
              <td><?php echo $id; ?></td>
              <td>
                <?php if (!empty($p['imagen'])): ?>
                  <img src="data:image/jpeg;base64,<?php echo base64_encode($p['imagen']); ?>" alt="Producto">
                <?php else: ?>
                  <img src="img/noimg.png" alt="Sin imagen">
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($p['nombre']); ?></td>
              <td><?php echo htmlspecialchars($p['descripcion']); ?></td>
              <td>$<?php echo number_format($p['precio'], 2); ?></td>
              <td><?php echo $p['stock']; ?></td>

              <td>
                <?php
                $qrQuery = $conn->prepare("SELECT qr FROM productos WHERE id = ?");
                $qrQuery->bind_param("i", $id);
                $qrQuery->execute();
                $qrQuery->store_result();
                $qrQuery->bind_result($qrBin);
                $qrQuery->fetch();

                if (!empty($qrBin)) {
                    echo '<img src="data:image/png;base64,' . base64_encode($qrBin) . '" alt="QR" width="80">';
                } else {
                    echo 'Sin QR';
                }
                $qrQuery->close();
                ?>
              </td>

              <td><a href="?agregar=<?php echo $id; ?>">🛍️ Agregar</a></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8">No hay productos registrados</td></tr>
        <?php endif; ?>
      </table>
    </div>

    <div class="carrito">
      <h3>🧾 Carrito de compras</h3>
      <?php if (!empty($_SESSION['carrito'])): ?>
        <table>
          <tr>
            <th>Producto</th>
            <th>Cant.</th>
            <th>Subtotal</th>
            <th>❌</th>
          </tr>
          <?php 
            $subtotal_general = 0;
            foreach ($_SESSION['carrito'] as $id => $item):
              $subtotal = $item['precio'] * $item['cantidad'];
              $subtotal_general += $subtotal;
          ?>
            <tr>
              <td><?php echo $item['nombre']; ?></td>
              <td><?php echo $item['cantidad']; ?></td>
              <td>$<?php echo number_format($subtotal, 2); ?></td>
              <td><a href="?eliminar=<?php echo $id; ?>">❌</a></td>
            </tr>
          <?php endforeach; ?>
        </table>

        <?php 
          $subtotal_sin_iva = $subtotal_general / 1.16;  
          $iva = $subtotal_general - $subtotal_sin_iva;
        ?>

        <div class="resumen">
          <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal_sin_iva, 2); ?></p>
          <p><strong>IVA (16%):</strong> $<?php echo number_format($iva, 2); ?></p>
          <p><strong>Total:</strong> $<?php echo number_format($subtotal_general, 2); ?></p>
          <form method="post">
            <button type="submit" name="finalizar" class="btn-finalizar">💳 Finalizar compra</button>
          </form>
        </div>
      <?php else: ?>
        <p style="text-align:center;">🛒 El carrito está vacío</p>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<a class="logout" href='1_logout.php'>🚪 Cerrar sesión</a>
</body>
</html>
