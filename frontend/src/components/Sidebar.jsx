import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

export const navItems = [
  { id: 'dashboard', label: 'Dashboard' },
  { id: 'transaksi', label: 'Transaksi' },
  { id: 'produk', label: 'Produk' },
  { id: 'stok', label: 'Stok' },
  { id: 'laporan', label: 'Laporan' },
  { id: 'rekomendasi', label: 'Rekomendasi' },
  { id: 'profil', label: 'Profil' },
];

export default function Sidebar({ currentPage, onSelectPage, mobileOpen, onCloseMobile }) {
  const { logout } = useAuth();

  return (
    <>
      {/* Mobile Backdrop */}
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onCloseMobile}
            className="fixed inset-0 bg-navy-950/60 backdrop-blur-sm z-40 lg:hidden"
          />
        )}
      </AnimatePresence>

      {/* Sidebar container */}
      <aside
        id="app-sidebar"
        className={`fixed left-0 top-0 w-64 h-screen bg-navy-950 flex flex-col z-50 transition-transform duration-300 ease-in-out lg:translate-x-0 ${
          mobileOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:shadow-none'
        }`}
      >
        {/* Brand Header */}
        <div className="px-5 py-4 border-b border-white/10 flex items-center justify-between">
          <span className="text-white font-bold text-lg tracking-tight">AlpetBizz</span>
          <button
            type="button"
            onClick={onCloseMobile}
            aria-label="Tutup Menu"
            className="lg:hidden p-1.5 text-white/70 hover:text-white hover:bg-white/10 rounded-lg focus:outline-none transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Navigation Items */}
        <nav className="flex-1 px-3 py-3 space-y-1 overflow-y-auto">
          {navItems.map((item) => {
            const isActive = currentPage === item.id;
            return (
              <button
                key={item.id}
                onClick={() => {
                  onSelectPage(item.id);
                  onCloseMobile();
                }}
                className={`relative w-full text-left flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition cursor-pointer ${
                  isActive
                    ? 'text-white'
                    : 'text-white/65 hover:bg-white/10 hover:text-white'
                }`}
              >
                {isActive && (
                  <motion.div
                    layoutId="activeTabIndicator"
                    className="absolute inset-0 bg-white/15 rounded-lg"
                    transition={{ type: 'spring', stiffness: 450, damping: 35 }}
                  />
                )}
                <span className="relative z-10">{item.label}</span>
              </button>
            );
          })}
        </nav>

        {/* Logout Button */}
        <div className="px-3 py-3 border-t border-white/10">
          <button
            onClick={logout}
            className="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium text-white/65 hover:bg-red-500/80 hover:text-white transition cursor-pointer"
          >
            Keluar
          </button>
        </div>
      </aside>
    </>
  );
}
