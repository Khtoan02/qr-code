<?php
/**
 * Export Transactions to Excel
 * Xuất danh sách giao dịch thành công ra file Excel (CSV format)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
requireAuth();

$filter = $_GET['filter'] ?? 'PAID'; // Chỉ export giao dịch thành công
$transactions = Database::getTransactions($filter);

// Filter chỉ lấy giao dịch thành công
$paidTransactions = array_filter($transactions, function($t) {
    return $t['status'] === 'PAID';
});

// Set headers for Excel download (CSV format - Excel có thể mở được)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="danh_sach_giao_dich_' . date('Y-m-d_His') . '.csv"');
header('Cache-Control: max-age=0');

// BOM UTF-8 để Excel hiển thị tiếng Việt đúng
echo "\xEF\xBB\xBF";

// Tạo CSV format (Excel có thể mở được, đơn giản và nhẹ)
$output = fopen('php://output', 'w');

// Header row
fputcsv($output, [
    'STT',
    'Thời gian',
    'Mã giao dịch',
    'Khách hàng',
    'Ghi chú',
    'Số tiền (VNĐ)',
    'Trạng thái'
], ';'); // Dùng ; để Excel tự động nhận diện

// Data rows
$stt = 1;
$totalAmount = 0;
foreach ($paidTransactions as $t) {
    fputcsv($output, [
        $stt,
        date('d/m/Y H:i:s', strtotime($t['created_at'])),
        $t['payment_code'],
        $t['customer_name'],
        $t['description'] ?? '',
        number_format($t['amount'], 0, ',', '.'),
        'Thành công'
    ], ';');
    $totalAmount += $t['amount'];
    $stt++;
}

// Empty row
fputcsv($output, [], ';');

// Summary row
fputcsv($output, [
    'TỔNG CỘNG',
    '',
    '',
    '',
    '',
    number_format($totalAmount, 0, ',', '.'),
    count($paidTransactions) . ' giao dịch'
], ';');

fclose($output);
exit;
?>
