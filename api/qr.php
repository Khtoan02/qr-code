<?php
/**
 * QR Code API Endpoint
 * Generate QR code với logo PayGen
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sepay.php';
require_once __DIR__ . '/../includes/qrgenerator.php';

header('Content-Type: application/json');

$amount = $_GET['amount'] ?? 0;
$paymentCode = $_GET['code'] ?? '';
$format = $_GET['format'] ?? 'url'; // 'url' hoặc 'image'

if (empty($paymentCode) || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing amount or payment code']);
    exit;
}

try {
    if ($format === 'image') {
        // Generate QR code với logo và return base64
        $qrData = QRGenerator::generateSePayQR($amount, $paymentCode, true);
        echo json_encode([
            'success' => true,
            'qr_code' => $qrData,
            'format' => 'base64'
        ]);
    } else {
        // Return URL (fallback)
        $qrUrl = SePayService::getQRUrl($amount, $paymentCode);
        echo json_encode([
            'success' => true,
            'qr_url' => $qrUrl,
            'format' => 'url'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate QR code',
        'message' => $e->getMessage()
    ]);
}
?>

