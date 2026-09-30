import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Store, Phone, MapPin, Tag, CheckCircle2, Shield } from 'lucide-react';
import { apiRequest } from '../api/client';
import { useAuth } from '../context/AuthContext';

export default function ProfilPage({ showToast }) {
  const { user, refreshAuth } = useAuth();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const [form, setForm] = useState({
    nama_usaha: '',
    no_hp: '',
    kategori: '',
    alamat: ''
  });

  const kategoriList = [
    'Makanan & Minuman',
    'Fashion',
    'Kerajinan',
    'Jasa',
    'Kecantikan',
    'Elektronik',
    'Lainnya'
  ];

  const loadProfil = async () => {
    try {
      const res = await apiRequest('profil_get', {}, 'GET');
      if (res.profil) {
        setForm({
          nama_usaha: res.profil.nama_usaha || '',
          no_hp: res.profil.no_hp || '',
          kategori: res.profil.kategori || '',
          alamat: res.profil.alamat || ''
        });
      }
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProfil();
  }, []);

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      await apiRequest('profil_save', form);
      showToast('Profil usaha berhasil diperbarui!', 'success');
      refreshAuth();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[300px]">
        <div className="w-8 h-8 border-3 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  const initial = user?.nama ? user.nama.charAt(0).toUpperCase() : 'A';

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Account Profile Card */}
      <motion.div
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-5"
      >
        <div className="w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center font-black text-3xl shadow-lg shadow-blue-500/25 flex-shrink-0">
          {initial}
        </div>
        <div className="flex-1 text-center sm:text-left">
          <div className="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
            <h3 className="text-xl font-bold text-slate-900">{user?.nama || 'Administrator'}</h3>
            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 self-center sm:self-auto">
              <Shield className="w-3 h-3" />
              <span>Owner UMKM</span>
            </span>
          </div>
          <p className="text-xs text-slate-400">Username: <span className="font-semibold text-slate-700">@{user?.username}</span> &bull; Email: <span className="font-semibold text-slate-700">{user?.email || '-'}</span></p>
          <p className="text-xs text-slate-500 mt-2">
            Akun aktif terverifikasi dalam sistem pencatatan keuangan AlpetBizz.
          </p>
        </div>
      </motion.div>

      {/* Business Details Form */}
      <motion.div
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm"
      >
        <div className="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100">
          <div className="p-2.5 rounded-2xl bg-blue-50 text-blue-600">
            <Store className="w-5 h-5" />
          </div>
          <div>
            <h4 className="font-bold text-base text-slate-900">Informasi Usaha (UMKM)</h4>
            <p className="text-xs text-slate-400">Detail identitas bisnis untuk kop dan arsip laporan</p>
          </div>
        </div>

        <form onSubmit={handleSave} className="space-y-4 text-xs">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nama Usaha / Toko</label>
              <div className="relative">
                <Store className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  type="text"
                  value={form.nama_usaha}
                  onChange={(e) => setForm(f => ({ ...f, nama_usaha: e.target.value }))}
                  placeholder="Contoh: Kedai Kopi Alpet"
                  className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
                />
              </div>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
              <div className="relative">
                <Phone className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  type="text"
                  value={form.no_hp}
                  onChange={(e) => setForm(f => ({ ...f, no_hp: e.target.value }))}
                  placeholder="Contoh: 08123456789"
                  className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
                />
              </div>
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Kategori Usaha</label>
            <div className="relative">
              <Tag className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <select
                value={form.kategori}
                onChange={(e) => setForm(f => ({ ...f, kategori: e.target.value }))}
                className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 outline-none"
              >
                <option value="">-- Pilih Kategori Bisnis --</option>
                {kategoriList.map(k => (
                  <option key={k} value={k}>{k}</option>
                ))}
              </select>
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-700 mb-1">Alamat Usaha / Lokasi</label>
            <div className="relative">
              <MapPin className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <textarea
                rows="3"
                value={form.alamat}
                onChange={(e) => setForm(f => ({ ...f, alamat: e.target.value }))}
                placeholder="Tuliskan alamat toko, kota, kode pos..."
                className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 outline-none resize-none"
              />
            </div>
          </div>

          <div className="pt-4 flex justify-end">
            <button
              type="submit"
              disabled={saving}
              className="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-semibold rounded-xl flex items-center gap-2 shadow-md shadow-blue-500/20 transition disabled:opacity-50"
            >
              {saving ? (
                <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
              ) : (
                <>
                  <CheckCircle2 className="w-4 h-4" />
                  <span>Simpan Perubahan</span>
                </>
              )}
            </button>
          </div>
        </form>
      </motion.div>
    </div>
  );
}
