import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Leaf, Lock, ArrowRight } from 'lucide-react';

export const LoginPage: React.FC = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    if (username === 'admin' && password === '123456') {
      localStorage.setItem('isAuthenticated', 'true');
      navigate('/admin');
    } else {
      setError('Tên đăng nhập hoặc mật khẩu không đúng');
    }
  };

  return (
    <div className="min-h-screen bg-slate-100 flex items-center justify-center p-4">
      <div className="bg-white w-full max-w-sm rounded-3xl shadow-xl p-8 border border-slate-100">
        <div className="text-center mb-8">
            <div className="inline-flex items-center justify-center w-16 h-16 bg-emerald-50 rounded-2xl text-emerald-600 mb-4 shadow-sm">
                <Leaf size={32} />
            </div>
            <h1 className="text-2xl font-bold text-slate-800">Admin Portal</h1>
            <p className="text-slate-400 text-sm">Đăng nhập để quản lý thanh toán</p>
        </div>

        <form onSubmit={handleLogin} className="space-y-4">
            <div>
                <label className="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Tài khoản</label>
                <input 
                    type="text" 
                    className="w-full px-4 py-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-emerald-200 outline-none text-slate-700 font-medium"
                    placeholder="Nhập tên đăng nhập"
                    value={username}
                    onChange={(e) => setUsername(e.target.value)}
                />
            </div>
            <div>
                <label className="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Mật khẩu</label>
                <input 
                    type="password" 
                    className="w-full px-4 py-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-emerald-200 outline-none text-slate-700 font-medium"
                    placeholder="••••••"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                />
            </div>

            {error && <p className="text-red-500 text-xs text-center">{error}</p>}

            <button 
                type="submit"
                className="w-full py-4 bg-emerald-600 text-white rounded-xl font-bold text-lg hover:bg-emerald-700 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-200 mt-4"
            >
                Đăng Nhập <ArrowRight size={20} />
            </button>
        </form>
        
        <div className="mt-6 text-center">
            <a href="/" className="text-slate-400 text-xs hover:text-emerald-600">Quay về trang chủ</a>
        </div>
      </div>
    </div>
  );
};