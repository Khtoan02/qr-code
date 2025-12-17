import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { PaymentTransaction, PaymentStatus } from '../types';
import { saveTransaction, getTransactions, updateTransactionStatus, getTransactionById } from '../services/mockDb';
import { checkSePayPayment, getSePayQRUrl } from '../services/sepayService';
import { AdminDashboard } from './AdminDashboard';
import { 
  LayoutDashboard, PlusCircle, History, LogOut, 
  Menu, Bell, ChevronDown, Search, Filter, 
  Copy, ExternalLink, CheckCircle, RefreshCw, XCircle, Leaf
} from 'lucide-react';

type Tab = 'DASHBOARD' | 'POS' | 'HISTORY';

export const AdminPortal: React.FC = () => {
  const [activeTab, setActiveTab] = useState<Tab>('DASHBOARD');
  const [isSidebarOpen, setSidebarOpen] = useState(true);
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.removeItem('isAuthenticated');
    navigate('/login');
  };

  return (
    <div className="flex h-screen bg-slate-50 font-sans text-slate-900">
      {/* --- SIDEBAR --- */}
      <aside 
        className={`${isSidebarOpen ? 'w-64' : 'w-20'} bg-slate-900 text-white transition-all duration-300 flex flex-col shadow-xl z-20`}
      >
        {/* Brand */}
        <div className="h-16 flex items-center justify-center border-b border-slate-800">
          {isSidebarOpen ? (
             <div className="flex items-center gap-2 font-bold text-xl tracking-tight">
                <Leaf className="text-emerald-500" /> PayGen <span className="text-slate-500 text-sm font-normal">Admin</span>
             </div>
          ) : (
             <Leaf className="text-emerald-500" />
          )}
        </div>

        {/* Navigation */}
        <nav className="flex-1 py-6 px-3 space-y-2">
            <NavItem 
                icon={<LayoutDashboard size={20} />} 
                label="Tổng quan" 
                active={activeTab === 'DASHBOARD'} 
                isOpen={isSidebarOpen}
                onClick={() => setActiveTab('DASHBOARD')}
            />
            <NavItem 
                icon={<PlusCircle size={20} />} 
                label="Tạo thanh toán" 
                active={activeTab === 'POS'} 
                isOpen={isSidebarOpen}
                onClick={() => setActiveTab('POS')}
            />
            <NavItem 
                icon={<History size={20} />} 
                label="Lịch sử giao dịch" 
                active={activeTab === 'HISTORY'} 
                isOpen={isSidebarOpen}
                onClick={() => setActiveTab('HISTORY')}
            />
        </nav>

        {/* User Profile / Logout */}
        <div className="p-4 border-t border-slate-800">
            <button 
                onClick={handleLogout}
                className={`flex items-center gap-3 w-full p-2 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition-colors ${!isSidebarOpen && 'justify-center'}`}
            >
                <LogOut size={20} />
                {isSidebarOpen && <span className="font-medium text-sm">Đăng xuất</span>}
            </button>
        </div>
      </aside>

      {/* --- MAIN CONTENT WRAPPER --- */}
      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        {/* TOP HEADER */}
        <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-10">
            <div className="flex items-center gap-4">
                <button onClick={() => setSidebarOpen(!isSidebarOpen)} className="p-2 hover:bg-slate-100 rounded-lg text-slate-500">
                    <Menu size={20} />
                </button>
                <h2 className="text-lg font-bold text-slate-700">
                    {activeTab === 'DASHBOARD' && 'Bảng điều khiển'}
                    {activeTab === 'POS' && 'Tạo giao dịch mới'}
                    {activeTab === 'HISTORY' && 'Lịch sử giao dịch'}
                </h2>
            </div>
            <div className="flex items-center gap-4">
                <div className="relative">
                    <Bell size={20} className="text-slate-400 hover:text-slate-600 cursor-pointer" />
                    <span className="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </div>
                <div className="flex items-center gap-2 pl-4 border-l border-slate-200">
                    <div className="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs">
                        AD
                    </div>
                    <div className="hidden md:block">
                        <p className="text-sm font-medium text-slate-700">Administrator</p>
                        <p className="text-[10px] text-slate-400 uppercase">System Admin</p>
                    </div>
                </div>
            </div>
        </header>

        {/* MAIN SCROLL AREA */}
        <main className="flex-1 overflow-y-auto p-6 bg-slate-50/50">
            <div className="max-w-7xl mx-auto h-full">
                {activeTab === 'DASHBOARD' && <AdminDashboard onViewAll={() => setActiveTab('HISTORY')} onNewOrder={() => setActiveTab('POS')} />}
                {activeTab === 'POS' && <CreatePaymentSection />}
                {activeTab === 'HISTORY' && <TransactionHistorySection />}
            </div>
        </main>
      </div>
    </div>
  );
};

// --- SUB-COMPONENTS ---

const NavItem = ({ icon, label, active, isOpen, onClick }: any) => (
    <button
        onClick={onClick}
        className={`flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200
        ${active 
            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/20' 
            : 'text-slate-400 hover:bg-slate-800 hover:text-white'
        }
        ${!isOpen && 'justify-center'}
        `}
    >
        {icon}
        {isOpen && <span className="font-medium text-sm">{label}</span>}
    </button>
);

// --- CREATE PAYMENT (POS) SECTION ---
const CreatePaymentSection: React.FC = () => {
  const [amountDisplay, setAmountDisplay] = useState('');
  const [note, setNote] = useState('');
  const [loading, setLoading] = useState(false);
  const [currentOrder, setCurrentOrder] = useState<PaymentTransaction | null>(null);
  const pollIntervalRef = useRef<number | null>(null);

  // Polling logic
  useEffect(() => {
    if (currentOrder && currentOrder.status === PaymentStatus.PENDING) {
        pollIntervalRef.current = window.setInterval(async () => {
          const isPaid = await checkSePayPayment(currentOrder.paymentCode, currentOrder.amount);
          if (isPaid) {
            updateTransactionStatus(currentOrder.id, PaymentStatus.PAID);
            const updated = getTransactionById(currentOrder.id);
            setCurrentOrder(updated || null);
            if (pollIntervalRef.current) clearInterval(pollIntervalRef.current);
          }
        }, 3000);
    }
    return () => { if (pollIntervalRef.current) clearInterval(pollIntervalRef.current); };
  }, [currentOrder?.id, currentOrder?.status]);

  const handleAmountChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value.replace(/\D/g, '');
    setAmountDisplay(raw ? new Intl.NumberFormat('vi-VN').format(parseInt(raw)) : '');
  };

  const handleCreate = () => {
    const val = parseInt(amountDisplay.replace(/\./g, ''));
    if (!val) return;
    setLoading(true);
    setTimeout(() => {
        const code = `DH${Math.floor(100000 + Math.random() * 900000)}`;
        const tx: PaymentTransaction = {
            id: crypto.randomUUID(),
            paymentCode: code,
            amount: val,
            description: note || code,
            customerName: 'Khách vãng lai',
            status: PaymentStatus.PENDING,
            createdAt: Date.now()
        };
        saveTransaction(tx);
        setCurrentOrder(tx);
        setLoading(false);
    }, 500);
  };

  const reset = () => {
    setAmountDisplay('');
    setNote('');
    setCurrentOrder(null);
  };

  const copyLink = () => {
      if(currentOrder) navigator.clipboard.writeText(`${window.location.origin}/#/pay/${currentOrder.id}`);
  };

  return (
    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full">
        {/* Left: Form */}
        <div className="lg:col-span-5 flex flex-col gap-6">
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 className="text-lg font-bold text-slate-800 mb-6">Thông tin thanh toán</h3>
                
                <div className="space-y-6">
                    <div>
                        <label className="block text-sm font-semibold text-slate-500 mb-2">Số tiền cần thu</label>
                        <div className="relative">
                            <input 
                                type="text" 
                                className="w-full text-3xl font-bold text-emerald-600 border-b-2 border-slate-200 focus:border-emerald-500 outline-none py-2 bg-transparent placeholder-slate-200"
                                placeholder="0"
                                value={amountDisplay}
                                onChange={handleAmountChange}
                            />
                            <span className="absolute right-0 bottom-3 text-slate-400 font-medium">VNĐ</span>
                        </div>
                        {/* Quick Select */}
                        <div className="flex flex-wrap gap-2 mt-4">
                            {[50000, 100000, 200000, 500000].map(v => (
                                <button 
                                    key={v} 
                                    onClick={() => setAmountDisplay(new Intl.NumberFormat('vi-VN').format(v))}
                                    className="px-3 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg text-xs font-medium transition-colors"
                                >
                                    {v/1000}k
                                </button>
                            ))}
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-slate-500 mb-2">Ghi chú đơn hàng</label>
                        <textarea 
                            className="w-full p-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-100 outline-none text-sm resize-none"
                            rows={3}
                            placeholder="Nhập ghi chú hoặc tên khách hàng..."
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                        />
                    </div>

                    <button 
                        disabled={!amountDisplay || loading}
                        onClick={handleCreate}
                        className={`w-full py-3.5 rounded-xl font-bold text-white transition-all shadow-lg shadow-emerald-200
                        ${amountDisplay ? 'bg-emerald-600 hover:bg-emerald-700 transform active:scale-[0.98]' : 'bg-slate-300 cursor-not-allowed'}
                        `}
                    >
                        {loading ? 'Đang tạo...' : 'Tạo Mã QR'}
                    </button>
                </div>
            </div>
            
            <div className="bg-emerald-50 p-6 rounded-2xl border border-emerald-100 text-emerald-800 text-sm">
                <p className="font-bold flex items-center gap-2 mb-2"><Leaf size={16}/> Mẹo quản lý</p>
                <p className="opacity-80">Mã QR sẽ được tạo tự động dựa trên số tiền. Hệ thống sẽ tự động kiểm tra trạng thái thanh toán mỗi 3 giây.</p>
            </div>
        </div>

        {/* Right: Result / QR */}
        <div className="lg:col-span-7">
            <div className="bg-white rounded-2xl shadow-sm border border-slate-200 h-full flex flex-col">
                <div className="p-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 className="font-bold text-slate-700">Vé thanh toán</h3>
                    {currentOrder && (
                        <span className={`px-3 py-1 rounded-full text-xs font-bold ${currentOrder.status === 'PAID' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}`}>
                            {currentOrder.status === 'PAID' ? 'ĐÃ THANH TOÁN' : 'CHỜ THANH TOÁN'}
                        </span>
                    )}
                </div>

                <div className="flex-1 flex flex-col items-center justify-center p-8 bg-slate-50/50">
                    {!currentOrder ? (
                        <div className="text-center text-slate-400">
                            <div className="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Search size={32} className="opacity-50"/>
                            </div>
                            <p>Chưa có đơn hàng nào được tạo</p>
                        </div>
                    ) : (
                        <div className="w-full max-w-md bg-white p-6 rounded-2xl shadow-xl border border-slate-100 animate-fade-in">
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <p className="text-slate-500 text-xs uppercase font-bold">Tổng thanh toán</p>
                                    <h2 className="text-3xl font-extrabold text-slate-800">
                                        {new Intl.NumberFormat('vi-VN').format(currentOrder.amount)} <span className="text-lg text-slate-400">đ</span>
                                    </h2>
                                </div>
                                <div className="text-right">
                                    <p className="text-slate-500 text-xs uppercase font-bold">Mã GD</p>
                                    <p className="font-mono font-bold text-emerald-600">{currentOrder.paymentCode}</p>
                                </div>
                            </div>

                            <div className="flex justify-center mb-6">
                                {currentOrder.status === 'PAID' ? (
                                    <div className="w-48 h-48 bg-green-50 rounded-xl flex flex-col items-center justify-center border border-green-100 text-green-600">
                                        <CheckCircle size={48} className="mb-2"/>
                                        <span className="font-bold">Thành Công</span>
                                    </div>
                                ) : (
                                    <img 
                                        src={getSePayQRUrl(currentOrder.amount, currentOrder.paymentCode)} 
                                        alt="QR"
                                        className="w-48 h-48 object-contain mix-blend-multiply"
                                    />
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-3 mb-4">
                                <button onClick={copyLink} className="flex items-center justify-center gap-2 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-medium text-slate-700 transition-colors">
                                    <Copy size={16}/> Copy Link
                                </button>
                                <button onClick={() => window.open(`/#/pay/${currentOrder.id}`, '_blank')} className="flex items-center justify-center gap-2 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium transition-colors">
                                    <ExternalLink size={16}/> Mở Tab Mới
                                </button>
                            </div>

                            {currentOrder.status === 'PENDING' && (
                                <div className="flex items-center justify-center gap-2 text-xs text-slate-400 animate-pulse">
                                    <RefreshCw size={12} className="animate-spin"/> Đang chờ khách quét mã...
                                </div>
                            )}

                            <div className="mt-4 pt-4 border-t border-slate-100">
                                <button onClick={reset} className="w-full text-slate-400 hover:text-slate-600 text-sm font-medium">
                                    Tạo đơn hàng mới
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    </div>
  );
};

// --- HISTORY SECTION ---
const TransactionHistorySection: React.FC = () => {
    const [transactions, setTransactions] = useState<PaymentTransaction[]>([]);
    const [filter, setFilter] = useState('ALL');

    useEffect(() => {
        setTransactions(getTransactions());
        const interval = setInterval(() => setTransactions(getTransactions()), 5000);
        return () => clearInterval(interval);
    }, []);

    const filtered = transactions.filter(t => {
        if (filter === 'ALL') return true;
        return t.status === filter;
    });

    return (
        <div className="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div className="p-5 border-b border-slate-100 flex justify-between items-center">
                <div className="flex gap-2">
                    <button 
                        onClick={() => setFilter('ALL')}
                        className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${filter === 'ALL' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}
                    >
                        Tất cả
                    </button>
                    <button 
                         onClick={() => setFilter('PAID')}
                        className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${filter === 'PAID' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100'}`}
                    >
                        Thành công
                    </button>
                    <button 
                         onClick={() => setFilter('PENDING')}
                        className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${filter === 'PENDING' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'}`}
                    >
                        Đang chờ
                    </button>
                </div>
                <div className="relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={16}/>
                    <input type="text" placeholder="Tìm mã giao dịch..." className="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-emerald-500 w-64"/>
                </div>
            </div>

            <div className="flex-1 overflow-auto">
                <table className="w-full text-left border-collapse">
                    <thead className="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th className="p-4 text-xs font-bold text-slate-500 uppercase">Thời gian</th>
                            <th className="p-4 text-xs font-bold text-slate-500 uppercase">Mã GD</th>
                            <th className="p-4 text-xs font-bold text-slate-500 uppercase">Khách hàng</th>
                            <th className="p-4 text-xs font-bold text-slate-500 uppercase text-right">Số tiền</th>
                            <th className="p-4 text-xs font-bold text-slate-500 uppercase text-center">Trạng thái</th>
                            <th className="p-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {filtered.length === 0 ? (
                            <tr>
                                <td colSpan={6} className="p-8 text-center text-slate-400">Không tìm thấy dữ liệu</td>
                            </tr>
                        ) : (
                            filtered.map(t => (
                                <tr key={t.id} className="hover:bg-slate-50 transition-colors">
                                    <td className="p-4 text-sm text-slate-600">
                                        {new Date(t.createdAt).toLocaleString('vi-VN')}
                                    </td>
                                    <td className="p-4 text-sm font-mono font-medium text-slate-800">{t.paymentCode}</td>
                                    <td className="p-4 text-sm text-slate-600">{t.customerName}</td>
                                    <td className="p-4 text-sm font-bold text-slate-800 text-right">
                                        {new Intl.NumberFormat('vi-VN').format(t.amount)}đ
                                    </td>
                                    <td className="p-4 text-center">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                            ${t.status === 'PAID' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}
                                        `}>
                                            {t.status === 'PAID' ? 'Thành công' : 'Chờ xử lý'}
                                        </span>
                                    </td>
                                    <td className="p-4 text-right">
                                        <button 
                                            onClick={() => window.open(`/#/pay/${t.id}`, '_blank')}
                                            className="text-emerald-600 hover:text-emerald-800 text-sm font-medium"
                                        >
                                            Chi tiết
                                        </button>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
