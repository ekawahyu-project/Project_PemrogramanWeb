# Tugas Kelompok (Project) — Sistem Manajemen UMKM Sederhana

**Mata Kuliah:** Pemrograman Web

## 1. Topik Aplikasi Web

Aplikasi web untuk membantu pelaku UMKM (Usaha Mikro, Kecil, dan Menengah) mengelola operasional harian usaha mereka: mencatat pemasukan/pengeluaran, mengelola stok barang, dan melihat laporan sederhana dalam bentuk dashboard.

**Nama aplikasi (sementara):** UMKM Manager

**Latar belakang masalah:**
Banyak pelaku UMKM masih mencatat transaksi secara manual (buku/kertas) sehingga sulit memantau kondisi keuangan usaha secara real-time. Aplikasi ini bertujuan menyederhanakan proses pencatatan tersebut dalam satu platform yang mudah digunakan.

---

## 2. Fungsionalitas yang Akan Dibangun

### 2.1 Autentikasi (Wajib ada, sesuai instruksi tugas)
- [ ] Halaman Login (username & password)
- [ ] Logout
- [ ] Proteksi halaman (hanya user yang login bisa akses dashboard)
- [ ] *(Tahap awal: validasi login memakai username/password hardcode di kode program, belum dari database — sesuai instruksi tugas saat ini)*
- [ ] *(Tahap lanjut: koneksi ke database untuk validasi login sungguhan)*

### 2.2 Manajemen Keuangan
- [ ] Catat transaksi pemasukan (jumlah, tanggal, kategori, catatan)
- [ ] Catat transaksi pengeluaran (jumlah, tanggal, kategori, catatan)
- [ ] Riwayat transaksi dengan filter (tanggal, kategori, jenis)
- [ ] Edit & hapus transaksi

### 2.3 Manajemen Stok Barang
- [ ] Tambah data barang (nama, harga, jumlah stok, satuan)
- [ ] Update stok (masuk/keluar barang)
- [ ] Notifikasi/tanda stok menipis (di bawah batas tertentu)
- [ ] Edit & hapus data barang

### 2.4 Dashboard & Laporan
- [ ] Ringkasan total pemasukan vs pengeluaran (harian/bulanan)
- [ ] Grafik sederhana (pemasukan-pengeluaran per bulan)
- [ ] Daftar barang dengan stok terendah
- [ ] Laba/rugi sederhana (pemasukan - pengeluaran)

### 2.5 Profil & Pengaturan
- [ ] Edit profil usaha (nama usaha, kategori usaha)
- [ ] Ganti password

---

## 3. Desain Halaman Login (Tahap Saat Ini)

Sesuai instruksi tugas: halaman login dibangun dengan **HTML, CSS, dan JavaScript**, di mana proses login berhasil menggunakan username & password yang **ditulis manual (hardcode) di kode program**, belum mengambil dari basis data.

- [ ] Struktur halaman login (HTML): input username, input password, tombol login
- [ ] Styling halaman login (CSS): tampilan bersih dan rapi
- [ ] Validasi login sederhana (JavaScript): cek username/password hardcode, tampilkan pesan berhasil/gagal

*(Sudah dibuatkan versi awalnya secara terpisah — file index.html, style.css, script.js)*

---

## 4. Rencana Teknologi (Tech Stack)

Menyesuaikan materi kelas Pemrograman Web (Form, HTTP GET/POST, Validasi):

| Bagian | Teknologi |
|---|---|
| Struktur halaman | HTML |
| Styling | CSS |
| Interaktivitas / validasi client-side | JavaScript |
| Pemrosesan form (tahap lanjut) | PHP (`$_GET`, `$_POST`, validasi server-side) |
| Database (tahap lanjut) | MySQL |
| Deployment | Menyusul (bisa hosting sederhana / XAMPP-Laragon utk development) |

---

## 5. Halaman yang Akan Dibangun

1. Halaman Login *(sedang dikerjakan)*
2. Dashboard utama
3. Halaman Transaksi (pemasukan & pengeluaran)
4. Halaman Manajemen Stok
5. Halaman Laporan/Grafik
6. Halaman Profil/Pengaturan

---

## 6. Pembagian Tugas Kelompok (isi sesuai anggota)

| Nama | Peran | Tugas |
|---|---|---|
| ... | ... | ... |
| ... | ... | ... |
| ... | ... | ... |

---

## 7. Catatan Tambahan
- Fitur bisa disesuaikan/dikurangi tergantung waktu pengerjaan dan kesepakatan kelompok.
- Prioritaskan fitur wajib (login) terlebih dahulu, mengikuti alur materi kelas: HTML form → validasi hardcode → nanti terhubung ke database.
- Proyek mata kuliah ini terintegrasi dengan proyek mata kuliah lain — pastikan desain & alur bisa nyambung dengan proyek lain di kelompok.
