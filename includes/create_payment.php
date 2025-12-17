<?php
// POST handling is now done in admin.php before any output
// This file only handles display logic

$createdId = $_GET['created'] ?? null;
$currentOrder = $createdId ? Database::getTransactionById($createdId) : null;
?>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full">
    <!-- Left: Form -->
    <div class="lg:col-span-5 flex flex-col gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Thông tin thanh toán</h3>
            
            <form method="POST" action="?tab=POS" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-500 mb-2">Số tiền cần thu</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="amountInput"
                            class="w-full text-3xl font-bold text-emerald-600 border-b-2 border-slate-200 focus:border-emerald-500 outline-none py-2 bg-transparent placeholder-slate-200"
                            placeholder="0"
                            required
                            oninput="formatAmount(this)"
                        />
                        <input type="hidden" id="amountValue" name="amount" />
                        <span class="absolute right-0 bottom-3 text-slate-400 font-medium">VNĐ</span>
                    </div>
                    <!-- Quick Select -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <?php foreach ([50000, 100000, 200000, 500000] as $v): ?>
                            <button 
                                type="button"
                                onclick="setAmount(<?php echo $v; ?>)"
                                class="px-3 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg text-xs font-medium transition-colors"
                            >
                                <?php echo ($v/1000); ?>k
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-500 mb-2">Ghi chú đơn hàng</label>
                    <textarea 
                        name="note"
                        class="w-full p-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-100 outline-none text-sm resize-none"
                        rows="3"
                        placeholder="Nhập ghi chú cho đơn hàng này..."
                    ></textarea>
                </div>

                <button 
                    type="submit"
                    name="create_payment"
                    class="w-full py-3.5 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 transform active:scale-[0.98] transition-all shadow-lg shadow-emerald-200"
                >
                    Tạo Mã QR
                </button>
            </form>
        </div>
        
        <div class="bg-emerald-50 p-6 rounded-2xl border border-emerald-100 text-emerald-800 text-sm">
            <p class="font-bold flex items-center gap-2 mb-2">
                <img src="/assets/images/icon.svg" alt="PayGen" class="w-4 h-4" />
                Mẹo quản lý
            </p>
            <p class="opacity-80">Mã QR sẽ được tạo tự động dựa trên số tiền. Hệ thống sẽ tự động kiểm tra trạng thái thanh toán mỗi 3 giây.</p>
        </div>
    </div>

    <!-- Right: Result / QR -->
    <div class="lg:col-span-7">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 h-full flex flex-col">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-700">Vé thanh toán</h3>
                <?php if ($currentOrder): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $currentOrder['status'] === 'PAID' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?>">
                        <?php echo $currentOrder['status'] === 'PAID' ? 'ĐÃ THANH TOÁN' : 'CHỜ THANH TOÁN'; ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="flex-1 flex flex-col items-center justify-center p-8 bg-slate-50/50">
                <?php if (!$currentOrder): ?>
                    <div class="text-center text-slate-400">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="opacity-50">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                            </svg>
                        </div>
                        <p>Chưa có đơn hàng nào được tạo</p>
                    </div>
                <?php else: 
                    require_once __DIR__ . '/../includes/sepay.php';
                    $qrUrl = SePayService::getQRUrl($currentOrder['amount'], $currentOrder['payment_code']);
                ?>
                    <div class="w-full max-w-md bg-white p-6 rounded-2xl shadow-xl border border-slate-100 animate-fade-in">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-slate-500 text-xs uppercase font-bold">Tổng thanh toán</p>
                                <h2 class="text-3xl font-extrabold text-slate-800">
                                    <?php echo formatVND($currentOrder['amount']); ?>
                                </h2>
                            </div>
                            <div class="text-right">
                                <p class="text-slate-500 text-xs uppercase font-bold">Mã GD</p>
                                <p class="font-mono font-bold text-emerald-600"><?php echo htmlspecialchars($currentOrder['payment_code']); ?></p>
                            </div>
                        </div>

                        <div class="flex flex-col items-center mb-6">
                            <?php if ($currentOrder['status'] === 'PAID'): ?>
                                <div class="w-48 h-48 bg-green-50 rounded-xl flex flex-col items-center justify-center border border-green-100 text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                    <span class="font-bold">Thành Công</span>
                                </div>
                            <?php else: ?>
                                <img 
                                    src="<?php echo htmlspecialchars($qrUrl); ?>" 
                                    alt="QR Code"
                                    class="w-48 h-48 object-contain mix-blend-multiply border border-slate-100 rounded-lg p-2 bg-white"
                                    onerror="this.src='https://qr.sepay.vn/img?acc=<?php echo VA_ACCOUNT_NUMBER; ?>&bank=MB&amount=<?php echo $currentOrder['amount']; ?>&des=<?php echo urlencode($currentOrder['payment_code']); ?>'"
                                />
                                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 w-full">
                                    <div class="text-xs text-blue-800 space-y-1">
                                        <div class="flex justify-between">
                                            <span class="font-medium">STK:</span>
                                            <span class="font-mono font-bold"><?php echo htmlspecialchars(SePayService::getAccountInfo()['account_number']); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Chủ TK:</span>
                                            <span class="font-bold"><?php echo htmlspecialchars(SePayService::getAccountInfo()['account_name']); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Nội dung:</span>
                                            <span class="font-mono font-bold"><?php echo htmlspecialchars($currentOrder['payment_code']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <button onclick="copyLink('<?php echo $currentOrder['id']; ?>')" class="flex items-center justify-center gap-2 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-medium text-slate-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                                Copy Link
                            </button>
                            <a href="/pay.php?id=<?php echo $currentOrder['id']; ?>" target="_blank" class="flex items-center justify-center gap-2 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                                Mở Tab Mới
                            </a>
                        </div>

                            <?php if ($currentOrder['status'] === 'PENDING'): ?>
                                <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    Đang chờ khách quét mã...
                                </div>
                            <?php endif; ?>

                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <a href="?tab=POS" class="block w-full text-slate-400 hover:text-slate-600 text-sm font-medium text-center">
                                Tạo đơn hàng mới
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function formatAmount(input) {
    // Remove all non-digit characters
    let value = input.value.replace(/\D/g, '');
    // Store raw value in hidden input
    document.getElementById('amountValue').value = value;
    // Format display value
    if (value) {
        input.value = parseInt(value).toLocaleString('vi-VN');
    } else {
        input.value = '';
    }
}

function setAmount(amount) {
    document.getElementById('amountInput').value = amount.toLocaleString('vi-VN');
    document.getElementById('amountValue').value = amount;
}

function copyLink(id) {
    const link = window.location.origin + '/pay.php?id=' + id;
    navigator.clipboard.writeText(link).then(() => {
        alert('Đã copy link!');
    });
}

// Auto-check payment status
<?php if ($currentOrder && $currentOrder['status'] === 'PENDING'): ?>
setInterval(() => {
    fetch('/api/check_payment.php?id=<?php echo $currentOrder['id']; ?>')
        .then(r => r.json())
        .then(data => {
            if (data.status === 'PAID') {
                location.reload();
            }
        });
}, 3000);
<?php endif; ?>
</script>

