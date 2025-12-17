<?php
require_once 'config.php';
require_once 'includes/db.php';
requireAuth();

// Handle POST requests BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payment'])) {
    require_once 'includes/sepay.php';
    
    // Get amount from hidden input (raw value) or from formatted input
    $amountRaw = $_POST['amountValue'] ?? $_POST['amount'] ?? '0';
    $amount = floatval(str_replace('.', '', $amountRaw));
    $note = $_POST['note'] ?? '';
    
    if ($amount > 0) {
        $id = uniqid('tx_', true);
        $paymentCode = generatePaymentCode();
        
        $transaction = [
            'id' => $id,
            'payment_code' => $paymentCode,
            'amount' => $amount,
            'description' => $note ?: $paymentCode,
            'customer_name' => 'Khách lẻ',
            'status' => 'PENDING',
            'created_at' => time() * 1000
        ];
        
        Database::saveTransaction($transaction);
        header('Location: ?tab=POS&created=' . $id);
        exit;
    }
}

$activeTab = $_GET['tab'] ?? 'DASHBOARD';
$stats = Database::getTransactionStats();
$transactions = Database::getTransactions();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Portal - PayGen</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="alternate icon" href="/assets/images/favicon.svg">
    <link rel="shortcut icon" href="/assets/images/favicon.svg">
    <meta name="theme-color" content="#10b981">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
      body {
        font-family: 'Inter', sans-serif;
      }
      .scrollbar-hide::-webkit-scrollbar {
          display: none;
      }
      .scrollbar-hide {
          -ms-overflow-style: none;
          scrollbar-width: none;
      }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="flex h-screen">
      <!-- Sidebar -->
      <aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-20">
        <div class="h-16 flex items-center justify-center border-b border-slate-800 px-4">
            <div class="flex items-center gap-2 font-bold text-xl tracking-tight">
                <img src="/assets/images/icon.svg" alt="PayGen" class="w-6 h-6" />
                PayGen <span class="text-slate-500 text-sm font-normal">Admin</span>
            </div>
        </div>

        <nav class="flex-1 py-6 px-3 space-y-2">
            <a href="?tab=DASHBOARD" class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 <?php echo $activeTab === 'DASHBOARD' ? 'bg-emerald-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span class="font-medium text-sm">Tổng quan</span>
            </a>
            <a href="?tab=POS" class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 <?php echo $activeTab === 'POS' ? 'bg-emerald-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>
                </svg>
                <span class="font-medium text-sm">Tạo thanh toán</span>
            </a>
            <a href="?tab=HISTORY" class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 <?php echo $activeTab === 'HISTORY' ? 'bg-emerald-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18M7 16l4-4 4 4 6-6"/>
                </svg>
                <span class="font-medium text-sm">Lịch sử giao dịch</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="/logout.php" class="flex items-center gap-3 w-full p-2 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                <span class="font-medium text-sm">Đăng xuất</span>
            </a>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-10">
            <h2 class="text-lg font-bold text-slate-700">
                <?php
                if ($activeTab === 'DASHBOARD') echo 'Bảng điều khiển';
                elseif ($activeTab === 'POS') echo 'Tạo giao dịch mới';
                else echo 'Lịch sử giao dịch';
                ?>
            </h2>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 pl-4 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs">
                        AD
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Administrator</p>
                        <p class="text-[10px] text-slate-400 uppercase">System Admin</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
            <?php if ($activeTab === 'DASHBOARD'): ?>
                <?php include 'includes/dashboard.php'; ?>
            <?php elseif ($activeTab === 'POS'): ?>
                <?php include 'includes/create_payment.php'; ?>
            <?php else: ?>
                <?php include 'includes/history.php'; ?>
            <?php endif; ?>
        </main>
      </div>
    </div>
    <script src="/assets/js/admin.js"></script>
</body>
</html>

