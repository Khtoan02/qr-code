<?php
/**
 * Quick Setup Page - Run this once to ensure database is set up correctly
 */
require_once 'config.php';

$message = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        // Force database connection and setup
        $pdo = getDB();
        
        // Verify tables exist
        $tables = ['transactions', 'users'];
        $missing = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                $missing[] = $table;
            }
        }
        
        if (empty($missing)) {
            $success = true;
            $message = "✅ Database đã được thiết lập thành công!";
        } else {
            $error = "Các bảng sau chưa được tạo: " . implode(', ', $missing);
        }
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Check current status
$status = [];
try {
    $pdo = getDB();
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $status['database'] = 'Connected';
    $status['tables'] = $tables;
    $status['transactions_count'] = 0;
    $status['users_count'] = 0;
    
    if (in_array('transactions', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions");
        $status['transactions_count'] = $stmt->fetch()['count'];
    }
    
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $status['users_count'] = $stmt->fetch()['count'];
    }
} catch (Exception $e) {
    $status['database'] = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - PayGen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">PayGen Setup</h1>
                <p class="text-slate-500">Kiểm tra và thiết lập database</p>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Status -->
            <div class="bg-slate-50 rounded-xl p-6 mb-6">
                <h2 class="font-bold text-slate-800 mb-4">Trạng thái Database</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Kết nối Database:</span>
                        <span class="font-medium <?php echo strpos($status['database'], 'Error') === false ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo htmlspecialchars($status['database']); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Bảng transactions:</span>
                        <span class="font-medium <?php echo in_array('transactions', $status['tables'] ?? []) ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo in_array('transactions', $status['tables'] ?? []) ? '✓ Có (' . $status['transactions_count'] . ' records)' : '✗ Chưa có'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Bảng users:</span>
                        <span class="font-medium <?php echo in_array('users', $status['tables'] ?? []) ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo in_array('users', $status['tables'] ?? []) ? '✓ Có (' . $status['users_count'] . ' users)' : '✗ Chưa có'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Setup Button -->
            <form method="POST" class="mb-6">
                <button 
                    type="submit" 
                    name="setup"
                    class="w-full py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-colors"
                >
                    Thiết lập Database
                </button>
            </form>

            <!-- Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                <p class="font-bold mb-2">ℹ️ Thông tin:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Hệ thống sẽ tự động tạo database và bảng khi cần</li>
                    <li>Nếu gặp lỗi, vui lòng kiểm tra cấu hình trong <code>config.php</code></li>
                    <li>Tài khoản mặc định: <strong>admin</strong> / <strong>123456</strong></li>
                </ul>
            </div>

            <div class="mt-6 text-center">
                <a href="/index.php" class="text-emerald-600 hover:text-emerald-700 font-medium">← Về trang chủ</a>
                <span class="mx-2">|</span>
                <a href="/login.php" class="text-emerald-600 hover:text-emerald-700 font-medium">Đăng nhập →</a>
            </div>
        </div>
    </div>
</body>
</html>

