import React from 'react';
import { HashRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AdminPortal } from './components/AdminPortal';
import { LoginPage } from './components/LoginPage';
import { HomePage } from './components/HomePage';
import { PaymentView } from './components/PaymentView';

// Auth Guard Component
const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
  const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
};

const App: React.FC = () => {
  return (
    <HashRouter>
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        
        {/* Public Payment Link (Dynamic ID) */}
        <Route path="/pay/:id" element={<PaymentView />} />

        {/* Protected Admin Routes */}
        <Route 
          path="/admin" 
          element={
            <ProtectedRoute>
              <AdminPortal />
            </ProtectedRoute>
          } 
        />

        {/* Catch all - Redirect to Home */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </HashRouter>
  );
};

export default App;