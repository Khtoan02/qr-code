<?php
/**
 * QR Code Image Endpoint với Logo PayGen
 * Tạo QR code và overlay logo PayGen vào giữa
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sepay.php';

$amount = $_GET['amount'] ?? 0;
$paymentCode = $_GET['code'] ?? '';

if (empty($paymentCode) || $amount <= 0) {
    http_response_code(400);
    die('Invalid parameters');
}

// Tạo VietQR data string
$qrData = "https://qr.sepay.vn/img?acc=" . VA_ACCOUNT_NUMBER . "&bank=MB&amount=" . $amount . "&des=" . urlencode($paymentCode);

// Sử dụng API QR code (qr-server.com hoặc similar)
$qrSize = 400;
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=" . $qrSize . "x" . $qrSize . "&data=" . urlencode($qrData) . "&margin=1";

// Fetch QR code image
$qrImageData = @file_get_contents($qrUrl);

if ($qrImageData === false) {
    // Fallback: redirect về SePay QR URL
    header('Location: ' . $qrData);
    exit;
}

// Tạo image từ QR data
$qrImage = @imagecreatefromstring($qrImageData);

if ($qrImage === false) {
    // Fallback
    header('Location: ' . $qrData);
    exit;
}

// Load và merge logo nếu có GD library
$logoPath = __DIR__ . '/../assets/images/icon.png'; // Cần convert SVG sang PNG
$logoPathSvg = __DIR__ . '/../assets/images/icon.svg';

// Nếu có Imagick, xử lý SVG logo
if (extension_loaded('imagick') && file_exists($logoPathSvg)) {
    try {
        $logoSize = $qrSize / 5; // 20% của QR size
        $logo = new Imagick($logoPathSvg);
        $logo->setImageFormat('png');
        $logo->resizeImage($logoSize, $logoSize, Imagick::FILTER_LANCZOS, 1);
        $logo->setImageBackgroundColor(new ImagickPixel('transparent'));
        
        // Convert Imagick sang GD resource
        $logoBlob = $logo->getImageBlob();
        $logoGd = @imagecreatefromstring($logoBlob);
        
        if ($logoGd) {
            // Tính vị trí đặt logo (giữa QR)
            $x = ($qrSize - $logoSize) / 2;
            $y = ($qrSize - $logoSize) / 2;
            
            // Merge logo vào QR code
            imagealphablending($qrImage, true);
            imagesavealpha($qrImage, true);
            imagecopymerge($qrImage, $logoGd, $x, $y, 0, 0, $logoSize, $logoSize, 100);
            
            imagedestroy($logoGd);
        }
        
        $logo->destroy();
    } catch (Exception $e) {
        // Ignore errors, continue without logo
    }
} elseif (file_exists($logoPath)) {
    // Nếu có PNG logo
    $logoSize = $qrSize / 5;
    $logoGd = @imagecreatefrompng($logoPath);
    
    if ($logoGd) {
        // Resize logo nếu cần
        $logoResized = imagecreatetruecolor($logoSize, $logoSize);
        imagealphablending($logoResized, false);
        imagesavealpha($logoResized, true);
        imagecopyresampled($logoResized, $logoGd, 0, 0, 0, 0, $logoSize, $logoSize, imagesx($logoGd), imagesy($logoGd));
        
        // Merge vào QR
        $x = ($qrSize - $logoSize) / 2;
        $y = ($qrSize - $logoSize) / 2;
        imagealphablending($qrImage, true);
        imagesavealpha($qrImage, true);
        imagecopymerge($qrImage, $logoResized, $x, $y, 0, 0, $logoSize, $logoSize, 100);
        
        imagedestroy($logoResized);
        imagedestroy($logoGd);
    }
}

// Output image
header('Content-Type: image/png');
header('Cache-Control: public, max-age=3600');
imagepng($qrImage);
imagedestroy($qrImage);
?>

