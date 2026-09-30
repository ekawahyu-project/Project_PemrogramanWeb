/**
 * In-memory & LocalStorage Mock Data Store
 * Memungkinkan aplikasi React berjalan langsung secara lokal (dummy mode)
 * tanpa ketergantungan database atau server eksternal.
 */

const STORAGE_KEY = 'alpetbizz_dummy_db_v1';

const defaultData = {
  users: [
    { username: 'admin', nama: 'Administrator', email: 'admin@gmail.com', password: 'admin123' }
  ],
  currentUser: { username: 'admin', nama: 'Administrator', email: 'admin@gmail.com' },
  produk: [
    { id: 'p1', nama: 'Kopi Arabika 250g', kategori: 'Makanan & Minuman', harga_beli: 30000, harga_jual: 55000, satuan: 'pack', stok: 25, stok_min: 5 },
    { id: 'p2', nama: 'Teh Hijau 100g',    kategori: 'Makanan & Minuman', harga_beli: 12000, harga_jual: 20000, satuan: 'pack', stok: 8,  stok_min: 10 },
    { id: 'p3', nama: 'Gula Pasir 1kg',    kategori: 'Makanan & Minuman', harga_beli: 13000, harga_jual: 17000, satuan: 'kg',   stok: 3,  stok_min: 5 },
    { id: 'p4', nama: 'Tas Kanvas Polos',  kategori: 'Fashion',            harga_beli: 25000, harga_jual: 45000, satuan: 'pcs',  stok: 15, stok_min: 3 },
    { id: 'p5', nama: 'Sabun Herbal',      kategori: 'Kecantikan',         harga_beli: 8000,  harga_jual: 15000, satuan: 'pcs',  stok: 30, stok_min: 10 },
  ],
  transaksi: [
    { id: 't1',  tanggal: '2026-07-15', keterangan: 'Penjualan Kopi Arabika',  jenis: 'Pemasukan',   jumlah: 385000, produk_id: 'p1' },
    { id: 't2',  tanggal: '2026-07-20', keterangan: 'Biaya Sewa Tempat',        jenis: 'Pengeluaran', jumlah: 500000, produk_id: null },
    { id: 't3',  tanggal: '2026-07-25', keterangan: 'Penjualan Sabun Herbal',   jenis: 'Pemasukan',   jumlah: 120000, produk_id: 'p5' },
    { id: 't4',  tanggal: '2026-08-05', keterangan: 'Penjualan Tas Kanvas',     jenis: 'Pemasukan',   jumlah: 315000, produk_id: 'p4' },
    { id: 't5',  tanggal: '2026-08-12', keterangan: 'Beli Bahan Baku',          jenis: 'Pengeluaran', jumlah: 300000, produk_id: null },
    { id: 't6',  tanggal: '2026-08-20', keterangan: 'Penjualan Kopi Arabika',   jenis: 'Pemasukan',   jumlah: 440000, produk_id: 'p1' },
    { id: 't7',  tanggal: '2026-08-28', keterangan: 'Biaya Listrik',            jenis: 'Pengeluaran', jumlah: 150000, produk_id: null },
    { id: 't8',  tanggal: '2026-09-05', keterangan: 'Penjualan Teh Hijau',      jenis: 'Pemasukan',   jumlah: 160000, produk_id: 'p2' },
    { id: 't9',  tanggal: '2026-09-12', keterangan: 'Penjualan Kopi Arabika',   jenis: 'Pemasukan',   jumlah: 275000, produk_id: 'p1' },
    { id: 't10', tanggal: '2026-09-18', keterangan: 'Beli Bahan Baku',          jenis: 'Pengeluaran', jumlah: 200000, produk_id: null },
    { id: 't11', tanggal: '2026-09-22', keterangan: 'Penjualan Sabun Herbal',   jenis: 'Pemasukan',   jumlah: 75000,  produk_id: 'p5' },
    { id: 't12', tanggal: '2026-09-25', keterangan: 'Biaya Operasional',        jenis: 'Pengeluaran', jumlah: 80000,  produk_id: null },
    { id: 't13', tanggal: '2026-09-28', keterangan: 'Penjualan Tas Kanvas',     jenis: 'Pemasukan',   jumlah: 225000, produk_id: 'p4' },
  ],
  stok_log: [
    { id: 'sl1', tanggal: '2026-09-20', produk_id: 'p1', jenis: 'Masuk',  jumlah: 30, keterangan: 'Restock dari supplier' },
    { id: 'sl2', tanggal: '2026-09-22', produk_id: 'p2', jenis: 'Keluar', jumlah: 5,  keterangan: 'Pesanan offline bazaar' },
    { id: 'sl3', tanggal: '2026-09-25', produk_id: 'p3', jenis: 'Keluar', jumlah: 4,  keterangan: 'Dipakai untuk sampel promosi' },
  ],
  laporan_tersimpan: [
    {
      id: 'l1',
      judul: 'Laporan Kuartal 3 (Juli - Sept)',
      tanggal_dari: '2026-07-01',
      tanggal_sampai: '2026-09-30',
      pemasukan: 1995000,
      pengeluaran: 1230000,
      laba: 765000,
      catatan: 'Penjualan stabil dan menunjukkan tren profit positif.',
      dibuat: '2026-09-30'
    }
  ],
  catatan_rekomendasi: [
    {
      id: 'cr1',
      judul: 'Restock Prioritas Kopi Arabika',
      isi: 'Penjualan tertinggi bulan ini. Segera hubungi supplier sebelum kehabisan.',
      prioritas: 'Tinggi',
      dibuat: '2026-09-28'
    },
    {
      id: 'cr2',
      judul: 'Bundling Teh Hijau & Gula Pasir',
      isi: 'Buat paket promosi bundling hemat untuk mempercepat perputaran persediaan.',
      prioritas: 'Sedang',
      dibuat: '2026-09-29'
    },
    {
      id: 'cr3',
      judul: 'Promosi Konten Tas Kanvas',
      isi: 'Unggah video showcase produk di media sosial untuk segmen mahasiswa.',
      prioritas: 'Rendah',
      dibuat: '2026-09-30'
    }
  ],
  profil: {
    nama_usaha: 'AlpetBizz UMKM Store',
    no_hp: '08123456789',
    kategori: 'Makanan & Minuman',
    alamat: 'Jl. Merdeka No. 45, Kampus Barat'
  }
};

function getDb() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(defaultData));
      return defaultData;
    }
    return JSON.parse(raw);
  } catch {
    return defaultData;
  }
}

function saveDb(data) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  } catch (err) {
    console.error('Failed to save to localStorage:', err);
  }
}

export function handleMockApi(action, data = {}) {
  const db = getDb();

  switch (action) {
    case 'auth_check':
      return {
        success: true,
        loggedIn: !!db.currentUser,
        user: db.currentUser
      };

    case 'login': {
      const { username, password } = data;
      const found = db.users.find(
        u => (u.username === username || u.email.toLowerCase() === username.toLowerCase()) && u.password === password
      );
      if (found) {
        db.currentUser = { username: found.username, nama: found.nama, email: found.email };
        saveDb(db);
        return { success: true, user: db.currentUser };
      }
      throw new Error('Username/email atau kata sandi salah.');
    }

    case 'register': {
      const { nama, username, email, password } = data;
      if (db.users.some(u => u.username === username)) {
        throw new Error('Username sudah terdaftar.');
      }
      const newUser = { nama, username, email, password };
      db.users.push(newUser);
      saveDb(db);
      return { success: true, message: 'Pendaftaran berhasil.' };
    }

    case 'logout':
      db.currentUser = null;
      saveDb(db);
      return { success: true };

    case 'dashboard': {
      let pem = 0, peng = 0;
      db.transaksi.forEach(t => {
        if (t.jenis === 'Pemasukan') pem += Number(t.jumlah);
        else peng += Number(t.jumlah);
      });
      const lowStock = db.produk.filter(p => Number(p.stok) <= Number(p.stok_min));
      const sortedTrx = [...db.transaksi].sort((a, b) => b.tanggal.localeCompare(a.tanggal)).slice(0, 5);

      const txMasukCount = db.transaksi.filter(t => t.jenis === 'Pemasukan').length;
      const txKeluarCount = db.transaksi.filter(t => t.jenis === 'Pengeluaran').length;

      return {
        success: true,
        stats: {
          pemasukan: pem,
          pengeluaran: peng,
          laba: pem - peng,
          total_produk: db.produk.length,
          low_stock_count: lowStock.length,
          tx_masuk_count: txMasukCount,
          tx_keluar_count: txKeluarCount
        },
        low_stock_products: lowStock,
        recent_transactions: sortedTrx
      };
    }

    case 'produk_list':
      return { success: true, data: db.produk };

    case 'produk_save': {
      const { id, nama, kategori, satuan, harga_beli, harga_jual, stok, stok_min } = data;
      const beli = Number(harga_beli);
      const jual = Number(harga_jual);
      if (id) {
        const idx = db.produk.findIndex(p => p.id === id);
        if (idx !== -1) {
          db.produk[idx] = { ...db.produk[idx], nama, kategori, satuan, harga_beli: beli, harga_jual: jual, stok: Number(stok), stok_min: Number(stok_min) };
        }
      } else {
        db.produk.push({
          id: 'p' + Date.now(),
          nama,
          kategori,
          satuan,
          harga_beli: beli,
          harga_jual: jual,
          stok: Number(stok),
          stok_min: Number(stok_min)
        });
      }
      saveDb(db);
      return { success: true, message: 'Produk berhasil disimpan.' };
    }

    case 'produk_delete': {
      const { id } = data;
      db.produk = db.produk.filter(p => p.id !== id);
      db.transaksi.forEach(t => {
        if (t.produk_id === id) t.produk_id = null;
      });
      saveDb(db);
      return { success: true, message: 'Produk dihapus.' };
    }

    case 'transaksi_list': {
      const sorted = [...db.transaksi].sort((a, b) => b.tanggal.localeCompare(a.tanggal));
      return { success: true, data: sorted };
    }

    case 'transaksi_save': {
      const { id, tanggal, keterangan, jenis, jumlah, produk_id } = data;
      const jml = Number(jumlah);
      if (id) {
        const idx = db.transaksi.findIndex(t => t.id === id);
        if (idx !== -1) {
          db.transaksi[idx] = { ...db.transaksi[idx], tanggal, keterangan, jenis, jumlah: jml, produk_id: produk_id || null };
        }
      } else {
        db.transaksi.push({
          id: 't' + Date.now(),
          tanggal,
          keterangan,
          jenis,
          jumlah: jml,
          produk_id: produk_id || null
        });
      }
      saveDb(db);
      return { success: true, message: 'Transaksi berhasil disimpan.' };
    }

    case 'transaksi_delete': {
      const { id } = data;
      db.transaksi = db.transaksi.filter(t => t.id !== id);
      saveDb(db);
      return { success: true, message: 'Transaksi berhasil dihapus.' };
    }

    case 'stok_list': {
      const sortedLogs = [...db.stok_log].sort((a, b) => b.tanggal.localeCompare(a.tanggal));
      return { success: true, logs: sortedLogs, produk: db.produk };
    }

    case 'stok_catat': {
      const { produk_id, jenis, jumlah, tanggal, keterangan } = data;
      const jml = Number(jumlah);
      const prod = db.produk.find(p => p.id === produk_id);
      if (!prod) throw new Error('Produk tidak ditemukan.');
      if (jenis === 'Keluar' && prod.stok < jml) {
        throw new Error(`Stok ${prod.nama} tidak mencukupi (tersedia: ${prod.stok}).`);
      }
      prod.stok = jenis === 'Masuk' ? prod.stok + jml : prod.stok - jml;
      db.stok_log.push({
        id: 'sl' + Date.now(),
        tanggal: tanggal || new Date().toISOString().split('T')[0],
        produk_id,
        jenis,
        jumlah: jml,
        keterangan
      });
      saveDb(db);
      return { success: true, message: 'Mutasi stok berhasil dicatat.' };
    }

    case 'stok_reversal': {
      const { id } = data;
      const log = db.stok_log.find(l => l.id === id);
      if (log) {
        const prod = db.produk.find(p => p.id === log.produk_id);
        if (prod) {
          prod.stok = log.jenis === 'Masuk' ? Math.max(0, prod.stok - log.jumlah) : prod.stok + log.jumlah;
        }
        db.stok_log = db.stok_log.filter(l => l.id !== id);
        saveDb(db);
        return { success: true, message: 'Reversal mutasi stok berhasil.' };
      }
      throw new Error('Log mutasi tidak ditemukan.');
    }

    case 'laporan_data': {
      const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      const monthly = months.map(m => ({ bulan: m, pemasukan: 0, pengeluaran: 0, laba: 0 }));

      db.transaksi.forEach(t => {
        const mIdx = parseInt(t.tanggal.substring(5, 7), 10) - 1;
        if (mIdx >= 0 && mIdx < 12) {
          if (t.jenis === 'Pemasukan') monthly[mIdx].pemasukan += Number(t.jumlah);
          else monthly[mIdx].pengeluaran += Number(t.jumlah);
        }
      });
      monthly.forEach(m => m.laba = m.pemasukan - m.pengeluaran);

      return {
        success: true,
        year: 2026,
        monthly,
        saved_reports: db.laporan_tersimpan
      };
    }

    case 'laporan_save': {
      const { id, judul, tanggal_dari, tanggal_sampai, catatan } = data;
      if (id) {
        const idx = db.laporan_tersimpan.findIndex(l => l.id === id);
        if (idx !== -1) {
          db.laporan_tersimpan[idx].judul = judul;
          db.laporan_tersimpan[idx].catatan = catatan;
          saveDb(db);
          return { success: true, message: 'Laporan berhasil diperbarui.' };
        }
      }
      let pem = 0, peng = 0;
      db.transaksi.forEach(t => {
        if (t.tanggal >= tanggal_dari && t.tanggal <= tanggal_sampai) {
          if (t.jenis === 'Pemasukan') pem += Number(t.jumlah);
          else peng += Number(t.jumlah);
        }
      });
      const newLaporan = {
        id: 'l' + Date.now(),
        judul,
        tanggal_dari,
        tanggal_sampai,
        pemasukan: pem,
        pengeluaran: peng,
        laba: pem - peng,
        catatan,
        dibuat: new Date().toISOString().split('T')[0]
      };
      db.laporan_tersimpan.push(newLaporan);
      saveDb(db);
      return { success: true, message: 'Laporan berhasil disimpan.' };
    }

    case 'laporan_delete': {
      const { id } = data;
      db.laporan_tersimpan = db.laporan_tersimpan.filter(l => l.id !== id);
      saveDb(db);
      return { success: true, message: 'Laporan dihapus.' };
    }

    case 'rekomendasi_data': {
      const productSales = {};
      db.transaksi.forEach(t => {
        if (t.jenis === 'Pemasukan' && t.produk_id) {
          if (!productSales[t.produk_id]) productSales[t.produk_id] = { count: 0, total: 0 };
          productSales[t.produk_id].count += 1;
          productSales[t.produk_id].total += Number(t.jumlah);
        }
      });
      const bestSellers = [];
      db.produk.forEach(p => {
        if (productSales[p.id]) {
          bestSellers.push({
            id: p.id,
            nama: p.nama,
            kategori: p.kategori,
            stok: p.stok,
            tx_count: productSales[p.id].count,
            tx_total: productSales[p.id].total
          });
        }
      });
      bestSellers.sort((a, b) => b.tx_total - a.tx_total);
      const restockAlerts = db.produk.filter(p => Number(p.stok) <= Number(p.stok_min));
      const slowMoving = db.produk.filter(p => !productSales[p.id]);

      return {
        success: true,
        best_sellers: bestSellers,
        restock_alerts: restockAlerts,
        slow_moving: slowMoving,
        notes: db.catatan_rekomendasi
      };
    }

    case 'rekomendasi_save': {
      const { id, judul, isi, prioritas } = data;
      if (id) {
        const idx = db.catatan_rekomendasi.findIndex(c => c.id === id);
        if (idx !== -1) {
          db.catatan_rekomendasi[idx] = { ...db.catatan_rekomendasi[idx], judul, isi, prioritas };
        }
      } else {
        db.catatan_rekomendasi.push({
          id: 'cr' + Date.now(),
          judul,
          isi,
          prioritas,
          dibuat: new Date().toISOString().split('T')[0]
        });
      }
      saveDb(db);
      return { success: true, message: 'Catatan rekomendasi disimpan.' };
    }

    case 'rekomendasi_delete': {
      const { id } = data;
      db.catatan_rekomendasi = db.catatan_rekomendasi.filter(c => c.id !== id);
      saveDb(db);
      return { success: true, message: 'Catatan dihapus.' };
    }

    case 'profil_get':
      return {
        success: true,
        user: db.currentUser || { username: 'admin', nama: 'Administrator', email: 'admin@gmail.com' },
        profil: db.profil
      };

    case 'profil_save': {
      const { nama_usaha, no_hp, kategori, alamat } = data;
      db.profil = { nama_usaha, no_hp, kategori, alamat };
      saveDb(db);
      return { success: true, message: 'Profil usaha berhasil disimpan.', profil: db.profil };
    }

    default:
      return { success: true };
  }
}
