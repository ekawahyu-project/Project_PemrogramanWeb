import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { apiRequest } from '../api/client';

export default function DashboardPage({ onNavigate, showToast }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchDashboard = async () => {
    try {
      setLoading(true);
      const res = await apiRequest('dashboard', {}, 'GET');
      if (res && res.success) {
        setData(res);
      }
    } catch (err) {
      if (showToast) showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  if (loading && !data) {
    return (
      <div className="flex items-center justify-center py-20 text-gray-400">
        <motion.div
          animate={{ rotate: 360 }}
          transition={{ repeat: Infinity, duration: 1, ease: 'linear' }}
          className="w-8 h-8 border-2 border-navy-700 border-t-transparent rounded-full"
        />
      </div>
    );
  }

  const stats = data?.stats || {};
  const pemasukan = Number(stats.pemasukan) || 0;
  const pengeluaran = Number(stats.pengeluaran) || 0;
  const laba = Number(stats.laba) || (pemasukan - pengeluaran);
  const totalProduk = Number(stats.total_produk) || 0;
  const lowStockCount = Number(stats.low_stock_count) || 0;
  const txMasukCount = stats.tx_masuk_count ?? 0;
  const txKeluarCount = stats.tx_keluar_count ?? 0;
  const recentTransactions = data?.recent_transactions || [];

  const formatRupiah = (num) => {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
  };

  const formatTanggal = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const day = String(d.getDate()).padStart(2, '0');
    const mon = bulan[d.getMonth()];
    const yr = d.getFullYear();
    return `${day} ${mon} ${yr}`;
  };

  const statItems = [
    {
      label: 'Total Pemasukan',
      value: formatRupiah(pemasukan),
      sub: `${txMasukCount} transaksi`,
      color: 'text-green-600',
    },
    {
      label: 'Total Pengeluaran',
      value: formatRupiah(pengeluaran),
      sub: `${txKeluarCount} transaksi`,
      color: 'text-red-500',
    },
    {
      label: 'Produk',
      value: `${totalProduk} item`,
      sub: lowStockCount > 0 ? `${lowStockCount} stok menipis` : 'Semua aman',
      color: lowStockCount > 0 ? 'text-orange-500' : 'text-blue-600',
    },
    {
      label: 'Laba Bersih',
      value: formatRupiah(Math.abs(laba)),
      sub: laba >= 0 ? 'Untung' : 'Rugi',
      color: laba >= 0 ? 'text-navy-800' : 'text-red-500',
    },
  ];

  const shortcuts = [
    { page: 'transaksi', label: 'Transaksi', desc: 'Catat pemasukan & pengeluaran' },
    { page: 'produk', label: 'Produk', desc: 'Kelola katalog data produk' },
    { page: 'stok', label: 'Stok', desc: 'Pantau & catat pergerakan barang' },
    { page: 'laporan', label: 'Laporan', desc: 'Grafik analitik & laporan bulanan' },
    { page: 'rekomendasi', label: 'Rekomendasi', desc: 'Analisis pintar & catatan bisnis' },
    { page: 'profil', label: 'Profil', desc: 'Informasi akun & profil usaha' },
  ];

  return (
    <div className="space-y-6">
      {/* Stok rendah alert */}
      {lowStockCount > 0 && (
        <motion.div
          initial={{ opacity: 0, y: -8 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3 }}
          className="bg-orange-50 border border-orange-200 rounded-xl p-3.5 sm:p-4 flex items-start sm:items-center gap-3"
        >
          <span className="text-orange-500 text-lg flex-shrink-0">⚠</span>
          <p className="text-xs sm:text-sm text-orange-800 font-medium leading-relaxed">
            {lowStockCount} produk stok menipis.{' '}
            <button
              onClick={() => onNavigate('stok')}
              className="underline font-semibold hover:text-orange-950 cursor-pointer"
            >
              Cek halaman Stok
            </button>
          </p>
        </motion.div>
      )}

      {/* Kartu Statistik */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {statItems.map((s, i) => (
          <motion.div
            key={s.label}
            initial={{ opacity: 0, y: 14 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.05, duration: 0.25 }}
            whileHover={{ y: -2, transition: { duration: 0.15 } }}
            className="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between"
          >
            <div>
              <p className="text-xs font-medium text-gray-500 mb-1.5">{s.label}</p>
              <p className={`text-lg sm:text-xl font-bold tracking-tight ${s.color}`}>
                {s.value}
              </p>
            </div>
            <p className="text-xs text-gray-400 mt-2.5 pt-2 border-t border-gray-50">{s.sub}</p>
          </motion.div>
        ))}
      </div>

      {/* Shortcut Navigasi */}
      <div>
        <h3 className="font-semibold text-navy-900 text-sm mb-3">Akses Cepat Modul</h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
          {shortcuts.map((s, i) => (
            <motion.button
              key={s.label}
              onClick={() => onNavigate(s.page)}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2 + i * 0.04, duration: 0.2 }}
              whileHover={{ y: -2, transition: { duration: 0.15 } }}
              className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:border-navy-300 hover:shadow-md transition group flex flex-col justify-between text-left cursor-pointer w-full"
            >
              <div>
                <p className="font-semibold text-navy-900 text-sm group-hover:text-navy-700 flex items-center justify-between">
                  <span>{s.label}</span>
                  <span className="text-gray-300 group-hover:text-navy-600 transition-transform group-hover:translate-x-0.5">
                    →
                  </span>
                </p>
                <p className="text-xs text-gray-400 mt-1">{s.desc}</p>
              </div>
            </motion.button>
          ))}
        </div>
      </div>

      {/* 5 Transaksi Terakhir */}
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.35, duration: 0.3 }}
        className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
      >
        <div className="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 className="font-semibold text-navy-900 text-sm">Transaksi Terakhir</h3>
          <button
            onClick={() => onNavigate('transaksi')}
            className="text-xs font-semibold text-navy-700 hover:text-navy-950 transition cursor-pointer"
          >
            Lihat semua
          </button>
        </div>
        <div className="overflow-x-auto w-full">
          <table className="w-full text-xs sm:text-sm min-w-[500px]">
            <thead className="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
              <tr>
                <th className="px-4 sm:px-5 py-3 text-left">Tanggal</th>
                <th className="px-4 sm:px-5 py-3 text-left">Keterangan</th>
                <th className="px-4 sm:px-5 py-3 text-left">Jenis</th>
                <th className="px-4 sm:px-5 py-3 text-right">Jumlah</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {recentTransactions.length === 0 ? (
                <tr>
                  <td colSpan="4" className="text-center py-6 text-gray-400 text-xs">
                    Belum ada riwayat transaksi.
                  </td>
                </tr>
              ) : (
                recentTransactions.map((t, idx) => {
                  const isMasuk = t.jenis === 'Pemasukan';
                  return (
                    <motion.tr
                      key={t.id || idx}
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      transition={{ delay: 0.4 + idx * 0.04 }}
                      className="hover:bg-gray-50/70 transition"
                    >
                      <td className="px-4 sm:px-5 py-3.5 text-gray-500 whitespace-nowrap">
                        {formatTanggal(t.tanggal)}
                      </td>
                      <td className="px-4 sm:px-5 py-3.5 font-medium text-navy-900">
                        {t.keterangan}
                      </td>
                      <td className="px-4 sm:px-5 py-3.5">
                        <span
                          className={`px-2 py-0.5 rounded-md text-xs font-medium ${
                            isMasuk
                              ? 'bg-green-100 text-green-700'
                              : 'bg-red-100 text-red-600'
                          }`}
                        >
                          {t.jenis}
                        </span>
                      </td>
                      <td
                        className={`px-4 sm:px-5 py-3.5 text-right font-semibold whitespace-nowrap ${
                          isMasuk ? 'text-green-600' : 'text-red-500'
                        }`}
                      >
                        {(isMasuk ? '+' : '-') + formatRupiah(t.jumlah)}
                      </td>
                    </motion.tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </motion.div>
    </div>
  );
}
