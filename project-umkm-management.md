# Tugas Kelompok (Project) — Sistem Manajemen UMKM Sederhana

**Mata Kuliah:** Pemrograman Web

## C. Deskripsi Singkat Project

Sistem informasi manajemen UMKM berbasis web yang menyediakan fitur untuk mengelola operasional usaha dalam satu platform. Pengguna dapat mencatat pemasukan dan pengeluaran, mengelola data produk dan stok barang, serta memantau kondisi usaha melalui dashboard dan laporan berbentuk grafik.

---

## D. Fungsi Utama & Pembagian Anggota

| Anggota | Fungsi yang Dikerjakan | Gambaran Singkat |
|---|---|---|
| [EWM] | Sistem Rekomendasi | Merekomendasikan barang yang sering dibeli, stok menipis, dan produk belum terjual |
| [MARA] | Halaman Laporan/Grafik | Grafik batang bulanan pemasukan vs pengeluaran (Chart.js) + tabel ringkasan |
| [AZNP] | Halaman Manajemen Stok | Pantau stok saat ini, catat masuk/keluar, riwayat pergerakan dengan reversal |
| [ZAG] | Halaman Manajemen Produk | CRUD produk: tambah, edit, hapus, status stok (Normal/Menipis/Habis) |
| [AABS] | Halaman Manajemen Transaksi | CRUD transaksi pemasukan & pengeluaran, link opsional ke produk |
| [ALL] | Login, Register, Dashboard, Profil | Fitur umum: autentikasi, dashboard ringkasan, profil usaha |

---

## Status Implementasi

| Halaman | File | Status | CRUD |
|---|---|---|---|
| Login | `login/index.php` | ✅ Selesai | — |
| Register | `register/index.php` | ✅ Selesai | C (buat akun) |
| Dashboard | `dashboard/index.php` | ✅ Selesai | R (ringkasan) |
| Transaksi | `transaksi/index.php` | ✅ Selesai | **C R U D** |
| Produk | `produk/index.php` | ✅ Selesai | **C R U D** |
| Stok | `stok/index.php` | ✅ Selesai | **C R U D** |
| Laporan | `laporan/index.php` | ✅ Selesai | **C R U D** |
| Rekomendasi | `rekomendasi/index.php` | ✅ Selesai | **C R U D** |
| Profil | `profil/index.php` | ✅ Selesai | R U (edit profil) |

---

## Alur Navigasi & `action=`

```
[Register] --POST action="register"--> simpan $_SESSION['users'] --> Login
[Login]    --POST --> validasi $_SESSION['users'] --> Dashboard

[Dashboard]   -- read-only summary, link ke semua halaman
[Transaksi]   --POST action="tambah"  --> Create
              --GET  ?edit=id          --> pre-fill form
              --POST action="update"  --> Update
              --POST action="hapus"   --> Delete
[Produk]      --POST action="tambah"  --> Create
              --GET  ?edit=id          --> pre-fill form
              --POST action="update"  --> Update (juga bersihkan ref di transaksi)
              --POST action="hapus"   --> Delete
[Stok]        --POST action="catat"   --> Create log + update produk.stok
              --POST action="hapus_log"--> Delete log + reversal stok
[Laporan]     -- read-only grafik & tabel
[Rekomendasi] -- read-only analisis
[Profil]      --POST action="save"   --> Update
[Sidebar]     --POST action="logout" --> session_destroy() --> Login
```

---

## Logika CRUD yang Diterapkan

### Transaksi
- **C**: Tambah dengan opsional link ke produk
- **R**: Tabel semua transaksi, sorted terbaru, tampil nama produk jika ada
- **U**: GET `?edit=id` → pre-fill form → POST `action=update`
- **D**: Hapus transaksi

### Produk
- **C**: Tambah produk baru dengan stok awal
- **R**: Tabel produk dengan badge status stok (Normal/Menipis/Habis)
- **U**: Edit semua atribut produk termasuk stok
- **D**: Hapus produk & otomatis bersihkan `produk_id` di transaksi terkait

### Stok (logika terhubung ke Produk)
- **C**: Catat stok masuk/keluar → `produk.stok` diupdate otomatis
- **R**: Tabel stok saat ini + progress bar visual + riwayat pergerakan
- **U**: Tidak ada (history bersifat audit trail; edit via halaman Produk)
- **D**: Hapus log pergerakan → stok produk di-*reverse* otomatis

---

## Teknologi

| Bagian | Teknologi |
|---|---|
| Struktur | HTML5 |
| Styling | Tailwind CSS v3 (CDN) — tema navy |
| Grafik | Chart.js (CDN) |
| Client-side | JavaScript vanilla |
| Server-side | PHP (`$_SESSION`, `$_POST`, `$_GET`) |
| Penyimpanan | PHP Session (tahap awal tanpa database) |
| Database | MySQL *(tahap lanjut)* |
| Dev server | XAMPP / Laragon |

---

## Struktur File

```
Project_PemWeb/
├── includes/
│   ├── sidebar.php       ← Navigasi shared (DRY)
│   └── init.php          ← Seed data session (produk, transaksi, stok_log)
├── login/index.php
├── register/index.php
├── dashboard/index.php
├── transaksi/index.php   ← CRUD transaksi
├── produk/index.php      ← CRUD produk
├── stok/index.php        ← Manajemen stok + log
├── laporan/index.php     ← Grafik & laporan
├── rekomendasi/index.php ← Sistem rekomendasi
└── profil/index.php
```

---

## Catatan Teknis

- **Session-based storage**: Data disimpan di `$_SESSION`. Hilang saat sesi browser berakhir — disengaja untuk tahap awal sebelum koneksi database.
- **Akun default**: `username: admin` / `password: admin123` tersedia saat sesi baru dimulai.
- **Integritas data**: Hapus produk → referensi `produk_id` di transaksi dibersihkan. Hapus log stok → perubahan stok di-reverse.
- **Tailwind CSS**: CDN tanpa file CSS statis. Konfigurasi warna navy inline.
- **Chart.js**: CDN di `laporan/index.php` untuk grafik batang bulanan.
