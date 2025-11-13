<?php
session_start();
require 'vendor/autoload.php'; // Asegúrate de tener instalado phpmailer y dompdf

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------------- VALIDACIONES ----------------
if (empty($_POST['correo'])) {
    die("⚠️ No se proporcionó un correo electrónico.");
}
$correo = trim($_POST['correo']);

if (!isset($_SESSION['ultimo_ticket']) || empty($_SESSION['ultimo_ticket'])) {
    die("⚠️ No hay productos para generar el ticket.");
}

$carrito = $_SESSION['ultimo_ticket'];

// ---------------- CÁLCULOS ----------------
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$subtotal_sin_iva = $subtotal / 1.16;
$iva = $subtotal - $subtotal_sin_iva;
$total = $subtotal;
$fecha = date("d/m/Y H:i:s");
$usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : "Invitado";

// ---------------- GENERAR HTML DEL TICKET ----------------
$html = '
<html><head><meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { border:1px solid #ccc; padding:6px; text-align:center; }
th { background:#000; color:#fff; }
img { width:60px; height:auto; border-radius:5px; }
.resumen { text-align:right; margin-top:15px; font-weight:bold; }
</style>
</head><body>
<h2>🎫 Ticket de Compra</h2>
<p><strong>Usuario:</strong> ' . htmlspecialchars($usuario) . '</p>
<p><strong>Fecha:</strong> ' . $fecha . '</p>
<table>
<tr><th>Imagen</th><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr>';

foreach ($carrito as $item) {
    $sub = $item["precio"] * $item["cantidad"];
    $imagen = !empty($item["imagen"]) ? '<img src="data:image/jpeg;base64,' . $item["imagen"] . '">' : 'N/A';
    $html .= "<tr>
        <td>$imagen</td>
        <td>{$item['nombre']}</td>
        <td>$" . number_format($item['precio'], 2) . "</td>
        <td>{$item['cantidad']}</td>
        <td>$" . number_format($sub, 2) . "</td>
    </tr>";
}
$html .= '</table>
<div class="resumen">
<p>Subtotal: $' . number_format($subtotal_sin_iva, 2) . '</p>
<p>IVA (16%): $' . number_format($iva, 2) . '</p>
<p>Total: $' . number_format($total, 2) . '</p>
</div>
</body></html>';

// ---------------- GENERAR PDF ----------------
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->render();
$pdf = $dompdf->output();
file_put_contents("ticket.pdf", $pdf);

// ---------------- ENVIAR CORREO ----------------
$mail = new PHPMailer(true);

try {
    // 🔹 Opcional: habilita depuración para ver detalles del SMTP
    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = 'html';

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // 🔹 Reemplaza con tus credenciales
    $mail->Username = 'juancarlosperezmondragon@gmail.com'; // tu correo Gmail
    $mail->Password = 'cjwy rynj jbeu qgpa'; // tu contraseña de aplicación de Gmail 

    $mail->SMTPSecure = 'tls'; // Usa 'tls' para el puerto 587
    $mail->Port = 587;

    $mail->setFrom('juancarlosperezmondragon@gmail.com', 'Tienda Online');
    $mail->addAddress($correo);

    $mail->isHTML(true);
    $mail->Subject = '🎫 Ticket de tu compra';
    $mail->Body = 'Gracias por tu compra, <b>' . htmlspecialchars($usuario) . '</b>.<br>Adjuntamos tu ticket en formato PDF.';

    $mail->addAttachment('ticket.pdf');

    $mail->send();
    unlink("ticket.pdf");

    echo "<h2 style='color:green; text-align:center;'>✅ Ticket enviado correctamente a <b>$correo</b></h2>";
    echo "<div style='text-align:center; margin-top:20px;'>
            <a href='ticket.php' style='color:blue; text-decoration:none;'>⬅️ Volver al ticket</a>
          </div>";

} catch (Exception $e) {
    echo "<h3 style='color:red; text-align:center;'>❌ Error al enviar el correo:</h3>";
    echo "<p style='text-align:center;'>" . htmlspecialchars($mail->ErrorInfo) . "</p>";
    unlink("ticket.pdf");
}
?>
