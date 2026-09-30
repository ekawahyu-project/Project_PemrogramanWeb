<?php
// includes/init.php Inisialisasi semua data session (include setelah session_start)

$fileProduk = file_exists(__DIR__ . '/../../data/produk.json')
    ? __DIR__ . '/../../data/produk.json'
    : __DIR__ . '/../data/produk.json';

if (!function_exists('produkAwal')) {
    function produkAwal(): array
    {
        return [
            ['id' => 'p1', 'nama' => 'Kopi Arabika 250g', 'kategori' => 'Makanan & Minuman', 'harga_beli' => 30000, 'harga_jual' => 55000, 'satuan' => 'pack', 'stok' => 25, 'stok_min' => 5],
            ['id' => 'p2', 'nama' => 'Teh Hijau 100g',    'kategori' => 'Makanan & Minuman', 'harga_beli' => 12000, 'harga_jual' => 20000, 'satuan' => 'pack', 'stok' => 8,  'stok_min' => 10],
            ['id' => 'p3', 'nama' => 'Gula Pasir 1kg',    'kategori' => 'Makanan & Minuman', 'harga_beli' => 13000, 'harga_jual' => 17000, 'satuan' => 'kg',   'stok' => 3,  'stok_min' => 5],
            ['id' => 'p4', 'nama' => 'Tas Kanvas Polos',  'kategori' => 'Fashion',            'harga_beli' => 25000, 'harga_jual' => 45000, 'satuan' => 'pcs',  'stok' => 15, 'stok_min' => 3],
            ['id' => 'p5', 'nama' => 'Sabun Herbal',      'kategori' => 'Kecantikan',         'harga_beli' => 8000,  'harga_jual' => 15000, 'satuan' => 'pcs',  'stok' => 30, 'stok_min' => 10],
        ];
    }
}

if (!function_exists('simpanProduk')) {
    function simpanProduk(): bool {
        global $fileProduk;
        $dir = dirname($fileProduk);
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) return false;
        return file_put_contents(
            $fileProduk,
            json_encode(array_values($_SESSION['produk']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        ) !== false;
    }
}

// Sumber data utama: file JSON. Kalau belum ada / rusak, pakai data awal lalu simpan.
if (file_exists($fileProduk)) {
    $dataJson = json_decode(file_get_contents($fileProduk), true);
    $_SESSION['produk'] = is_array($dataJson) ? $dataJson : produkAwal();
} else {
    $_SESSION['produk'] = produkAwal();
    simpanProduk();
}

if (!isset($_SESSION['transaksi'])) {
    $_SESSION['transaksi'] = [
        // Juli 2026
        ['id' => 't1',  'tanggal' => '2026-07-15', 'keterangan' => 'Penjualan Kopi Arabika',  'jenis' => 'Pemasukan',   'jumlah' => 385000, 'produk_id' => 'p1'],
        ['id' => 't2',  'tanggal' => '2026-07-20', 'keterangan' => 'Biaya Sewa Tempat',        'jenis' => 'Pengeluaran', 'jumlah' => 500000, 'produk_id' => null],
        ['id' => 't3',  'tanggal' => '2026-07-25', 'keterangan' => 'Penjualan Sabun Herbal',   'jenis' => 'Pemasukan',   'jumlah' => 120000, 'produk_id' => 'p5'],
        // Agustus 2026
        ['id' => 't4',  'tanggal' => '2026-08-05', 'keterangan' => 'Penjualan Tas Kanvas',     'jenis' => 'Pemasukan',   'jumlah' => 315000, 'produk_id' => 'p4'],
        ['id' => 't5',  'tanggal' => '2026-08-12', 'keterangan' => 'Beli Bahan Baku',          'jenis' => 'Pengeluaran', 'jumlah' => 300000, 'produk_id' => null],
        ['id' => 't6',  'tanggal' => '2026-08-20', 'keterangan' => 'Penjualan Kopi Arabika',   'jenis' => 'Pemasukan',   'jumlah' => 440000, 'produk_id' => 'p1'],
        ['id' => 't7',  'tanggal' => '2026-08-28', 'keterangan' => 'Biaya Listrik',            'jenis' => 'Pengeluaran', 'jumlah' => 150000, 'produk_id' => null],
        // September 2026
        ['id' => 't8',  'tanggal' => '2026-09-05', 'keterangan' => 'Penjualan Teh Hijau',      'jenis' => 'Pemasukan',   'jumlah' => 160000, 'produk_id' => 'p2'],
        ['id' => 't9',  'tanggal' => '2026-09-12', 'keterangan' => 'Penjualan Kopi Arabika',   'jenis' => 'Pemasukan',   'jumlah' => 275000, 'produk_id' => 'p1'],
        ['id' => 't10', 'tanggal' => '2026-09-18', 'keterangan' => 'Beli Bahan Baku',          'jenis' => 'Pengeluaran', 'jumlah' => 200000, 'produk_id' => null],
        ['id' => 't11', 'tanggal' => '2026-09-22', 'keterangan' => 'Penjualan Sabun Herbal',   'jenis' => 'Pemasukan',   'jumlah' => 75000,  'produk_id' => 'p5'],
        ['id' => 't12', 'tanggal' => '2026-09-25', 'keterangan' => 'Biaya Operasional',        'jenis' => 'Pengeluaran', 'jumlah' => 80000,  'produk_id' => null],
        ['id' => 't13', 'tanggal' => '2026-09-28', 'keterangan' => 'Penjualan Tas Kanvas',     'jenis' => 'Pemasukan',   'jumlah' => 225000, 'produk_id' => 'p4'],
    ];
}

if (!isset($_SESSION['stok_log'])) {
    $_SESSION['stok_log'] = [
        ['id' => 'sl1', 'tanggal' => '2026-09-20', 'produk_id' => 'p1', 'jenis' => 'Masuk',  'jumlah' => 30, 'keterangan' => 'Restock dari supplier'],
        ['id' => 'sl2', 'tanggal' => '2026-09-22', 'produk_id' => 'p2', 'jenis' => 'Masuk',  'jumlah' => 20, 'keterangan' => 'Pembelian batch baru'],
        ['id' => 'sl3', 'tanggal' => '2026-09-25', 'produk_id' => 'p1', 'jenis' => 'Keluar', 'jumlah' => 5,  'keterangan' => 'Penjualan'],
        ['id' => 'sl4', 'tanggal' => '2026-09-27', 'produk_id' => 'p3', 'jenis' => 'Masuk',  'jumlah' => 10, 'keterangan' => 'Restock gula'],
        ['id' => 'sl5', 'tanggal' => '2026-09-28', 'produk_id' => 'p4', 'jenis' => 'Keluar', 'jumlah' => 3,  'keterangan' => 'Penjualan tas'],
    ];
}

if (!isset($_SESSION['laporan_tersimpan'])) {
    $_SESSION['laporan_tersimpan'] = [
        ['id' => 'l1', 'judul' => 'Evaluasi Bisnis Juli 2026',    'kategori' => 'Evaluasi Bulanan',   'tanggal_dari' => '2026-07-01', 'tanggal_sampai' => '2026-07-31', 'status' => 'Selesai',        'catatan' => 'Bulan pertama operasi, operasional stabil.',         'dibuat' => '2026-08-01', 'pembuat' => 'Admin'],
        ['id' => 'l2', 'judul' => 'Performa Penjualan Agustus',   'kategori' => 'Penjualan & Produk', 'tanggal_dari' => '2026-08-01', 'tanggal_sampai' => '2026-08-31', 'status' => 'Selesai',        'catatan' => 'Peningkatan repeat order kopi Arabika 250g.',        'dibuat' => '2026-09-01', 'pembuat' => 'Admin'],
    ];
} else {
    // Normalisasi session lama agar memiliki atribut baru
    foreach ($_SESSION['laporan_tersimpan'] as &$lItem) {
        if (!isset($lItem['kategori'])) $lItem['kategori'] = 'Evaluasi Bulanan';
        if (!isset($lItem['status']))   $lItem['status']   = 'Selesai';
        if (!isset($lItem['pembuat']))  $lItem['pembuat']  = 'Admin';
    }
    unset($lItem);
}

if (!isset($_SESSION['catatan_rekomendasi'])) {
    $_SESSION['catatan_rekomendasi'] = [
        ['id' => 'cr1', 'judul' => 'Tingkatkan stok Kopi Arabika', 'isi' => 'Produk terlaris. Tambah minimal 50 pack/bulan untuk hindari kehabisan.', 'prioritas' => 'Tinggi', 'dibuat' => '2026-09-28'],
        ['id' => 'cr2', 'judul' => 'Promosi Teh Hijau',            'isi' => 'Stok menipis, jarang terjual. Pertimbangkan diskon atau bundle promo.',  'prioritas' => 'Sedang', 'dibuat' => '2026-09-29'],
    ];
}

// Inisialisasi & normalisasi data akun pengguna
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        'admin' => [
            'nama'       => 'Administrator',
            'username'   => 'admin',
            'email'      => 'admin@gmail.com',
            'password'   => 'admin123',
            'no_hp'      => '08123456789',
            'nama_usaha' => 'AlpetBizz Store',
            'kategori'   => 'Makanan & Minuman',
            'alamat'     => 'Jl. Boulevard Raya No. 12, Jakarta',
        ]
    ];
} else {
    foreach ($_SESSION['users'] as $uKey => &$uVal) {
        if (!isset($uVal['username']) || empty($uVal['username'])) $uVal['username'] = $uKey;
        if (!isset($uVal['no_hp']))      $uVal['no_hp']      = '';
        if (!isset($uVal['nama_usaha'])) $uVal['nama_usaha'] = '';
        if (!isset($uVal['kategori']))   $uVal['kategori']   = '';
        if (!isset($uVal['alamat']))     $uVal['alamat']     = '';
    }
    unset($uVal);
}

// Inisialisasi profil pengguna aktif
if (!isset($_SESSION['profil'])) {
    $curUname = $_SESSION['user'] ?? 'admin';
    $curUser = $_SESSION['users'][$curUname] ?? [];
    $_SESSION['profil'] = [
        'no_hp'      => $curUser['no_hp']      ?? '08123456789',
        'nama_usaha' => $curUser['nama_usaha'] ?? 'AlpetBizz Store',
        'kategori'   => $curUser['kategori']   ?? 'Makanan & Minuman',
        'alamat'     => $curUser['alamat']     ?? 'Jl. Boulevard Raya No. 12, Jakarta',
    ];
}

// Helper: cari produk berdasarkan ID
if (!function_exists('getProdukById')) {
    function getProdukById(string $id): ?array
    {
        foreach ($_SESSION['produk'] as $p) {
            if ($p['id'] === $id) return $p;
        }
        return null;
    }
}

// Variabel UI bersama — tersedia di semua halaman yang include init.php
$inputClass = 'w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition';
