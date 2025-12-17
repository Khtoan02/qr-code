import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { DollarSign, ShoppingBag, Clock, ArrowUpRight, TrendingUp } from 'lucide-react';
import { getTransactions, getTotalRevenue } from '../services/mockDb';
import { PaymentTransaction, PaymentStatus } from '../types';

interface DashboardProps {
    onViewAll: () => void;
    onNewOrder: () => void;
}

export const AdminDashboard: React.FC<DashboardProps> = ({ onViewAll, onNewOrder }) => {
  const [transactions, setTransactions] = useState<PaymentTransaction[]>([]);
  const [revenue, setRevenue] = useState(0);

  useEffect(() => {
    const loadData = () => {
      const txs = getTransactions();
      setTransactions(txs);
      setRevenue(getTotalRevenue());
    };
    loadData();
    const interval = setInterval(loadData, 5000);
    return () => clearInterval(interval);
  }, []);

  const formatVND = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
  };

  // Chart Data: Last 7 transactions
  const chartData = transactions.slice(0, 10).reverse().map(t => ({
    name: t.paymentCode,
    amount: t.amount,
    status: t.status
  }));

  const pendingCount = transactions.filter(t => t.status === PaymentStatus.PENDING).length;
  const successCount = transactions.filter(t => t.status === PaymentStatus.PAID).length;

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Stats Row */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Revenue Card */}
        <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between h-40 relative overflow-hidden group">
            <div className="absolute right-0 top-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110 duration-500">
                <DollarSign size={100} className="text-emerald-600" />
            </div>
            <div>
                <p className="text-sm font-semibold text-slate-500 uppercase tracking-wider">Tổng doanh thu</p>
                <h3 className="text-3xl font-bold text-slate-800 mt-2">{formatVND(revenue)}</h3>
            </div>
            <div className="flex items-center gap-2 text-emerald-600 text-sm font-medium">
                <TrendingUp size={16} /> +12% so với hôm qua
            </div>
        </div>

        {/* Orders Card */}
        <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between h-40">
            <div className="flex justify-between items-start">
                <div>
                    <p className="text-sm font-semibold text-slate-500 uppercase tracking-wider">Tổng đơn hàng</p>
                    <h3 className="text-3xl font-bold text-slate-800 mt-2">{transactions.length}</h3>
                </div>
                <div className="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <ShoppingBag size={24} />
                </div>
            </div>
             <div className="text-sm text-slate-400">
                <span className="text-slate-800 font-bold">{successCount}</span> thành công
            </div>
        </div>

        {/* Pending Card */}
        <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between h-40 cursor-pointer hover:border-amber-300 transition-colors" onClick={onViewAll}>
            <div className="flex justify-between items-start">
                <div>
                    <p className="text-sm font-semibold text-slate-500 uppercase tracking-wider">Đơn chờ xử lý</p>
                    <h3 className="text-3xl font-bold text-amber-500 mt-2">{pendingCount}</h3>
                </div>
                <div className="p-3 bg-amber-50 text-amber-600 rounded-xl animate-pulse">
                    <Clock size={24} />
                </div>
            </div>
            <div className="text-sm text-amber-600 font-medium flex items-center gap-1">
                Cần xử lý ngay <ArrowUpRight size={14} />
            </div>
        </div>
      </div>

      {/* Main Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[500px]">
         {/* Chart Section */}
         <div className="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <h4 className="font-bold text-slate-800 mb-6">Biểu đồ dòng tiền</h4>
            <div className="flex-1 min-h-0">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={chartData} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                        <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: '#94a3b8', fontSize: 10}} />
                        <YAxis axisLine={false} tickLine={false} tick={{fill: '#94a3b8', fontSize: 10}} tickFormatter={(val) => `${val/1000}k`} />
                        <Tooltip 
                            cursor={{fill: '#f8fafc'}}
                            contentStyle={{borderRadius: '12px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'}}
                            formatter={(val: number) => formatVND(val)}
                        />
                        <Bar dataKey="amount" fill="#10b981" radius={[4, 4, 0, 0]} barSize={40} />
                    </BarChart>
                </ResponsiveContainer>
            </div>
         </div>

         {/* Recent Transactions List (Mini) */}
         <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <div className="flex justify-between items-center mb-6">
                <h4 className="font-bold text-slate-800">Giao dịch gần đây</h4>
                <button onClick={onViewAll} className="text-xs text-emerald-600 font-bold hover:underline">Xem tất cả</button>
            </div>
            <div className="flex-1 overflow-y-auto space-y-4 pr-2 scrollbar-thin scrollbar-thumb-slate-200">
                {transactions.slice(0, 6).map(t => (
                    <div key={t.id} className="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                        <div className="flex items-center gap-3">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center shrink-0 ${t.status === 'PAID' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600'}`}>
                                {t.status === 'PAID' ? <DollarSign size={16} /> : <Clock size={16} />}
                            </div>
                            <div>
                                <p className="text-sm font-bold text-slate-800">{formatVND(t.amount)}</p>
                                <p className="text-xs text-slate-500 font-mono">{t.paymentCode}</p>
                            </div>
                        </div>
                        <div className="text-right">
                             <span className={`text-[10px] font-bold px-2 py-1 rounded-full ${t.status === 'PAID' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'}`}>
                                {t.status === 'PAID' ? 'Success' : 'Pending'}
                            </span>
                        </div>
                    </div>
                ))}
                <button 
                    onClick={onNewOrder}
                    className="w-full py-3 mt-2 border border-dashed border-slate-300 rounded-xl text-slate-500 text-sm font-medium hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-600 transition-all"
                >
                    + Tạo giao dịch mới
                </button>
            </div>
         </div>
      </div>
    </div>
  );
};
