# Tugas Kelompok (Project) — Sistem Manajemen UMKM Sederhana

**Mata Kuliah:** Pemrograman Web

## C. Deskripsi Singkat Project

Sistem informasi manajemen UMKM berbasis web yang menyediakan fitur untuk mengelola operasional usaha dalam satu platform. Pengguna dapat mencatat pemasukan dan pengeluaran, mengelola data produk dan stok barang, serta memantau kondisi usaha melalui dashboard dan laporan berbentuk grafik.

---

## D. Fungsi Utama & Pembagian Anggota

| Anggota | Fungsi yang Dikerjakan | Gambaran Singkat |
|---|---|---|
| [EWM] | Sistem Rekomendasi | Analisis barang terlaris, notifikasi restock, dan manajemen catatan rekomendasi (CRUD) |
| [MARA] | Halaman Laporan/Grafik | Grafik bulanan pemasukan vs pengeluaran (Chart.js) + manajemen laporan periode tersimpan (CRUD) |
| [AZNP] | Halaman Manajemen Stok | Pantau stok saat ini, catat masuk/keluar (otomatis sinkron ke produk), riwayat pergerakan & reversal (CRUD) |
| [ZAG] | Halaman Manajemen Produk | CRUD data produk: tambah, edit, hapus (membersihkan referensi transaksi), dan status stok |
| [AABS] | Halaman Manajemen Transaksi | CRUD pencatatan transaksi pemasukan & pengeluaran yang terhubung opsional ke produk |
| [ALL] | Login, Register, Dashboard, Profil | Fitur umum: autentikasi multi-user, dashboard ringkasan eksekutif, dan manajemen profil usaha |

---

## Status Implementasi & Arsitektur File

Arsitektur dibuat bersih dan flat (tidak boros folder `index.php`):

| Halaman / Fitur | File Utama | Status | CRUD |
|---|---|---|---|
| Entry Redirect | `index.php` | ✅ Selesai | Redirect otomatis ke login/dashboard |
| Login | `login.php` | ✅ Selesai | Verifikasi data akun session |
| Register | `register.php` | ✅ Selesai | **C** (pendaftaran akun baru) |
| Dashboard | `dashboard.php` | ✅ Selesai | **R** (ringkasan statistik & shortcut) |
| Transaksi | `transaksi.php` | ✅ Selesai | **C R U D** (kelola pemasukan/pengeluaran) |
| Produk | `produk.php` | ✅ Selesai | **C R U D** (kelola katalog & stok master) |
| Stok | `stok.php` | ✅ Selesai | **C R U D** (pergerakan stok barang) |
| Laporan | `laporan.php` | ✅ Selesai | **C R U D** (grafik live & arsip laporan periode) |
| Rekomendasi | `rekomendasi.php` | ✅ Selesai | **C R U D** (analisis pintar & catatan tindak lanjut) |
| Profil | `profil.php` (`profil.js`) | ✅ Selesai | **R U** (manajemen profil akun & usaha) |

---

## Alur Navigasi & `action=`

```
[index.php]    -- (Cek session) -> login.php atau dashboard.php
[register.php] --POST action="register.php" (action=register)--> simpan $_SESSION['users'] --> login.php
[login.php]    --POST action="login.php"--> validasi user --> set $_SESSION['user'] --> dashboard.php

[dashboard.php]   -- Ringkasan statistik, shortcut ke modul, dan 5 transaksi terakhir
[transaksi.php]   --POST action="transaksi.php" (action=tambah / update / hapus)
[produk.php]      --POST action="produk.php" (action=tambah / update / hapus)
[stok.php]        --POST action="stok.php" (action=catat / hapus_log)
[laporan.php]     --POST action="laporan.php" (action=simpan / update / hapus)
[rekomendasi.php] --POST action="rekomendasi.php" (action=tambah / update / hapus)
[profil.php]      --POST action="profil.php" (action=save)
[sidebar.php]     --POST action="dashboard.php" (action=logout) --> session_destroy() --> login.php
```

---

## Logika CRUD yang Diterapkan

1. **Transaksi (`transaksi.php`)**
   - **Create**: Tambah transaksi pemasukan/pengeluaran baru, opsional pilih produk terkait.
   - **Read**: Tabel daftar seluruh transaksi (diurutkan terbaru), filter jenis pemasukan/pengeluaran.
   - **Update**: Edit transaksi dengan parameter `?edit=id`.
   - **Delete**: Hapus transaksi dari daftar.

2. **Produk (`produk.php`)**
   - **Create**: Tambah produk baru dengan detail harga beli, harga jual, satuan, dan stok awal.
   - **Read**: Katalog daftar produk lengkap dengan indikator status (Normal / Menipis / Habis).
   - **Update**: Edit rincian informasi dan stok produk.
   - **Delete**: Hapus produk dari sistem (sekaligus mengosongkan relasi `produk_id` di transaksi).

3. **Stok (`stok.php`)**
   - **Create**: Catat barang Masuk/Keluar. Stok produk master otomatis terupdate secara sinkron.
   - **Read**: Monitor stok saat ini dengan visual progress bar + tabel riwayat mutasi stok.
   - **Delete (Reversal)**: Hapus log mutasi stok, sistem otomatis mengembalikan kuantitas stok produk ke saldo sebelumnya.

4. **Laporan (`laporan.php`)**
   - **Create**: Simpan snapshot laporan keuangan berdasarkan rentang tanggal (`tanggal_dari` s/d `tanggal_sampai`).
   - **Read**: Grafik batang bulanan interaktif Chart.js + daftar laporan tersimpan.
   - **Update**: Edit judul laporan dan catatan evaluasi bisnis.
   - **Delete**: Hapus arsip laporan yang sudah tidak diperlukan.

5. **Rekomendasi (`rekomendasi.php`)**
   - **Create**: Buat catatan rencana tindakan bisnis (judul, deskripsi, prioritas: Tinggi/Sedang/Rendah).
   - **Read**: Analisis cerdas produk terlaris, barang menipis yang perlu restock, dan barang belum terjual.
   - **Update**: Edit catatan rekomendasi dan prioritas tindak lanjut.
   - **Delete**: Hapus catatan rencana yang sudah diselesaikan.

---

## Teknologi

| Bagian | Teknologi |
|---|---|
| Struktur | HTML5 |
| Styling | Tailwind CSS v3 (CDN) — Tema Navy Elegan |
| Grafik | Chart.js (CDN) |
| Interaktivitas | JavaScript Vanilla (`profil.js`) |
| Backend & Session | PHP (`$_SESSION`, `$_POST`, `$_GET`) |
| Penyimpanan | Session-based Storage (Ringan, DRY & Efisien) |

---

## Struktur Direktori Saat Ini

```
Project_PemWeb/
├── includes/
│   ├── init.php              ← Inisialisasi data session & variabel bersama ($inputClass)
│   └── sidebar.php           ← Navigasi shared (DRY)
├── dashboard.php             ← Dashboard utama
├── index.php                 ← Entry redirector
├── laporan.php               ← Modul Laporan & Grafik (CRUD)
├── login.php                 ← Autentikasi Login
├── produk.php                ← Modul Manajemen Produk (CRUD)
├── profil.js                 ← Script interaksi profil
├── profil.php                ← Modul Profil Usaha
├── register.php              ← Registrasi Pengguna Baru
├── rekomendasi.php           ← Modul Rekomendasi Bisnis (CRUD)
├── stok.php                  ← Modul Manajemen Stok (CRUD)
├── transaksi.php             ← Modul Transaksi Keuangan (CRUD)
├── AGENTS.md
└── project-umkm-management.md
```
