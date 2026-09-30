import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, Plus, AlertTriangle, TrendingUp, Tag, Edit2, Trash2, CheckSquare } from 'lucide-react';
import { apiRequest } from '../api/client';
import Modal from '../components/Modal';

export default function RekomendasiPage({ showToast }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [form, setForm] = useState({
    id: '',
    judul: '',
    isi: '',
    prioritas: 'Sedang'
  });

  const loadData = async () => {
    try {
      const res = await apiRequest('rekomendasi_data', {}, 'GET');
      setData(res);
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
      judul: '',
      isi: '',
      prioritas: 'Sedang'
    });
    setIsModalOpen(true);
  };

  const openEditModal = (item) => {
    setEditingItem(item);
    setForm({
      id: item.id,
      judul: item.judul,
      isi: item.isi,
      prioritas: item.prioritas
    });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    if (!form.judul.trim() || !form.isi.trim()) {
      showToast('Judul dan isi catatan wajib diisi.', 'error');
      return;
    }

    try {
      await apiRequest('rekomendasi_save', form);
      showToast(editingItem ? 'Catatan rekomendasi diperbarui.' : 'Catatan rekomendasi baru ditambahkan!', 'success');
      setIsModalOpen(false);
      loadData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Hapus catatan rekomendasi ini?')) return;
    try {
      await apiRequest('rekomendasi_delete', { id });
      showToast('Catatan berhasil dihapus.', 'success');
      loadData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const formatRupiah = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

  const { best_sellers = [], restock_alerts = [], slow_moving = [], notes = [] } = data || {};

  return (
    <div className="space-y-6">
      {/* Smart Intelligence Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {/* Best Sellers Card */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2.5 mb-3">
              <div className="p-2 rounded-xl bg-blue-50 text-blue-600">
                <TrendingUp className="w-5 h-5" />
              </div>
              <div>
                <h4 className="font-bold text-sm text-slate-800">Produk Terlaris</h4>
                <p className="text-xs text-slate-400">Paling banyak terjual</p>
              </div>
            </div>

            {best_sellers.length === 0 ? (
              <p className="text-xs text-slate-400 py-6 text-center">Belum ada transaksi penjualan produk yang tercatat.</p>
            ) : (
              <div className="space-y-2 mt-4">
                {best_sellers.slice(0, 3).map((p, i) => (
                  <div key={p.id} className="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                    <div className="flex items-center gap-2.5">
                      <span className="w-5 h-5 rounded-full bg-blue-600 text-white font-extrabold text-[10px] flex items-center justify-center flex-shrink-0">
                        {i + 1}
                      </span>
                      <div className="min-w-0">
                        <p className="font-bold text-slate-800 truncate">{p.nama}</p>
                        <p className="text-[10px] text-slate-400">{p.tx_count} transaksi</p>
                      </div>
                    </div>
                    <div className="text-right flex-shrink-0">
                      <p className="font-bold text-blue-600">{formatRupiah(p.tx_total)}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Restock Priority Card */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2.5 mb-3">
              <div className="p-2 rounded-xl bg-amber-50 text-amber-600">
                <AlertTriangle className="w-5 h-5" />
              </div>
              <div>
                <h4 className="font-bold text-sm text-slate-800">Prioritas Restock</h4>
                <p className="text-xs text-slate-400">Stok mendekati batas aman</p>
              </div>
            </div>

            {restock_alerts.length === 0 ? (
              <div className="py-6 text-center text-xs text-emerald-600 font-semibold bg-emerald-50 rounded-xl mt-4">
                Semua stok produk aman!
              </div>
            ) : (
              <div className="space-y-2 mt-4">
                {restock_alerts.slice(0, 3).map((p) => (
                  <div key={p.id} className="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/60 border border-amber-200/70 text-xs">
                    <div className="min-w-0">
                      <p className="font-bold text-slate-800 truncate">{p.nama}</p>
                      <p className="text-[10px] text-amber-700">Min: {p.stok_min} {p.satuan}</p>
                    </div>
                    <span className="px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-200 text-amber-800 flex-shrink-0">
                      Sisa {p.stok}
                    </span>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Slow-Moving / Promotion Card */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2.5 mb-3">
              <div className="p-2 rounded-xl bg-purple-50 text-purple-600">
                <Tag className="w-5 h-5" />
              </div>
              <div>
                <h4 className="font-bold text-sm text-slate-800">Perlu Promosi</h4>
                <p className="text-xs text-slate-400">Belum ada transaksi</p>
              </div>
            </div>

            {slow_moving.length === 0 ? (
              <div className="py-6 text-center text-xs text-blue-600 font-semibold bg-blue-50 rounded-xl mt-4">
                Semua produk sudah pernah terjual!
              </div>
            ) : (
              <div className="space-y-2 mt-4">
                {slow_moving.slice(0, 3).map((p) => (
                  <div key={p.id} className="flex items-center justify-between p-2.5 rounded-xl bg-purple-50/60 border border-purple-200/70 text-xs">
                    <div className="min-w-0">
                      <p className="font-bold text-slate-800 truncate">{p.nama}</p>
                      <p className="text-[10px] text-purple-700">Tersedia: {p.stok} {p.satuan}</p>
                    </div>
                    <span className="px-2 py-0.5 rounded-full font-bold text-[10px] bg-purple-200 text-purple-800 flex-shrink-0">
                      Diskon / Promo
                    </span>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Action Items Notes Section */}
      <div className="space-y-4">
        <div className="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
          <div>
            <h4 className="text-sm font-bold text-slate-800 flex items-center gap-2">
              <CheckSquare className="w-4 h-4 text-blue-600" />
              <span>Rencana Tindak Lanjut Usaha</span>
            </h4>
            <p className="text-xs text-slate-400">Checklist dan target tindak lanjut operasional</p>
          </div>
          <button
            onClick={openAddModal}
            className="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-md shadow-blue-500/20 transition"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Catatan</span>
          </button>
        </div>

        {notes.length === 0 ? (
          <div className="bg-white p-12 text-center text-xs text-slate-400 rounded-2xl border border-slate-200/80">
            Belum ada rencana tindak lanjut yang dicatat. Buat sekarang untuk merapikan strategi tokomu!
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {notes.map((item, idx) => {
              const priorityColors = {
                Tinggi: 'bg-rose-50 text-rose-700 border-rose-200',
                Sedang: 'bg-amber-50 text-amber-700 border-amber-200',
                Rendah: 'bg-slate-100 text-slate-700 border-slate-200',
              };

              return (
                <motion.div
                  key={item.id}
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: idx * 0.04 }}
                  className="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between"
                >
                  <div>
                    <div className="flex items-center justify-between gap-2 mb-2">
                      <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold border ${priorityColors[item.prioritas] || priorityColors.Sedang}`}>
                        Prioritas {item.prioritas}
                      </span>
                      <span className="text-[10px] text-slate-400 font-medium">{item.dibuat}</span>
                    </div>
                    <h5 className="font-bold text-sm text-slate-800 mb-1">{item.judul}</h5>
                    <p className="text-xs text-slate-500 leading-relaxed">{item.isi}</p>
                  </div>

                  <div className="flex items-center justify-end gap-1 mt-4 pt-3 border-t border-slate-100">
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
                </motion.div>
              );
            })}
          </div>
        )}
      </div>

      {/* Modal Add / Edit */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingItem ? 'Edit Rencana Tindak Lanjut' : 'Tambah Rencana Baru'}
      >
        <form onSubmit={handleSave} className="space-y-3.5 text-xs">
          <div>
            <label className="block font-semibold text-slate-700 mb-1">Judul Strategi / Rencana</label>
            <input
              type="text"
              value={form.judul}
              onChange={(e) => setForm(f => ({ ...f, judul: e.target.value }))}
              placeholder="Contoh: Tambah stok Kopi Arabika 50 pack"
              required
              className="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Tingkat Prioritas</label>
            <div className="grid grid-cols-3 gap-2">
              {['Tinggi', 'Sedang', 'Rendah'].map((prio) => (
                <button
                  key={prio}
                  type="button"
                  onClick={() => setForm(f => ({ ...f, prioritas: prio }))}
                  className={`py-2 rounded-xl font-bold border transition ${
                    form.prioritas === prio
                      ? prio === 'Tinggi'
                        ? 'bg-rose-50 text-rose-700 border-rose-300'
                        : prio === 'Sedang'
                        ? 'bg-amber-50 text-amber-700 border-amber-300'
                        : 'bg-slate-100 text-slate-700 border-slate-300'
                      : 'bg-white text-slate-500 border-slate-200'
                  }`}
                >
                  {prio}
                </button>
              ))}
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Deskripsi Lengkap</label>
            <textarea
              rows="3"
              value={form.isi}
              onChange={(e) => setForm(f => ({ ...f, isi: e.target.value }))}
              placeholder="Jelaskan langkah konkret yang perlu dilakukan..."
              required
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
              Simpan Rencana
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
