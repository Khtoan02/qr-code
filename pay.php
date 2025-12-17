<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/sepay.php';

$id = $_GET['id'] ?? '';
$transaction = $id ? Database::getTransactionById($id) : null;

if (!$transaction) {
    header('Location: /index.php');
    exit;
}

// Auto-check payment status if pending
if ($transaction['status'] === 'PENDING') {
    $isPaid = SePayService::checkPayment($transaction['payment_code'], $transaction['amount']);
    if ($isPaid) {
        Database::updateTransactionStatus($id, 'PAID');
        $transaction = Database::getTransactionById($id);
    }
}

$qrUrl = SePayService::getQRUrl($transaction['amount'], $transaction['payment_code']);
$accountInfo = SePayService::getAccountInfo();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thanh toán - PayGen</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="alternate icon" href="/assets/images/favicon.svg">
    <link rel="shortcut icon" href="/assets/images/favicon.svg">
    <meta name="theme-color" content="#10b981">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
      body {
        font-family: 'Inter', sans-serif;
      }
      @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
      }
      @keyframes scale-in {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
      .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
      .animate-scale-in { animation: scale-in 0.5s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-100">
    <?php if ($transaction['status'] === 'PAID'): ?>
        <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-xl p-8 text-center animate-fade-in">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Giao dịch đã đóng</h2>
                <p class="text-slate-500 mb-6">Đơn hàng này đã được thanh toán thành công và link truy cập đã hết hiệu lực bảo mật.</p>
                <a href="/index.php" class="flex items-center justify-center gap-2 w-full py-3 bg-slate-100 text-slate-600 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Về trang chủ
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="flex items-center justify-center min-h-screen bg-slate-100 p-4 font-sans">
            <div class="w-full max-w-sm relative">
                <!-- Card Container -->
                <div id="paymentCard" class="bg-white rounded-[2rem] shadow-2xl shadow-emerald-900/10 overflow-hidden border border-slate-100 relative transition-all duration-500 border-t-8 border-t-emerald-500">
                    
                    <!-- Decorative Punch Holes (Tạo hiệu ứng vé/bill) -->
                    <div class="absolute -left-3 top-[62%] w-6 h-6 bg-slate-100 rounded-full z-10"></div>
                    <div class="absolute -right-3 top-[62%] w-6 h-6 bg-slate-100 rounded-full z-10"></div>
                    <div class="absolute left-4 right-4 top-[62%] border-b-2 border-dashed border-slate-100"></div>

                    <!-- 1. Header Section -->
                    <div class="p-8 pb-4 text-center">
                        <!-- Status Badge -->
                        <div id="statusBadge" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-5 shadow-sm bg-emerald-50 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Đang chờ thanh toán
                        </div>
                        
                        <!-- Amount -->
                        <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight flex justify-center items-baseline gap-1">
                            <?php echo number_format($transaction['amount'], 0, ',', '.'); ?> <span class="text-2xl text-slate-400 font-semibold">đ</span>
                        </h3>
                        <p class="text-slate-400 text-xs font-medium mt-1">Quét mã để thanh toán tự động</p>
                    </div>

                    <!-- 2. QR Code Section -->
                    <div class="p-8 pt-2 pb-12 flex justify-center">
                        <!-- QR Viewfinder UI -->
                        <div id="qrContainer" class="relative group perspective-1000">
                            <!-- Glow Effect Background -->
                            <div class="absolute -inset-2 bg-gradient-to-br from-emerald-400/30 to-teal-400/30 rounded-3xl blur-xl opacity-50 group-hover:opacity-100 transition duration-500"></div>
                            
                            <div class="relative bg-white p-4 rounded-3xl shadow-sm border border-slate-100 overflow-hidden transform transition-all duration-300 group-hover:scale-[1.02]">
                                <!-- Viewfinder Corners (Góc ngắm) -->
                                <div class="absolute top-3 left-3 w-6 h-6 border-t-4 border-l-4 border-emerald-500 rounded-tl-xl"></div>
                                <div class="absolute top-3 right-3 w-6 h-6 border-t-4 border-r-4 border-emerald-500 rounded-tr-xl"></div>
                                <div class="absolute bottom-3 left-3 w-6 h-6 border-b-4 border-l-4 border-emerald-500 rounded-bl-xl"></div>
                                <div class="absolute bottom-3 right-3 w-6 h-6 border-b-4 border-r-4 border-emerald-500 rounded-br-xl"></div>
                                
                                <!-- QR Image -->
                                <img 
                                    id="qrImage"
                                    src="<?php echo htmlspecialchars($qrUrl); ?>" 
                                    alt="QR Payment" 
                                    class="w-56 h-56 object-contain mix-blend-multiply transition-all duration-500" 
                                    onerror="this.onerror=null; this.src='https://qr.sepay.vn/img?acc=<?php echo VA_ACCOUNT_NUMBER; ?>&bank=MB&amount=<?php echo $transaction['amount']; ?>&des=<?php echo urlencode($transaction['payment_code']); ?>'"
                                />
                                
                                <!-- Center Logo Overlay (PayGen Logo) -->
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-lg p-1.5 z-10">
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white relative overflow-hidden">
                                        <div class="absolute inset-0 bg-white/20 animate-pulse rounded-full"></div>
                                        <img src="/assets/images/icon.svg" alt="PayGen" class="w-6 h-6 relative z-10 filter brightness-0 invert" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success State UI (Hidden by default) -->
                        <div id="successIcon" class="hidden w-56 h-56 flex flex-col items-center justify-center bg-green-50/50 rounded-3xl border border-green-100">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-4 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                            </div>
                            <p class="text-green-700 font-bold text-lg">Đã nhận tiền</p>
                            <p class="text-green-600/70 text-xs mt-1">Cảm ơn quý khách</p>
                        </div>
                    </div>

                    <!-- 3. Info Details Section -->
                    <div class="bg-slate-50 p-6 pt-10 border-t border-slate-100">
                        <!-- Bank Rows -->
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Ngân hàng</span>
                            <span class="text-slate-700 font-bold text-sm bg-white px-2 py-1 rounded border border-slate-100 shadow-sm"><?php echo htmlspecialchars($accountInfo['bank_name']); ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Chủ tài khoản</span>
                            <span class="text-slate-700 font-bold text-sm uppercase"><?php echo htmlspecialchars($accountInfo['account_name']); ?></span>
                        </div>
                        
                        <!-- Account Number Box (Copyable) -->
                        <div 
                            id="copyAccountBtn"
                            class="bg-white p-4 rounded-xl border border-dashed border-emerald-200 flex justify-between items-center mt-6 cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group active:scale-[0.98]"
                        >
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase mb-1">Số tài khoản</div>
                                <div class="text-emerald-900 font-mono font-bold text-lg tracking-wide"><?php echo htmlspecialchars($accountInfo['account_number']); ?></div>
                            </div>
                            <div id="copyIcon" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-emerald-600">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Description & Footer Note -->
                        <div class="text-center mt-6">
                            <p class="text-slate-400 text-xs">Nội dung: <span class="font-bold text-slate-600"><?php echo htmlspecialchars($transaction['payment_code']); ?></span></p>
                            <?php if (!empty($transaction['description']) && $transaction['description'] !== $transaction['payment_code']): ?>
                                <p class="text-slate-400 text-xs mt-1">Ghi chú: <span class="font-medium text-slate-600"><?php echo htmlspecialchars($transaction['description']); ?></span></p>
                            <?php endif; ?>
                            <div id="pendingStatus" class="mt-4 flex items-center justify-center gap-2 text-[10px] text-slate-400 bg-slate-100 py-1.5 px-3 rounded-full w-fit mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-500">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                Hệ thống đang kiểm tra tự động
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Optional) -->
                <div class="mt-6 flex gap-3">
                    <button onclick="downloadQR()" class="flex-1 py-3 bg-white text-slate-600 font-bold text-sm rounded-xl shadow-sm border border-slate-200 hover:bg-slate-50 active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Lưu ảnh
                    </button>
                    <button onclick="copyLink()" class="flex-1 py-3 bg-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        Copy Link
                    </button>
                </div>
            </div>
        </div>

        <script>
        let isPaid = false;
        let checkInterval = null;
        let copied = false;

        // Copy account number
        document.getElementById('copyAccountBtn').addEventListener('click', function() {
            const accountNo = '<?php echo htmlspecialchars($accountInfo['account_number'], ENT_QUOTES); ?>';
            const bankName = '<?php echo htmlspecialchars($accountInfo['bank_name'], ENT_QUOTES); ?>';
            navigator.clipboard.writeText(`${accountNo} ${bankName}`).then(() => {
                copied = true;
                const copyIcon = document.getElementById('copyIcon');
                copyIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                setTimeout(() => {
                    copied = false;
                    copyIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-emerald-600"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
                }, 2000);
            });
        });

        // Copy payment link
        function copyLink() {
            const link = window.location.href;
            navigator.clipboard.writeText(link).then(() => {
                alert('Đã copy link thanh toán!');
            });
        }

        // Download QR image
        function downloadQR() {
            const qrImg = document.getElementById('qrImage');
            const link = document.createElement('a');
            link.download = 'qr-payment-<?php echo $transaction['payment_code']; ?>.png';
            link.href = qrImg.src;
            link.click();
        }

        function updateToSuccess() {
            if (isPaid) return;
            isPaid = true;

            if (checkInterval) {
                clearInterval(checkInterval);
            }

            const qrContainer = document.getElementById('qrContainer');
            const successIcon = document.getElementById('successIcon');
            const statusBadge = document.getElementById('statusBadge');
            const pendingStatus = document.getElementById('pendingStatus');
            const paymentCard = document.getElementById('paymentCard');

            // Update card border
            paymentCard.classList.remove('border-t-emerald-500');
            paymentCard.classList.add('border-t-green-500');

            // Update status badge
            statusBadge.className = 'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-5 shadow-sm bg-green-100 text-green-700';
            statusBadge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Giao dịch thành công';

            // Hide QR, show success
            qrContainer.style.opacity = '0';
            qrContainer.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                qrContainer.classList.add('hidden');
                successIcon.classList.remove('hidden');
                successIcon.style.opacity = '0';
                successIcon.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    successIcon.style.opacity = '1';
                    successIcon.style.transform = 'scale(1)';
                }, 50);
            }, 300);

            // Hide pending status
            if (pendingStatus) {
                pendingStatus.classList.add('hidden');
            }

            // Auto redirect after 3 seconds
            setTimeout(() => {
                window.location.href = '/index.php';
            }, 3000);
        }

        // Auto-check payment status
        checkInterval = setInterval(() => {
            if (isPaid) {
                clearInterval(checkInterval);
                return;
            }

            fetch('/api/check_payment.php?id=<?php echo $id; ?>')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'PAID' && !isPaid) {
                        updateToSuccess();
                    }
                })
                .catch(err => {
                    console.error('Error checking payment:', err);
                });
        }, 2000);
        </script>
    <?php endif; ?>
</body>
</html>
