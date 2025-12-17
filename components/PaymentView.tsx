import React, { useEffect, useState, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getTransactionById, updateTransactionStatus } from '../services/mockDb';
import { checkSePayPayment, getSePayQRUrl } from '../services/sepayService';
import { PaymentStatus } from '../types';
import { CheckCircle, ShieldCheck, Leaf, Lock, AlertCircle, Home } from 'lucide-react';

export const PaymentView: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [transaction, setTransaction] = useState(getTransactionById(id || ''));
  const [isChecking, setIsChecking] = useState(false);
  const timerRef = useRef<number | null>(null);

  useEffect(() => {
    if (!id) {
        navigate('/'); // Invalid link redirect to home
        return;
    }
    
    // Initial load
    const tx = getTransactionById(id);
    if (!tx) {
        navigate('/'); // Not found redirect to home
        return;
    }
    setTransaction(tx);

    const checkStatus = async () => {
      // Fetch latest status
      const currentTx = getTransactionById(id);
      if (!currentTx || currentTx.status === PaymentStatus.PAID) {
          setTransaction(currentTx);
          return;
      }

      setIsChecking(true);
      const isPaid = await checkSePayPayment(currentTx.paymentCode, currentTx.amount);
      setIsChecking(false);
      
      if (isPaid) {
        updateTransactionStatus(id, PaymentStatus.PAID);
        setTransaction(getTransactionById(id));
      }
    };

    checkStatus();
    timerRef.current = window.setInterval(checkStatus, 3000);
    return () => { if (timerRef.current) clearInterval(timerRef.current); };
  }, [id, navigate]);

  if (!transaction) return null;

  // LOGIC: Đóng giao dịch nếu đã thanh toán thành công (User View)
  // Chỉ hiển thị thông báo đóng, không hiện QR nữa.
  if (transaction.status === PaymentStatus.PAID) {
      return (
        <div className="min-h-screen bg-slate-100 flex items-center justify-center p-4">
            <div className="bg-white w-full max-w-sm rounded-[2rem] shadow-xl p-8 text-center animate-fade-in">
                <div className="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 mx-auto mb-6">
                    <Lock size={32} />
                </div>
                <h2 className="text-xl font-bold text-slate-800 mb-2">Giao dịch đã đóng</h2>
                <p className="text-slate-500 mb-6">Đơn hàng này đã được thanh toán thành công và link truy cập đã hết hiệu lực bảo mật.</p>
                <button 
                    onClick={() => navigate('/')}
                    className="flex items-center justify-center gap-2 w-full py-3 bg-slate-100 text-slate-600 font-medium rounded-xl hover:bg-slate-200 transition-colors"
                >
                    <Home size={18} /> Về trang chủ
                </button>
            </div>
        </div>
      );
  }

  const qrUrl = getSePayQRUrl(transaction.amount, transaction.paymentCode);

  return (
    <div className="min-h-screen bg-slate-100 flex items-center justify-center p-4">
      {/* Background with Theme Image if available */}
      {transaction.themeImage && (
        <div className="absolute inset-0 z-0 opacity-10 bg-cover bg-center filter blur-sm" style={{ backgroundImage: `url(${transaction.themeImage})` }} />
      )}

      <div className="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl shadow-slate-300/50 overflow-hidden relative z-10 border border-slate-100">
         {/* Top Decor */}
         <div className="h-2 bg-gradient-to-r from-emerald-500 to-teal-500"></div>

         <div className="p-6 relative">
            <div className="text-center mt-4">
                <div className="inline-flex items-center justify-center w-12 h-12 bg-emerald-50 rounded-full text-emerald-600 mb-3">
                    <Leaf size={20} fill="currentColor" fillOpacity={0.2}/>
                </div>
                <h2 className="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Cổng Thanh Toán</h2>
                <h1 className="text-3xl font-black text-slate-800 tracking-tight">
                    {new Intl.NumberFormat('vi-VN').format(transaction.amount)}<span className="text-xl text-slate-400">đ</span>
                </h1>
            </div>
         </div>

         {/* Ticket Cutout Effect */}
         <div className="relative">
             <div className="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-100 rounded-full"></div>
             <div className="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-100 rounded-full"></div>
             <div className="mx-6 border-b-2 border-dashed border-slate-100"></div>
         </div>

         <div className="p-8 flex flex-col items-center">
            <div className="relative group">
                <img src={qrUrl} alt="QR" className="w-56 h-56 rounded-xl mix-blend-multiply border border-slate-100 p-1" />
                <div className="absolute bottom-[-20px] left-1/2 -translate-x-1/2 whitespace-nowrap bg-emerald-100 text-emerald-800 text-[10px] font-bold px-3 py-1 rounded-full border border-emerald-200">
                    {transaction.paymentCode}
                </div>
            </div>

            <div className="mt-8 w-full space-y-3">
                <div className="flex justify-between text-sm">
                    <span className="text-slate-400">Nội dung</span>
                    <span className="font-bold text-slate-700">{transaction.paymentCode}</span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-slate-400">Khách hàng</span>
                    <span className="font-bold text-slate-700">{transaction.customerName}</span>
                </div>
                 <div className="flex justify-between text-sm">
                    <span className="text-slate-400">Trạng thái</span>
                    <span className="font-bold text-amber-500 flex items-center gap-1">
                        <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Chờ thanh toán
                    </span>
                </div>
            </div>
            
            <div className="mt-6 bg-amber-50 p-3 rounded-xl flex items-start gap-2 text-xs text-amber-700 w-full">
                <AlertCircle size={16} className="shrink-0 mt-0.5" />
                <span>Vui lòng không tắt trình duyệt. Màn hình sẽ tự động đóng khi thanh toán hoàn tất.</span>
            </div>
         </div>

         <div className="bg-slate-50 p-4 text-center border-t border-slate-100">
            <div className="flex items-center justify-center gap-2 text-[10px] text-slate-400 font-medium">
                <ShieldCheck size={12} /> Bảo mật bởi PayGen & SePay
            </div>
         </div>
      </div>
    </div>
  );
};