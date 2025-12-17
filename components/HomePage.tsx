import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Leaf, ShieldCheck, LogIn } from 'lucide-react';

export const HomePage: React.FC = () => {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-emerald-50 flex items-center justify-center p-6">
      <div className="text-center max-w-md">
        <div className="inline-flex items-center justify-center w-24 h-24 bg-white rounded-[2rem] text-emerald-600 mb-8 shadow-xl shadow-emerald-100">
            <Leaf size={48} strokeWidth={1.5} fill="currentColor" fillOpacity={0.1} />
        </div>
        
        <h1 className="text-4xl font-black text-slate-800 mb-4 tracking-tight">PayGen <span className="text-emerald-600">Gateway</span></h1>
        <p className="text-lg text-slate-500 font-medium leading-relaxed mb-8">
            Hệ thống thanh toán nội bộ & quản lý giao dịch tập trung.
        </p>

        <div className="flex flex-col gap-4 items-center">
            <button 
                onClick={() => navigate('/login')}
                className="w-full sm:w-64 py-4 bg-white text-emerald-700 font-bold rounded-2xl shadow-lg shadow-slate-200 border border-slate-100 hover:bg-emerald-50 transition-all active:scale-95 flex items-center justify-center gap-2"
            >
                <LogIn size={20} /> Đăng nhập quản trị
            </button>
        </div>

        <div className="mt-12 flex items-center justify-center gap-2 text-slate-400 text-xs font-semibold uppercase tracking-wider">
            <ShieldCheck size={14} /> Hệ thống nội bộ
        </div>
      </div>
    </div>
  );
};