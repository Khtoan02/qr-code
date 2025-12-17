<?php
$chartData = array_slice(array_reverse($transactions), 0, 10);
?>
<div class="space-y-6 animate-fade-in">
  <!-- Stats Row -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Revenue Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between h-40 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110 duration-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-emerald-600">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Tổng doanh thu</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo formatVND($stats['revenue']); ?></h3>
        </div>
        <div class="flex items-center gap-2 text-emerald-600 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
            </svg>
            +12% so với hôm qua
        </div>
    </div>

    <!-- Orders Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between h-40">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Tổng đơn hàng</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo $stats['total']; ?></h3>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
        </div>
         <div class="text-sm text-slate-400">
            <span class="text-slate-800 font-bold"><?php echo $stats['paid']; ?></span> thành công
        </div>
    </div>

    <!-- Pending Card -->
    <a href="?tab=HISTORY" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between h-40 cursor-pointer hover:border-amber-300 transition-colors">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Đơn chờ xử lý</p>
                <h3 class="text-3xl font-bold text-amber-500 mt-2"><?php echo $stats['pending']; ?></h3>
            </div>
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="text-sm text-amber-600 font-medium flex items-center gap-1">
            Cần xử lý ngay
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
            </svg>
        </div>
    </a>
  </div>

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[500px]">
     <!-- Chart Section -->
     <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
        <h4 class="font-bold text-slate-800 mb-6">Biểu đồ dòng tiền</h4>
        <div class="flex-1 min-h-0">
            <canvas id="revenueChart"></canvas>
        </div>
     </div>

     <!-- Recent Transactions List -->
     <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-slate-800">Giao dịch gần đây</h4>
            <a href="?tab=HISTORY" class="text-xs text-emerald-600 font-bold hover:underline">Xem tất cả</a>
        </div>
        <div class="flex-1 overflow-y-auto space-y-4 pr-2 scrollbar-hide">
            <?php foreach (array_slice($transactions, 0, 6) as $t): ?>
                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 <?php echo $t['status'] === 'PAID' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600'; ?>">
                            <?php if ($t['status'] === 'PAID'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800"><?php echo formatVND($t['amount']); ?></p>
                            <p class="text-xs text-slate-500 font-mono"><?php echo htmlspecialchars($t['payment_code']); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                         <span class="text-[10px] font-bold px-2 py-1 rounded-full <?php echo $t['status'] === 'PAID' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'; ?>">
                            <?php echo $t['status'] === 'PAID' ? 'Success' : 'Pending'; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="?tab=POS" class="block w-full py-3 mt-2 border border-dashed border-slate-300 rounded-xl text-slate-500 text-sm font-medium hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-600 transition-all text-center">
                + Tạo giao dịch mới
            </a>
        </div>
     </div>
  </div>
</div>

<script>
const chartData = <?php echo json_encode($chartData); ?>;
const ctx = document.getElementById('revenueChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(t => t.payment_code),
            datasets: [{
                label: 'Số tiền',
                data: chartData.map(t => parseFloat(t.amount)),
                backgroundColor: '#10b981',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000) + 'k';
                        }
                    }
                }
            }
        }
    });
}
</script>

