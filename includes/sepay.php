<?php
require_once __DIR__ . '/../config.php';

class SePayService {
    /**
     * Kiểm tra xem giao dịch đã thành công chưa bằng cách gọi API SePay
     */
    public static function checkPayment($paymentCode, $amount) {
        $corsProxy = 'https://corsproxy.io/?';
        $apiUrl = "https://my.sepay.vn/userapi/transactions/list?account_number=" . MAIN_ACCOUNT_NUMBER . "&limit=20";
        $fullUrl = $corsProxy . urlencode($apiUrl);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . SEPAY_API_KEY,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return false;
        }

        $data = json_decode($response, true);

        if (isset($data['status']) && $data['status'] === 200 && isset($data['transactions']) && is_array($data['transactions'])) {
            foreach ($data['transactions'] as $transaction) {
                $contentMatch = stripos($transaction['transaction_content'] ?? '', $paymentCode) !== false;
                $amountIn = floatval($transaction['amount_in'] ?? 0);
                $amountMatch = $amountIn >= $amount;

                if ($contentMatch && $amountMatch) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Tạo URL mã QR SePay
     */
    public static function getQRUrl($amount, $paymentCode) {
        // Sử dụng SePay QR URL trực tiếp (đảm bảo hoạt động)
        return "https://qr.sepay.vn/img?acc=" . VA_ACCOUNT_NUMBER . "&bank=MB&amount=" . $amount . "&des=" . urlencode($paymentCode);
    }
    
    /**
     * Lấy thông tin tài khoản để hiển thị
     */
    public static function getAccountInfo() {
        return [
            'account_number' => VA_ACCOUNT_NUMBER,
            'account_name' => defined('ACCOUNT_NAME') ? ACCOUNT_NAME : 'CÔNG TY TNHH PAYGEN',
            'bank_name' => defined('BANK_NAME') ? BANK_NAME : 'Ngân hàng Quân đội',
            'bank_code' => defined('BANK_CODE') ? BANK_CODE : 'MB'
        ];
    }
}
?>

