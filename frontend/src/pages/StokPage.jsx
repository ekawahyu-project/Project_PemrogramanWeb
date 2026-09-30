import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Plus, ArrowDownCircle, ArrowUpCircle, RotateCcw, Boxes } from 'lucide-react';
import { apiRequest } from '../api/client';
import Modal from '../components/Modal';

export default function StokPage({ showToast }) {
  const [logs, setLogs] = useState([]);
  const [produk, setProduk] = useState([]);
  const [loading, setLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState({
    produk_id: '',
    jenis: 'Masuk',
    jumlah: '1',
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: ''
  });

  const loadData = async () => {
    try {
      const res = await apiRequest('stok_list', {}, 'GET');
      setLogs(res.logs || []);
      setProduk(res.produk || []);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const openCatatModal = () => {
    setForm({
      produk_id: produk[0]?.id || '',
      jenis: 'Masuk',
      jumlah: '1',
      tanggal: new Date().toISOString().split('T')[0],
      keterangan: ''
    });
    setIsModalOpen(true);
  };

  const handleCatat = async (e) => {
    e.preventDefault();
    if (!form.produk_id) {
      showToast('Pilih salah satu produk.', 'error');
      return;
    }
    if (Number(form.jumlah) <= 0) {
      showToast('Jumlah mutasi harus lebih besar dari 0.', 'error');
      return;
    }

    try {
      await apiRequest('stok_catat', form);
      showToast('Mutasi stok berhasil dicatat!', 'success');
      setIsModalOpen(false);
      loadData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleReversal = async (id) => {
    if (!window.confirm('Batalkan (reversal) mutasi stok ini? Jumlah stok produk akan dikembalikan ke posisi semula.')) return;
    try {
      await apiRequest('stok_reversal', { id });
      showToast('Mutasi berhasil dibatalkan dan stok dikembalikan.', 'success');
      loadData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const totalMasuk = logs.filter(l => l.jenis === 'Masuk').reduce((acc, l) => acc + Number(l.jumlah), 0);
  const totalKeluar = logs.filter(l => l.jenis === 'Keluar').reduce((acc, l) => acc + Number(l.jumlah), 0);

  return (
    <div className="space-y-5">
      {/* Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-xs text-slate-500 font-semibold">Total Log Mutasi</p>
            <h3 className="text-xl font-extrabold text-slate-800 mt-1">{logs.length} Kali</h3>
          </div>
          <div className="p-2.5 rounded-xl bg-blue-50 text-blue-600">
            <Boxes className="w-5 h-5" />
          </div>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-xs text-slate-500 font-semibold">Total Barang Masuk</p>
            <h3 className="text-xl font-extrabold text-emerald-600 mt-1">+{totalMasuk} Unit</h3>
          </div>
          <div className="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
            <ArrowDownCircle className="w-5 h-5" />
          </div>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-xs text-slate-500 font-semibold">Total Barang Keluar</p>
            <h3 className="text-xl font-extrabold text-rose-600 mt-1">-{totalKeluar} Unit</h3>
          </div>
          <div className="p-2.5 rounded-xl bg-rose-50 text-rose-600">
            <ArrowUpCircle className="w-5 h-5" />
          </div>
        </div>
      </div>

      {/* Visual Monitor Stok Produk Saat Ini */}
      <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <h4 className="text-sm font-bold text-slate-800 mb-1">Status Ketersediaan Stok</h4>
        <p className="text-xs text-slate-400 mb-4">Level stok aktual terhadap batas minimum persediaan</p>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
          {produk.map((p) => {
            const isLow = Number(p.stok) <= Number(p.stok_min);
            const isOut = Number(p.stok) === 0;
            const target = Math.max(Number(p.stok_min) * 2, Number(p.stok), 10);
            const pct = Math.min(100, Math.round((Number(p.stok) / target) * 100));

            return (
              <div key={p.id} className="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60">
                <div className="flex items-center justify-between mb-1.5">
                  <span className="text-xs font-bold text-slate-800 truncate">{p.nama}</span>
                  <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                    isOut
                      ? 'bg-rose-100 text-rose-700'
                      : isLow
                      ? 'bg-amber-100 text-amber-700'
                      : 'bg-emerald-100 text-emerald-700'
                  }`}>
                    {isOut ? 'Habis' : isLow ? 'Menipis' : 'Aman'}
                  </span>
                </div>
                <div className="flex justify-between text-[11px] text-slate-500 mb-1.5 font-medium">
                  <span>Stok: <b className="text-slate-700">{p.stok}</b> {p.satuan}</span>
                  <span>Min: {p.stok_min} {p.satuan}</span>
                </div>
                <div className="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                  <motion.div
                    initial={{ width: 0 }}
                    animate={{ width: `${pct}%` }}
                    transition={{ duration: 0.5 }}
                    className={`h-full rounded-full ${
                      isOut ? 'bg-rose-500' : isLow ? 'bg-amber-500' : 'bg-emerald-500'
                    }`}
                  />
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Action Bar */}
      <div className="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <h4 className="text-sm font-bold text-slate-800">Riwayat Pergerakan Stok</h4>
          <p className="text-xs text-slate-400">Semua perubahan stok barang masuk & keluar</p>
        </div>
        <button
          onClick={openCatatModal}
          className="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-md shadow-blue-500/20 transition"
        >
          <Plus className="w-4 h-4" />
          <span>Catat Mutasi Stok</span>
        </button>
      </div>

      {/* Log Table */}
      <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        {loading ? (
          <div className="py-12 flex justify-center">
            <div className="w-7 h-7 border-3 border-blue-600 border-t-transparent rounded-full animate-spin" />
          </div>
        ) : logs.length === 0 ? (
          <div className="py-12 text-center text-xs text-slate-400">
            Belum ada riwayat pergerakan stok.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                <tr>
                  <th className="py-3.5 px-4">Tanggal</th>
                  <th className="py-3.5 px-4">Nama Produk</th>
                  <th className="py-3.5 px-4 text-center">Jenis</th>
                  <th className="py-3.5 px-4 text-right">Jumlah</th>
                  <th className="py-3.5 px-4">Keterangan</th>
                  <th className="py-3.5 px-4 text-center">Tindakan</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {logs.map((item, idx) => {
                  const isMasuk = item.jenis === 'Masuk';
                  const prod = produk.find(p => p.id === item.produk_id);
                  return (
                    <motion.tr
                      key={item.id}
                      initial={{ opacity: 0, y: 4 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: idx * 0.02 }}
                      className="hover:bg-slate-50/70 transition"
                    >
                      <td className="py-3 px-4 font-medium text-slate-500 whitespace-nowrap">{item.tanggal}</td>
                      <td className="py-3 px-4 font-bold text-slate-800">
                        {prod ? prod.nama : 'Produk Tidak Ditemukan'}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold ${
                          isMasuk
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'bg-rose-50 text-rose-700 border border-rose-200'
                        }`}>
                          {item.jenis}
                        </span>
                      </td>
                      <td className={`py-3 px-4 text-right font-extrabold whitespace-nowrap ${
                        isMasuk ? 'text-emerald-600' : 'text-rose-600'
                      }`}>
                        {isMasuk ? '+' : '-'} {item.jumlah} {prod?.satuan || 'pcs'}
                      </td>
                      <td className="py-3 px-4 text-slate-500">{item.keterangan || '-'}</td>
                      <td className="py-3 px-4 text-center">
                        <button
                          onClick={() => handleReversal(item.id)}
                          className="px-2.5 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 flex items-center gap-1 mx-auto transition"
                          title="Batalkan mutasi dan kembalikan stok"
                        >
                          <RotateCcw className="w-3 h-3" />
                          <span>Reversal</span>
                        </button>
                      </td>
                    </motion.tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal Catat Stok */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title="Catat Pergerakan Stok"
      >
        <form onSubmit={handleCatat} className="space-y-3.5 text-xs">
          <div>
            <label className="block font-semibold text-slate-700 mb-1">Pilih Produk</label>
            <select
              value={form.produk_id}
              onChange={(e) => setForm(f => ({ ...f, produk_id: e.target.value }))}
              required
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            >
              <option value="">-- Pilih Produk --</option>
              {produk.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.nama} (Stok Saat Ini: {p.stok} {p.satuan})
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Jenis Pergerakan</label>
            <div className="grid grid-cols-2 gap-2">
              <button
                type="button"
                onClick={() => setForm(f => ({ ...f, jenis: 'Masuk' }))}
                className={`py-2 rounded-xl font-bold border transition ${
                  form.jenis === 'Masuk'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-300'
                    : 'bg-white text-slate-600 border-slate-200'
                }`}
              >
                Barang Masuk (+)
              </button>
              <button
                type="button"
                onClick={() => setForm(f => ({ ...f, jenis: 'Keluar' }))}
                className={`py-2 rounded-xl font-bold border transition ${
                  form.jenis === 'Keluar'
                    ? 'bg-rose-50 text-rose-700 border-rose-300'
                    : 'bg-white text-slate-600 border-slate-200'
                }`}
              >
                Barang Keluar (-)
              </button>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Jumlah</label>
              <input
                type="number"
                min="1"
                value={form.jumlah}
                onChange={(e) => setForm(f => ({ ...f, jumlah: e.target.value }))}
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              />
            </div>
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Tanggal</label>
              <input
                type="date"
                value={form.tanggal}
                onChange={(e) => setForm(f => ({ ...f, tanggal: e.target.value }))}
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              />
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Keterangan</label>
            <input
              type="text"
              value={form.keterangan}
              onChange={(e) => setForm(f => ({ ...f, keterangan: e.target.value }))}
              placeholder="Contoh: Restock dari supplier"
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
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
              Simpan Mutasi
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
