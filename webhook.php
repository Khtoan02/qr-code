<?php
/**
 * SePay Webhook Endpoint
 * 
 * URL: https://yourdomain.com/webhook.php
 * 
 * Cấu hình trong SePay:
 * - Webhook URL: https://yourdomain.com/webhook.php
 * - Method: POST
 * - Content-Type: application/json
 * - Authentication: API Key
 * - API Key: TZ6IDLTMBQGTVGUOGSNXWOMQZD0FKR94D02MWZXE7CCQ7WFCRVKUXSHZEEVJ92YJ
 * 
 * Tài liệu: https://docs.sepay.vn/tich-hop-webhooks.html
 */

require_once 'config.php';
require_once 'includes/db.php';

// Set content type
header('Content-Type: application/json');

// Log webhook data (for debugging)
$logFile = __DIR__ . '/logs/webhook.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logWebhook($data) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Get all headers
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

// Get raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Log incoming webhook
logWebhook([
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => $headers,
    'auth_header' => $authHeader,
    'raw_data' => $rawData,
    'parsed_data' => $data
]);

// Allow GET for testing/debugging
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'webhook_endpoint',
        'url' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'method' => 'POST',
        'description' => 'SePay webhook endpoint - chỉ chấp nhận POST requests',
        'authentication' => 'API Key',
        'api_key' => SEPAY_API_KEY,
        'instructions' => [
            '1. Cấu hình webhook URL này trong SePay dashboard',
            '2. SePay sẽ gửi POST request với header Authorization: Apikey ' . SEPAY_API_KEY,
            '3. Webhook sẽ tự động cập nhật trạng thái giao dịch',
            '4. Kiểm tra logs tại: /logs/webhook.log'
        ],
        'test' => [
            'Để test webhook, sử dụng curl:',
            'curl -X POST https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' -H "Content-Type: application/json" -H "Authorization: Apikey ' . SEPAY_API_KEY . '" -d \'{"id":123,"code":"DH123456","content":"DH123456","transferType":"in","transferAmount":100000}\''
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Verify webhook method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Webhook chỉ chấp nhận POST requests.']);
    exit;
}

// Verify JSON data
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data', 'raw' => $rawData]);
    exit;
}

// Verify API Key authentication (theo tài liệu SePay)
if (!empty($authHeader)) {
    $expectedAuth = 'Apikey ' . SEPAY_API_KEY;
    if ($authHeader !== $expectedAuth) {
        logWebhook(['status' => 'auth_failed', 'received' => $authHeader, 'expected' => $expectedAuth]);
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API Key']);
        exit;
    }
}

try {
    // SePay webhook structure theo tài liệu: https://docs.sepay.vn/tich-hop-webhooks.html
    // Format dữ liệu SePay gửi:
    // {
    //     "id": 92704,
    //     "gateway": "Vietcombank",
    //     "transactionDate": "2023-03-25 14:02:37",
    //     "accountNumber": "0123499999",
    //     "code": null hoặc "DH123456",  // Mã code thanh toán (SePay tự nhận diện)
    //     "content": "chuyen tien mua iphone",
    //     "transferType": "in",  // "in" là tiền vào, "out" là tiền ra
    //     "transferAmount": 2277000,
    //     "accumulated": 19077000,
    //     "subAccount": null,
    //     "referenceCode": "MBVCB.3278907687",
    //     "description": ""
    // }
    
    $sepayId = $data['id'] ?? null;
    $code = $data['code'] ?? null;  // Mã code thanh toán (DH123456)
    $content = $data['content'] ?? '';  // Nội dung chuyển khoản
    $transferType = $data['transferType'] ?? '';
    $transferAmount = floatval($data['transferAmount'] ?? 0);
    $accountNumber = $data['accountNumber'] ?? '';
    $subAccount = $data['subAccount'] ?? null;
    $transactionDate = $data['transactionDate'] ?? date('Y-m-d H:i:s');
    
    // Chỉ xử lý giao dịch tiền vào
    if ($transferType !== 'in') {
        logWebhook(['status' => 'ignored', 'reason' => 'not_incoming_transaction', 'transferType' => $transferType]);
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Ignored: not incoming transaction']);
        exit;
    }
    
    // Tìm payment code từ field "code" hoặc extract từ "content"
    $paymentCode = '';
    if (!empty($code)) {
        // SePay đã nhận diện được code
        $paymentCode = $code;
    } else {
        // Extract payment code từ content (format: DH123456)
        if (preg_match('/DH\d{6}/', $content, $matches)) {
            $paymentCode = $matches[0];
        }
    }
    
    if (empty($paymentCode)) {
        logWebhook(['status' => 'ignored', 'reason' => 'no_payment_code', 'data' => $data]);
        // Trả về success để SePay không retry
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'No payment code found - transaction ignored']);
        exit;
    }
    
    if ($transferAmount <= 0) {
        logWebhook(['status' => 'error', 'reason' => 'invalid_amount', 'amount' => $transferAmount]);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid amount']);
        exit;
    }
    
    // Tìm transaction bằng payment code
    $pdo = Database::getInstance();
    
    // Kiểm tra trùng lặp bằng SePay ID (theo khuyến nghị của SePay)
    if ($sepayId) {
        // Có thể lưu SePay ID vào database để tránh trùng lặp
        // Tạm thời chỉ check bằng payment code
    }
    
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE payment_code = :code AND status = 'PENDING'");
    $stmt->execute(['code' => $paymentCode]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        // Transaction not found - might be for a different system or already processed
        logWebhook([
            'status' => 'transaction_not_found',
            'payment_code' => $paymentCode,
            'sepay_id' => $sepayId
        ]);
        // Trả về success để SePay không retry
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Transaction not found or already processed']);
        exit;
    }
    
    // Verify amount matches (with small tolerance for rounding)
    $amountDiff = abs($transaction['amount'] - $transferAmount);
    if ($amountDiff > 100) { // Allow 100 VND difference
        logWebhook([
            'status' => 'amount_mismatch',
            'expected' => $transaction['amount'],
            'received' => $transferAmount,
            'difference' => $amountDiff,
            'payment_code' => $paymentCode
        ]);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Amount mismatch',
            'expected' => $transaction['amount'],
            'received' => $transferAmount
        ]);
        exit;
    }
    
    // Update transaction status to PAID
    Database::updateTransactionStatus($transaction['id'], 'PAID');
    
    logWebhook([
        'status' => 'success',
        'transaction_id' => $transaction['id'],
        'payment_code' => $paymentCode,
        'amount' => $transferAmount,
        'sepay_id' => $sepayId
    ]);
    
    // Return success response theo format SePay yêu cầu
    // Với API Key: HTTP Status Code phải là 201 hoặc 200 và có "success": true
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Transaction updated successfully',
        'transaction_id' => $transaction['id'],
        'payment_code' => $paymentCode,
        'amount' => $transferAmount
    ]);
    
} catch (Exception $e) {
    logWebhook([
        'status' => 'error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'data' => $data
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
?>

