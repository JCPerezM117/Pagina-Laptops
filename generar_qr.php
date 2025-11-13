<?php
require 'vendor/autoload.php';
include("1_conexion.php");

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

// 🔗 URL base local (ajústala si tu carpeta tiene otro nombre)
$baseUrl = "http://localhost/Topicos%20Selectos%20de%20desarrollo%20web/Unidad_2/producto_detalle.php?id=";

// Obtener todos los productos
$sql = "SELECT id FROM productos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Crear la URL del producto
        $url = $baseUrl . $row['id'];

        // Generar el QR con esa URL
        $qr = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->size(250)
            ->margin(10)
            ->build();

        // Obtener los bytes del QR
        $qrData = $qr->getString();

        // Guardar el QR en la base de datos
        $stmt = $conn->prepare("UPDATE productos SET qr = ? WHERE id = ?");
        $stmt->bind_param("bi", $qrData, $row['id']);
        $stmt->send_long_data(0, $qrData);
        $stmt->execute();
        $stmt->close();
    }

    echo "✅ QRs generados correctamente. Escanea uno y abrirá su detalle.";
} else {
    echo "⚠️ No hay productos en la base de datos.";
}

$conn->close();
?>
