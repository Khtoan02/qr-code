<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PayGen Gateway - Hệ thống thanh toán</title>
    
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
      .scrollbar-hide::-webkit-scrollbar {
          display: none;
      }
      .scrollbar-hide {
          -ms-overflow-style: none;
          scrollbar-width: none;
      }
      
      @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
      }
      @keyframes slide-up {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
      }
      @keyframes zoom-in {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
      
      .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
      .animate-slide-up { animation: slide-up 0.4s ease-out forwards; }
      .animate-zoom-in { animation: zoom-in 0.3s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-emerald-50 flex items-center justify-center p-6">
      <div class="text-center max-w-md">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-[2rem] text-emerald-600 mb-8 shadow-xl shadow-emerald-100">
            <img src="/assets/images/logo.svg" alt="PayGen Logo" class="w-16 h-16" />
        </div>
        
        <h1 class="text-4xl font-black text-slate-800 mb-4 tracking-tight">PayGen <span class="text-emerald-600">Gateway</span></h1>
        <p class="text-lg text-slate-500 font-medium leading-relaxed mb-8">
            Hệ thống thanh toán nội bộ & quản lý giao dịch tập trung.
        </p>

        <div class="flex flex-col gap-4 items-center">
            <a href="/login.php" class="w-full sm:w-64 py-4 bg-white text-emerald-700 font-bold rounded-2xl shadow-lg shadow-slate-200 border border-slate-100 hover:bg-emerald-50 transition-all active:scale-95 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                </svg>
                Đăng nhập quản trị
            </a>
        </div>

        <div class="mt-12 flex items-center justify-center gap-2 text-slate-400 text-xs font-semibold uppercase tracking-wider">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Hệ thống nội bộ
        </div>
      </div>
    </div>
</body>
</html>

