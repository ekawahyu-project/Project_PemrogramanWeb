import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Plus, Search, Edit2, Trash2, Package } from 'lucide-react';
import { apiRequest } from '../api/client';
import Modal from '../components/Modal';

export default function ProdukPage({ showToast }) {
  const [produk, setProduk] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [selectedKategori, setSelectedKategori] = useState('Semua');

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [form, setForm] = useState({
    id: '',
    nama: '',
    kategori: 'Makanan & Minuman',
    harga_beli: '',
    harga_jual: '',
    satuan: 'pcs',
    stok: '0',
    stok_min: '5'
  });

  const kategoriList = ['Semua', 'Makanan & Minuman', 'Fashion', 'Kecantikan', 'Lainnya'];
  const satuanList = ['pcs', 'pack', 'kg', 'gram', 'liter', 'ml', 'box', 'lusin', 'meter', 'lembar'];

  const loadProduk = async () => {
    try {
      const res = await apiRequest('produk_list', {}, 'GET');
      setProduk(res.data || []);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProduk();
  }, []);

  const openAddModal = () => {
    setEditingItem(null);
    setForm({
      id: '',
      nama: '',
      kategori: 'Makanan & Minuman',
      harga_beli: '',
      harga_jual: '',
      satuan: 'pcs',
      stok: '0',
      stok_min: '5'
    });
    setIsModalOpen(true);
  };

  const openEditModal = (p) => {
    setEditingItem(p);
    setForm({
      id: p.id,
      nama: p.nama,
      kategori: p.kategori,
      harga_beli: p.harga_beli,
      harga_jual: p.harga_jual,
      satuan: p.satuan,
      stok: p.stok,
      stok_min: p.stok_min
    });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    if (!form.nama.trim()) {
      showToast('Nama produk wajib diisi.', 'error');
      return;
    }
    const beli = Number(form.harga_beli);
    const jual = Number(form.harga_jual);
    if (jual < beli) {
      showToast('Harga jual tidak boleh lebih kecil dari harga beli.', 'error');
      return;
    }

    try {
      await apiRequest('produk_save', form);
      showToast(editingItem ? 'Produk berhasil diperbarui.' : 'Produk baru berhasil ditambahkan!', 'success');
      setIsModalOpen(false);
      loadProduk();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Yakin ingin menghapus produk ini dari katalog?')) return;
    try {
      await apiRequest('produk_delete', { id });
      showToast('Produk berhasil dihapus.', 'success');
      loadProduk();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

  const filtered = produk.filter((p) => {
    const matchCat = selectedKategori === 'Semua' || p.kategori === selectedKategori;
    const matchSearch = p.nama.toLowerCase().includes(search.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <div className="space-y-5">
      {/* Header Toolbar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <div className="flex flex-wrap items-center gap-2.5 flex-1">
          <div className="relative flex-1 min-w-[200px]">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nama produk..."
              className="w-full pl-10 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
            />
          </div>

          <div className="flex bg-slate-100 p-1 rounded-xl overflow-x-auto max-w-full">
            {kategoriList.map((cat) => (
              <button
                key={cat}
                onClick={() => setSelectedKategori(cat)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition ${
                  selectedKategori === cat
                    ? 'bg-white text-slate-900 shadow-sm'
                    : 'text-slate-500 hover:text-slate-800'
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        <button
          onClick={openAddModal}
          className="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-md shadow-blue-500/20 transition"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Produk</span>
        </button>
      </div>

      {/* Table Catalog */}
      <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        {loading ? (
          <div className="py-12 flex justify-center">
            <div className="w-7 h-7 border-3 border-blue-600 border-t-transparent rounded-full animate-spin" />
          </div>
        ) : filtered.length === 0 ? (
          <div className="py-12 text-center text-xs text-slate-400">
            Tidak ada produk yang sesuai dengan filter.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                <tr>
                  <th className="py-3.5 px-4">Nama Produk</th>
                  <th className="py-3.5 px-4">Kategori</th>
                  <th className="py-3.5 px-4 text-right">Harga Beli</th>
                  <th className="py-3.5 px-4 text-right">Harga Jual</th>
                  <th className="py-3.5 px-4 text-center">Stok</th>
                  <th className="py-3.5 px-4 text-center">Status</th>
                  <th className="py-3.5 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {filtered.map((item, idx) => {
                  const isLow = Number(item.stok) <= Number(item.stok_min);
                  const isOut = Number(item.stok) === 0;

                  return (
                    <motion.tr
                      key={item.id}
                      initial={{ opacity: 0, y: 4 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: idx * 0.02 }}
                      className="hover:bg-slate-50/70 transition"
                    >
                      <td className="py-3 px-4 font-bold text-slate-800">
                        <div className="flex items-center gap-2">
                          <Package className="w-4 h-4 text-slate-400 flex-shrink-0" />
                          <span>{item.nama}</span>
                        </div>
                      </td>
                      <td className="py-3 px-4 text-slate-500 font-medium">
                        <span className="px-2 py-0.5 rounded-md bg-slate-100 text-[11px]">
                          {item.kategori}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-right text-slate-500 font-medium whitespace-nowrap">
                        {formatRupiah(item.harga_beli)}
                      </td>
                      <td className="py-3 px-4 text-right font-bold text-blue-600 whitespace-nowrap">
                        {formatRupiah(item.harga_jual)}
                      </td>
                      <td className="py-3 px-4 text-center font-bold">
                        {item.stok} <span className="text-[11px] font-normal text-slate-400">{item.satuan}</span>
                      </td>
                      <td className="py-3 px-4 text-center">
                        {isOut ? (
                          <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Habis
                          </span>
                        ) : isLow ? (
                          <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Menipis (&le; {item.stok_min})
                          </span>
                        ) : (
                          <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Aman
                          </span>
                        )}
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
        title={editingItem ? 'Edit Produk' : 'Tambah Produk Baru'}
      >
        <form onSubmit={handleSave} className="space-y-3.5 text-xs">
          <div>
            <label className="block font-semibold text-slate-700 mb-1">Nama Produk</label>
            <input
              type="text"
              value={form.nama}
              onChange={(e) => setForm(f => ({ ...f, nama: e.target.value }))}
              placeholder="Contoh: Kopi Arabika 250g"
              required
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Kategori</label>
              <select
                value={form.kategori}
                onChange={(e) => setForm(f => ({ ...f, kategori: e.target.value }))}
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              >
                {kategoriList.filter(k => k !== 'Semua').map(k => (
                  <option key={k} value={k}>{k}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Satuan</label>
              <select
                value={form.satuan}
                onChange={(e) => setForm(f => ({ ...f, satuan: e.target.value }))}
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              >
                {satuanList.map(s => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Harga Beli (Rp)</label>
              <input
                type="number"
                min="0"
                value={form.harga_beli}
                onChange={(e) => setForm(f => ({ ...f, harga_beli: e.target.value }))}
                placeholder="30000"
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              />
            </div>
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Harga Jual (Rp)</label>
              <input
                type="number"
                min="1"
                value={form.harga_jual}
                onChange={(e) => setForm(f => ({ ...f, harga_jual: e.target.value }))}
                placeholder="55000"
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Stok Awal</label>
              <input
                type="number"
                min="0"
                value={form.stok}
                onChange={(e) => setForm(f => ({ ...f, stok: e.target.value }))}
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              />
            </div>
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Batas Stok Minimum</label>
              <input
                type="number"
                min="0"
                value={form.stok_min}
                onChange={(e) => setForm(f => ({ ...f, stok_min: e.target.value }))}
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              />
            </div>
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
              Simpan Produk
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
