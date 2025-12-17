<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sepay.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (!$id) {
    echo json_encode(['error' => 'Missing transaction ID']);
    exit;
}

$transaction = Database::getTransactionById($id);

if (!$transaction) {
    echo json_encode(['error' => 'Transaction not found']);
    exit;
}

// If already paid, return immediately
if ($transaction['status'] === 'PAID') {
    echo json_encode(['status' => 'PAID', 'transaction' => $transaction]);
    exit;
}

// Check payment status
$isPaid = SePayService::checkPayment($transaction['payment_code'], $transaction['amount']);

if ($isPaid) {
    Database::updateTransactionStatus($id, 'PAID');
    $transaction = Database::getTransactionById($id);
    echo json_encode(['status' => 'PAID', 'transaction' => $transaction]);
} else {
    echo json_encode(['status' => 'PENDING', 'transaction' => $transaction]);
}
?>

