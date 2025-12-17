<?php
/**
 * Webhook Test Endpoint
 * Dùng để test webhook locally hoặc kiểm tra format dữ liệu
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Simulate SePay webhook data
$testData = [
    'transaction_content' => 'DH123456',
    'amount_in' => '100000',
    'account_number' => '0329249536',
    'sub_account' => 'VQRQAFYMM9200',
    'transaction_date' => date('Y-m-d H:i:s'),
    'reference_number' => 'REF123456'
];

echo json_encode([
    'message' => 'Webhook test endpoint',
    'webhook_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/webhook.php',
    'test_data_format' => $testData,
    'instructions' => [
        '1. Cấu hình webhook URL trong SePay dashboard',
        '2. URL: http://yourdomain.com/webhook.php',
        '3. Method: POST',
        '4. Content-Type: application/json',
        '5. SePay sẽ gửi dữ liệu khi có giao dịch mới'
    ],
    'current_transactions' => Database::getTransactions()
], JSON_PRETTY_PRINT);
?>

