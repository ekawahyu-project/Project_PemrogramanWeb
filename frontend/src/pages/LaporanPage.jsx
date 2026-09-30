import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Plus, BarChart2, FileText, Trash2, Edit2, Printer } from 'lucide-react';
import { apiRequest } from '../api/client';
import Modal from '../components/Modal';

export default function LaporanPage({ showToast }) {
  const [reportData, setReportData] = useState(null);
  const [loading, setLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [form, setForm] = useState({
    id: '',
    judul: '',
    tanggal_dari: '',
    tanggal_sampai: '',
    catatan: ''
  });

  const loadLaporan = async () => {
    try {
      const res = await apiRequest('laporan_data', {}, 'GET');
      setReportData(res);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadLaporan();
  }, []);

  const openAddModal = () => {
    setEditingItem(null);
    const today = new Date().toISOString().split('T')[0];
    const firstDayMonth = today.substring(0, 8) + '01';
    setForm({
      id: '',
      judul: `Laporan Keuangan Periode Ini`,
      tanggal_dari: firstDayMonth,
      tanggal_sampai: today,
      catatan: ''
    });
    setIsModalOpen(true);
  };

  const openEditModal = (item) => {
    setEditingItem(item);
    setForm({
      id: item.id,
      judul: item.judul,
      tanggal_dari: item.tanggal_dari,
      tanggal_sampai: item.tanggal_sampai,
      catatan: item.catatan || ''
    });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    if (!form.judul.trim()) {
      showToast('Judul laporan wajib diisi.', 'error');
      return;
    }

    try {
      await apiRequest('laporan_save', form);
      showToast(editingItem ? 'Laporan berhasil diperbarui.' : 'Snapshot laporan berhasil disimpan!', 'success');
      setIsModalOpen(false);
      loadLaporan();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Hapus laporan tersimpan ini?')) return;
    try {
      await apiRequest('laporan_delete', { id });
      showToast('Laporan berhasil dihapus.', 'success');
      loadLaporan();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handlePrint = () => {
    window.print();
  };

  const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

  const { monthly = [], saved_reports = [], year = new Date().getFullYear() } = reportData || {};

  const maxVal = Math.max(
    ...monthly.map(m => Math.max(m.pemasukan, m.pengeluaran)),
    1000000
  );

  return (
    <div className="space-y-6">
      {/* Monthly Chart Card */}
      <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm print:border-none print:shadow-none">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
          <div>
            <h4 className="text-sm font-bold text-slate-800 flex items-center gap-2">
              <BarChart2 className="w-4 h-4 text-blue-600" />
              <span>Grafik Arus Kas Bulanan ({year})</span>
            </h4>
            <p className="text-xs text-slate-400">Perbandingan pemasukan vs pengeluaran per bulan</p>
          </div>

          <div className="flex items-center gap-4 text-xs font-semibold">
            <div className="flex items-center gap-1.5">
              <span className="w-3 h-3 rounded-md bg-emerald-500 inline-block" />
              <span className="text-slate-600">Pemasukan</span>
            </div>
            <div className="flex items-center gap-1.5">
              <span className="w-3 h-3 rounded-md bg-rose-500 inline-block" />
              <span className="text-slate-600">Pengeluaran</span>
            </div>
          </div>
        </div>

        {/* Visual Bar Chart */}
        <div className="h-64 flex items-end justify-between gap-2 pt-6 pb-2 border-b border-slate-100 overflow-x-auto min-w-[500px]">
          {monthly.map((m) => {
            const hPem = (m.pemasukan / maxVal) * 100;
            const hPeng = (m.pengeluaran / maxVal) * 100;

            return (
              <div key={m.bulan} className="flex-1 flex flex-col items-center h-full justify-end group">
                <div className="w-full flex items-end justify-center gap-1 h-48 relative">
                  <div className="absolute -top-10 opacity-0 group-hover:opacity-100 bg-slate-900 text-white text-[10px] py-1 px-2 rounded-lg pointer-events-none transition whitespace-nowrap z-20 shadow-md">
                    +{formatRupiah(m.pemasukan)} / -{formatRupiah(m.pengeluaran)}
                  </div>

                  <motion.div
                    initial={{ height: 0 }}
                    animate={{ height: `${Math.max(hPem, 2)}%` }}
                    transition={{ duration: 0.6, ease: 'easeOut' }}
                    className="w-3 sm:w-4 bg-emerald-500 rounded-t-md hover:bg-emerald-600 transition"
                  />
                  <motion.div
                    initial={{ height: 0 }}
                    animate={{ height: `${Math.max(hPeng, 2)}%` }}
                    transition={{ duration: 0.6, ease: 'easeOut', delay: 0.1 }}
                    className="w-3 sm:w-4 bg-rose-500 rounded-t-md hover:bg-rose-600 transition"
                  />
                </div>
                <span className="text-[11px] font-semibold text-slate-500 mt-2">{m.bulan}</span>
              </div>
            );
          })}
        </div>
      </div>

      {/* Saved Reports Section */}
      <div className="space-y-4">
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
          <div>
            <h4 className="text-sm font-bold text-slate-800">Arsip Laporan Periode</h4>
            <p className="text-xs text-slate-400">Snapshot kalkulasi keuangan yang disimpan</p>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={handlePrint}
              className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition"
              title="Cetak Laporan / Simpan PDF"
            >
              <Printer className="w-4 h-4" />
              <span>Cetak / PDF</span>
            </button>
            <button
              onClick={openAddModal}
              className="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-md shadow-blue-500/20 transition"
            >
              <Plus className="w-4 h-4" />
              <span>Simpan Laporan Baru</span>
            </button>
          </div>
        </div>

        <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          {saved_reports.length === 0 ? (
            <div className="py-12 text-center text-xs text-slate-400">
              Belum ada arsip snapshot laporan yang disimpan.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                  <tr>
                    <th className="py-3.5 px-4">Judul Laporan</th>
                    <th className="py-3.5 px-4">Periode</th>
                    <th className="py-3.5 px-4 text-right">Pemasukan</th>
                    <th className="py-3.5 px-4 text-right">Pengeluaran</th>
                    <th className="py-3.5 px-4 text-right">Laba Bersih</th>
                    <th className="py-3.5 px-4">Catatan</th>
                    <th className="py-3.5 px-4 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-700">
                  {saved_reports.map((item, idx) => (
                    <motion.tr
                      key={item.id}
                      initial={{ opacity: 0, y: 4 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: idx * 0.02 }}
                      className="hover:bg-slate-50/70 transition"
                    >
                      <td className="py-3 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <FileText className="w-4 h-4 text-blue-500 flex-shrink-0" />
                        <span>{item.judul}</span>
                      </td>
                      <td className="py-3 px-4 text-slate-500 whitespace-nowrap">
                        {item.tanggal_dari} &rarr; {item.tanggal_sampai}
                      </td>
                      <td className="py-3 px-4 text-right font-semibold text-emerald-600 whitespace-nowrap">
                        {formatRupiah(item.pemasukan)}
                      </td>
                      <td className="py-3 px-4 text-right font-semibold text-rose-600 whitespace-nowrap">
                        {formatRupiah(item.pengeluaran)}
                      </td>
                      <td className={`py-3 px-4 text-right font-extrabold whitespace-nowrap ${
                        item.laba >= 0 ? 'text-slate-800' : 'text-rose-600'
                      }`}>
                        {formatRupiah(item.laba)}
                      </td>
                      <td className="py-3 px-4 text-slate-500 max-w-xs truncate">
                        {item.catatan || '-'}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <div className="flex items-center justify-center gap-1">
                          <button
                            onClick={() => openEditModal(item)}
                            className="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                            title="Edit Judul & Catatan"
                          >
                            <Edit2 className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => handleDelete(item.id)}
                            className="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                            title="Hapus"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </td>
                    </motion.tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Modal Add / Edit Laporan */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingItem ? 'Edit Informasi Laporan' : 'Simpan Snapshot Laporan'}
      >
        <form onSubmit={handleSave} className="space-y-3.5 text-xs">
          <div>
            <label className="block font-semibold text-slate-700 mb-1">Judul Laporan</label>
            <input
              type="text"
              value={form.judul}
              onChange={(e) => setForm(f => ({ ...f, judul: e.target.value }))}
              placeholder="Contoh: Rekap Kuartal 3"
              required
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
              <input
                type="date"
                value={form.tanggal_dari}
                onChange={(e) => setForm(f => ({ ...f, tanggal_dari: e.target.value }))}
                disabled={!!editingItem}
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none disabled:bg-slate-50 disabled:text-slate-400"
              />
            </div>
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
              <input
                type="date"
                value={form.tanggal_sampai}
                onChange={(e) => setForm(f => ({ ...f, tanggal_sampai: e.target.value }))}
                disabled={!!editingItem}
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none disabled:bg-slate-50 disabled:text-slate-400"
              />
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Catatan Evaluasi Usaha</label>
            <textarea
              rows="3"
              value={form.catatan}
              onChange={(e) => setForm(f => ({ ...f, catatan: e.target.value }))}
              placeholder="Tuliskan catatan analisis performa usaha..."
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none resize-none"
            />
          </div>

          <div className="pt-3 border-t border-slate-100 flex gap-2">
            <button
              type="button"
              onClick={() => setIsModalOpen(false)}
              className="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              className="flex-1 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-md shadow-blue-500/20 transition"
            >
              {editingItem ? 'Perbarui Laporan' : 'Simpan Arsip'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
