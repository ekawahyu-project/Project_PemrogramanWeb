import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Plus, Search, Edit2, Trash2, ArrowDownLeft, ArrowUpRight } from 'lucide-react';
import { apiRequest } from '../api/client';
import Modal from '../components/Modal';

export default function TransaksiPage({ showToast }) {
  const [transaksi, setTransaksi] = useState([]);
  const [produkList, setProdukList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filterType, setFilterType] = useState('Semua');
  const [search, setSearch] = useState('');

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [form, setForm] = useState({
    id: '',
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: '',
    jenis: 'Pemasukan',
    jumlah: '',
    produk_id: ''
  });

  const loadData = async () => {
    try {
      const [resTrx, resProd] = await Promise.all([
        apiRequest('transaksi_list', {}, 'GET'),
        apiRequest('produk_list', {}, 'GET')
      ]);
      setTransaksi(resTrx.data || []);
      setProdukList(resProd.data || []);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const openAddModal = () => {
    setEditingItem(null);
    setForm({
      id: '',
      tanggal: new Date().toISOString().split('T')[0],
      keterangan: '',
      jenis: 'Pemasukan',
      jumlah: '',
      produk_id: ''
    });
    setIsModalOpen(true);
  };

  const openEditModal = (item) => {
    setEditingItem(item);
    setForm({
      id: item.id,
      tanggal: item.tanggal,
      keterangan: item.keterangan,
      jenis: item.jenis,
      jumlah: item.jumlah,
      produk_id: item.produk_id || ''
    });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    if (!form.keterangan.trim()) {
      showToast('Keterangan transaksi wajib diisi.', 'error');
      return;
    }
    if (!form.jumlah || Number(form.jumlah) <= 0) {
      showToast('Jumlah uang harus lebih besar dari 0.', 'error');
      return;
    }

    try {
      await apiRequest('transaksi_save', form);
      showToast(editingItem ? 'Transaksi berhasil diperbarui.' : 'Transaksi baru berhasil dicatat!', 'success');
      setIsModalOpen(false);
      loadData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) return;
    try {
      await apiRequest('transaksi_delete', { id });
      showToast('Transaksi berhasil dihapus.', 'success');
      loadData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

  const filtered = transaksi.filter((t) => {
    const matchType = filterType === 'Semua' || t.jenis === filterType;
    const matchSearch = t.keterangan.toLowerCase().includes(search.toLowerCase());
    return matchType && matchSearch;
  });

  return (
    <div className="space-y-5">
      {/* Header Toolbar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        {/* Search & Filter */}
        <div className="flex flex-wrap items-center gap-2.5 flex-1">
          <div className="relative flex-1 min-w-[200px]">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari transaksi..."
              className="w-full pl-10 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
            />
          </div>

          <div className="flex bg-slate-100 p-1 rounded-xl">
            {['Semua', 'Pemasukan', 'Pengeluaran'].map((type) => (
              <button
                key={type}
                onClick={() => setFilterType(type)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                  filterType === type
                    ? 'bg-white text-slate-900 shadow-sm'
                    : 'text-slate-500 hover:text-slate-800'
                }`}
              >
                {type}
              </button>
            ))}
          </div>
        </div>

        {/* Add Button */}
        <button
          onClick={openAddModal}
          className="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-md shadow-blue-500/20 transition"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Transaksi</span>
        </button>
      </div>

      {/* Transactions Table / List */}
      <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        {loading ? (
          <div className="py-12 flex justify-center">
            <div className="w-7 h-7 border-3 border-blue-600 border-t-transparent rounded-full animate-spin" />
          </div>
        ) : filtered.length === 0 ? (
          <div className="py-12 text-center text-xs text-slate-400">
            Tidak ada transaksi yang cocok dengan kriteria pencarian.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                <tr>
                  <th className="py-3.5 px-4">Tanggal</th>
                  <th className="py-3.5 px-4">Keterangan</th>
                  <th className="py-3.5 px-4">Jenis</th>
                  <th className="py-3.5 px-4">Produk Terkait</th>
                  <th className="py-3.5 px-4 text-right">Jumlah</th>
                  <th className="py-3.5 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {filtered.map((item, idx) => {
                  const isIncome = item.jenis === 'Pemasukan';
                  const prod = produkList.find((p) => p.id === item.produk_id);
                  return (
                    <motion.tr
                      key={item.id}
                      initial={{ opacity: 0, y: 4 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: idx * 0.02 }}
                      className="hover:bg-slate-50/70 transition"
                    >
                      <td className="py-3 px-4 font-medium text-slate-500 whitespace-nowrap">{item.tanggal}</td>
                      <td className="py-3 px-4 font-bold text-slate-800">{item.keterangan}</td>
                      <td className="py-3 px-4">
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10px] ${
                          isIncome
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'bg-rose-50 text-rose-700 border border-rose-200'
                        }`}>
                          {isIncome ? <ArrowDownLeft className="w-3 h-3" /> : <ArrowUpRight className="w-3 h-3" />}
                          {item.jenis}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-slate-500">
                        {prod ? prod.nama : '-'}
                      </td>
                      <td className={`py-3 px-4 text-right font-extrabold whitespace-nowrap ${
                        isIncome ? 'text-emerald-600' : 'text-rose-600'
                      }`}>
                        {isIncome ? '+' : '-'} {formatRupiah(item.jumlah)}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <div className="flex items-center justify-center gap-1">
                          <button
                            onClick={() => openEditModal(item)}
                            className="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                            title="Edit"
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
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal Add / Edit */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingItem ? 'Edit Transaksi' : 'Tambah Transaksi Baru'}
      >
        <form onSubmit={handleSave} className="space-y-3.5 text-xs">
          <div>
            <label className="block font-semibold text-slate-700 mb-1">Jenis Transaksi</label>
            <div className="grid grid-cols-2 gap-2">
              <button
                type="button"
                onClick={() => setForm(f => ({ ...f, jenis: 'Pemasukan' }))}
                className={`py-2 rounded-xl font-bold border transition ${
                  form.jenis === 'Pemasukan'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-300'
                    : 'bg-white text-slate-600 border-slate-200'
                }`}
              >
                Pemasukan (+)
              </button>
              <button
                type="button"
                onClick={() => setForm(f => ({ ...f, jenis: 'Pengeluaran' }))}
                className={`py-2 rounded-xl font-bold border transition ${
                  form.jenis === 'Pengeluaran'
                    ? 'bg-rose-50 text-rose-700 border-rose-300'
                    : 'bg-white text-slate-600 border-slate-200'
                }`}
              >
                Pengeluaran (-)
              </button>
            </div>
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

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Keterangan</label>
            <input
              type="text"
              value={form.keterangan}
              onChange={(e) => setForm(f => ({ ...f, keterangan: e.target.value }))}
              placeholder="Contoh: Penjualan Kopi Arabika"
              required
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Jumlah Uang (Rp)</label>
            <input
              type="number"
              min="1"
              value={form.jumlah}
              onChange={(e) => setForm(f => ({ ...f, jumlah: e.target.value }))}
              placeholder="Contoh: 150000"
              required
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Produk Terkait (Opsional)</label>
            <select
              value={form.produk_id}
              onChange={(e) => setForm(f => ({ ...f, produk_id: e.target.value }))}
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            >
              <option value="">-- Tidak Terhubung ke Produk --</option>
              {produkList.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.nama} (Stok: {p.stok})
                </option>
              ))}
            </select>
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
              Simpan
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
