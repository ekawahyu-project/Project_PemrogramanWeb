import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { AuthProvider, useAuth } from './context/AuthContext';
import Sidebar, { navItems } from './components/Sidebar';
import Navbar from './components/Navbar';
import Toast from './components/Toast';

// Pages
import AuthPage from './pages/AuthPage';
import DashboardPage from './pages/DashboardPage';
import TransaksiPage from './pages/TransaksiPage';
import ProdukPage from './pages/ProdukPage';
import StokPage from './pages/StokPage';
import LaporanPage from './pages/LaporanPage';
import RekomendasiPage from './pages/RekomendasiPage';
import ProfilPage from './pages/ProfilPage';

function MainApp() {
  const { user, loading } = useAuth();
  const [currentPage, setCurrentPage] = useState('dashboard');
  const [mobileOpen, setMobileOpen] = useState(false);
  const [toast, setToast] = useState(null);

  const showToast = (message, type = 'info') => {
    setToast({ message, type });
    setTimeout(() => {
      setToast(null);
    }, 3500);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-900 flex flex-col items-center justify-center text-white">
        <motion.div
          animate={{ scale: [1, 1.15, 1], rotate: [0, 180, 360] }}
          transition={{ duration: 1.5, repeat: Infinity, ease: 'easeInOut' }}
          className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center font-extrabold text-xl shadow-xl shadow-blue-500/30 mb-4"
        >
          A
        </motion.div>
        <p className="text-xs font-semibold text-slate-400 tracking-wider uppercase animate-pulse">
          Memuat AlpetBizz...
        </p>
      </div>
    );
  }

  if (!user) {
    return (
      <>
        <AuthPage showToast={showToast} />
        <Toast toast={toast} onClose={() => setToast(null)} />
      </>
    );
  }

  const pageConfigs = {
    dashboard: {
      title: 'Dashboard',
      subtitle: <>Selamat datang, <span className="font-medium text-navy-800">{user?.username || 'admin'}</span></>
    },
    transaksi: {
      title: 'Manajemen Transaksi',
      subtitle: 'Catat dan kelola riwayat pemasukan & pengeluaran usaha.'
    },
    produk: {
      title: 'Manajemen Produk',
      subtitle: 'Kelola data katalog produk — tambah, update, atau hapus produk.'
    },
    stok: {
      title: 'Manajemen Stok',
      subtitle: 'Pantau stok saat ini dan catat mutasi keluar masuk barang.'
    },
    laporan: {
      title: 'Laporan & Grafik',
      subtitle: 'Grafik pemasukan/pengeluaran live + arsip laporan per periode.'
    },
    rekomendasi: {
      title: 'Sistem Rekomendasi',
      subtitle: 'Analisis pintar otomatis + catatan rencana tindak lanjut bisnis.'
    },
    profil: {
      title: 'Profil Saya',
      subtitle: 'Kelola informasi akun dan identitas usaha Anda.'
    },
  };

  const currentConfig = pageConfigs[currentPage] || pageConfigs.dashboard;

  const renderPage = () => {
    switch (currentPage) {
      case 'dashboard':
        return <DashboardPage onNavigate={setCurrentPage} showToast={showToast} />;
      case 'transaksi':
        return <TransaksiPage showToast={showToast} />;
      case 'produk':
        return <ProdukPage showToast={showToast} />;
      case 'stok':
        return <StokPage showToast={showToast} />;
      case 'laporan':
        return <LaporanPage showToast={showToast} />;
      case 'rekomendasi':
        return <RekomendasiPage showToast={showToast} />;
      case 'profil':
        return <ProfilPage showToast={showToast} />;
      default:
        return <DashboardPage onNavigate={setCurrentPage} showToast={showToast} />;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 flex">
      {/* Sidebar Navigation */}
      <Sidebar
        currentPage={currentPage}
        onSelectPage={setCurrentPage}
        mobileOpen={mobileOpen}
        onCloseMobile={() => setMobileOpen(false)}
      />

      {/* Main Content Area */}
      <main className="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <Navbar
          title={currentConfig.title}
          subtitle={currentConfig.subtitle}
          onOpenMobile={() => setMobileOpen(true)}
        />

        <div className="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 max-w-7xl w-full mx-auto">
          <AnimatePresence mode="wait">
            <motion.div
              key={currentPage}
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              transition={{ duration: 0.18, ease: 'easeOut' }}
            >
              {renderPage()}
            </motion.div>
          </AnimatePresence>
        </div>
      </main>

      {/* Global Toast */}
      <Toast toast={toast} onClose={() => setToast(null)} />
    </div>
  );
}

export default function App() {
  return (
    <AuthProvider>
      <MainApp />
    </AuthProvider>
  );
}
