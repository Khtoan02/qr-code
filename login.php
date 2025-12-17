<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['is_authenticated'] = true;
        $_SESSION['username'] = $username;
        header('Location: /admin.php');
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng nhập - PayGen Admin</title>
    
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
    </style>
</head>
<body class="bg-slate-100">
    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-sm rounded-3xl shadow-xl p-8 border border-slate-100">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-50 rounded-2xl text-emerald-600 mb-4 shadow-sm">
                <img src="/assets/images/icon.svg" alt="PayGen" class="w-10 h-10" />
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Admin Portal</h1>
            <p class="text-slate-400 text-sm">Đăng nhập để quản lý thanh toán</p>
        </div>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Tài khoản</label>
                <input 
                    type="text" 
                    name="username"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-emerald-200 outline-none text-slate-700 font-medium"
                    placeholder="Nhập tên đăng nhập"
                    required
                />
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Mật khẩu</label>
                <input 
                    type="password" 
                    name="password"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-emerald-200 outline-none text-slate-700 font-medium"
                    placeholder="••••••"
                    required
                />
            </div>

            <?php if ($error): ?>
                <p class="text-red-500 text-xs text-center"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <button 
                type="submit"
                class="w-full py-4 bg-emerald-600 text-white rounded-xl font-bold text-lg hover:bg-emerald-700 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-200 mt-4"
            >
                Đăng Nhập
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="/index.php" class="text-slate-400 text-xs hover:text-emerald-600">Quay về trang chủ</a>
        </div>
      </div>
    </div>
</body>
</html>

