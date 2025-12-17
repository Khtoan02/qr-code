<?php
$filter = $_GET['filter'] ?? 'ALL';
$filteredTransactions = Database::getTransactions($filter);
$search = $_GET['search'] ?? '';

if ($search) {
    $filteredTransactions = array_filter($filteredTransactions, function($t) use ($search) {
        return stripos($t['payment_code'], $search) !== false || 
               stripos($t['customer_name'], $search) !== false;
    });
}
?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-full">
    <div class="p-5 border-b border-slate-100">
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-2">
                <a href="?tab=HISTORY&filter=ALL" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'ALL' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                    Tất cả
                </a>
                <a href="?tab=HISTORY&filter=PAID" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'PAID' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100'; ?>">
                    Thành công
                </a>
                <a href="?tab=HISTORY&filter=PENDING" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'PENDING' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'; ?>">
                    Đang chờ
                </a>
            </div>
            <?php 
            $paidCount = count(array_filter($filteredTransactions, function($t) { return $t['status'] === 'PAID'; }));
            if ($paidCount > 0): 
            ?>
                <a 
                    href="/api/export_excel.php?filter=PAID" 
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors flex items-center gap-2 shadow-sm"
                    target="_blank"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Xuất Excel (<?php echo $paidCount; ?>)
                </a>
            <?php endif; ?>
        </div>
        <form method="GET" class="relative">
            <input type="hidden" name="tab" value="HISTORY">
            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" name="search" placeholder="Tìm mã giao dịch..." value="<?php echo htmlspecialchars($search); ?>" class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-emerald-500 w-64"/>
        </form>
    </div>

    <div class="flex-1 overflow-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 sticky top-0 z-10">
                <tr>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Thời gian</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Mã GD</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Khách hàng</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Ghi chú</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase text-right">Số tiền</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase text-center">Trạng thái</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($filteredTransactions)): ?>
                    <tr>
                        <td colSpan="7" class="p-8 text-center text-slate-400">Không tìm thấy dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filteredTransactions as $t): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 text-sm text-slate-600">
                                <?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?>
                            </td>
                            <td class="p-4 text-sm font-mono font-medium text-slate-800"><?php echo htmlspecialchars($t['payment_code']); ?></td>
                            <td class="p-4 text-sm text-slate-600"><?php echo htmlspecialchars($t['customer_name']); ?></td>
                            <td class="p-4 text-sm text-slate-600 max-w-xs">
                                <?php 
                                $description = $t['description'] ?? '';
                                if (empty($description) || $description === $t['payment_code']) {
                                    echo '<span class="text-slate-400">-</span>';
                                } else {
                                    echo '<div class="truncate max-w-[200px]" title="' . htmlspecialchars($description) . '">' . htmlspecialchars($description) . '</div>';
                                }
                                ?>
                            </td>
                            <td class="p-4 text-sm font-bold text-slate-800 text-right">
                                <?php echo formatVND($t['amount']); ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize <?php echo $t['status'] === 'PAID' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'; ?>">
                                    <?php echo $t['status'] === 'PAID' ? 'Thành công' : 'Chờ xử lý'; ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="/pay.php?id=<?php echo $t['id']; ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

