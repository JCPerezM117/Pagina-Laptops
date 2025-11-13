<?php
require 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

$url = "https://www.youtube.com"; // 🔗 Texto o URL que quieras convertir en QR

// Crear el QR
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($url)
    ->encoding(new Encoding('UTF-8'))
    ->size(250)
    ->margin(10)
    ->build();

// Convertir a Base64 para incrustar en HTML
$qrBase64 = base64_encode($result->getString());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de QR</title>
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 50px;
        }
        img {
            border: 4px solid #fff;
            border-radius: 10px;
            padding: 10px;
            background: #000;
            width: 250px;
            height: 250px;
        }
        h1 {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <h1>🔳 Código QR generado</h1>
    <p>Contenido: <strong><?php echo htmlspecialchars($url); ?></strong></p>
    <img src="data:image/png;base64,<?php echo $qrBase64; ?>" alt="Código QR">
</body>
</html>
