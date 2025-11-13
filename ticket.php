<?php
session_start();

// Verificar si hay carrito para generar ticket
if (!isset($_SESSION['ultimo_ticket']) || empty($_SESSION['ultimo_ticket'])) {
    die("⚠️ No hay productos para generar el ticket.");
}

$carrito = $_SESSION['ultimo_ticket'];

// Calcular totales
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
            $subtotal_sin_iva = $subtotal / 1.16;  
            $iva = $subtotal - $subtotal_sin_iva;

date_default_timezone_set('America/Belize');
$fecha = date("d/m/Y H:i:s");
$usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : "Invitado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket de Compra</title>
<style>
body { font-family: Arial, sans-serif; background: #000; padding:20px; }
.contenedor { max-width:700px; margin:auto; background:#fff; padding:20px; 
    border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.2); }
h2 { text-align:center; margin-bottom:10px; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#000; color:white; }
td img { width:150px; height:auto; object-fit:cover; border-radius:8px; }
.resumen { margin-top:15px; text-align:right; font-weight:bold; }
.btn-imprimir { display:block; margin:20px auto; padding:10px 20px;
     background:#007BFF; color:white; border:none; border-radius:5px; cursor:pointer; }
.btn-imprimir:hover { background:#0056b3; }

/* Estilos para impresión */
@media print {
  .btn-imprimir { display:none; }
  body { background:white; padding:0; }
  .contenedor { box-shadow:none; margin:0; border-radius:0; }
}
</style>
</head>
<body>
<div class="contenedor">
    <h2>🎫 Ticket de Compra</h2>
    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario); ?></p>
    <p><strong>Fecha:</strong> <?php echo $fecha; ?></p>

    <table>
        <tr>
            <th>Imagen</th>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($carrito as $item):
            $sub = $item['precio'] * $item['cantidad'];
        ?>
        <tr>
            <td>
                <?php if (!empty($item['imagen'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $item['imagen']; ?>" alt="Producto">
                <?php else: ?>
                    <img src="img/noimg.png" alt="Sin imagen">
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
            <td>$<?php echo number_format($item['precio'], 2); ?></td>
            <td><?php echo $item['cantidad']; ?></td>
            <td>$<?php echo number_format($sub, 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="resumen">
        <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal_sin_iva, 2); ?></p>
          <p><strong>IVA (16%):</strong> $<?php echo number_format($iva, 2); ?></p>
          <p><strong>Total:</strong> $<?php echo number_format($subtotal, 2); ?></p>
    </div>

    <button class="btn-imprimir" onclick="window.print();">🖨️ Guardar o Imprimir Ticket</button>

    

<form action="enviar_ticket.php" method="POST" style="text-align:center; margin-top:20px;">
    <input type="email" name="correo" placeholder="Ingresa tu correo electrónico" required 
        style="padding:8px; border-radius:5px; width:70%; border:1px solid #ccc;">
    <button type="submit" class="btn-imprimir" style="background:#28a745;">📧 Enviar por Correo</button>
</form>

</div>
</body>
</html>
