import React from 'react';
import { useAuth } from '../context/AuthContext';

export default function Navbar({ title, subtitle, onOpenMobile }) {
  const { user } = useAuth();

  return (
    <>
      {/* Mobile Topbar Navbar (Muncul hanya pada layar < lg) */}
      <header className="lg:hidden sticky top-0 z-30 flex items-center justify-between px-4 py-3 bg-navy-950 text-white shadow-md">
        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={onOpenMobile}
            aria-label="Buka Menu"
            className="p-2 -ml-1 text-white hover:bg-white/10 rounded-lg focus:outline-none transition cursor-pointer"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <span className="font-bold text-base tracking-tight text-white">AlpetBizz</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="text-xs font-semibold px-2.5 py-1 bg-white/10 text-white rounded-md border border-white/10 truncate max-w-[120px]">
            {user?.username || 'admin'}
          </span>
        </div>
      </header>

      {/* Main Page Title Header */}
      <header className="bg-white border-b border-gray-100 px-4 sm:px-6 py-4">
        <h2 className="font-bold text-lg sm:text-xl text-navy-900">{title}</h2>
        <p className="text-xs sm:text-sm text-gray-500 mt-0.5">{subtitle}</p>
      </header>
    </>
  );
}
