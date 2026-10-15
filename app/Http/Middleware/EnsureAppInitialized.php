<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppInitialized
{
    /**
     * Handle an incoming request and ensure session data is initialized.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->initializeData();

        // Share common variables across all Blade views
        $inputClass = 'w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition';
        View::share('inputClass', $inputClass);

        // Sync with global $_SESSION array for backward compatibility
        $_SESSION = session()->all();

        $response = $next($request);

        // Sync back any changes made directly to $_SESSION
        if (isset($_SESSION) && is_array($_SESSION)) {
            foreach ($_SESSION as $key => $val) {
                session([$key => $val]);
            }
        }

        return $response;
    }

    /**
     * Initialize essential datasets into Laravel session.
     */
    protected function initializeData(): void
    {
        // 1. Users
        if (! session()->has('users')) {
            session([
                'users' => [
                    'admin' => [
                        'nama' => 'Administrator',
                        'username' => 'admin',
                        'email' => 'admin@gmail.com',
                        'password' => 'admin123',
                        'no_hp' => '08123456789',
                        'nama_usaha' => 'AlpetBizz Store',
                        'kategori' => 'Makanan & Minuman',
                        'alamat' => 'Malang',
                    ],
                ],
            ]);
        }

        // 2. Produk
        $produkFile = storage_path('app/produk.json');
        if (! session()->has('produk')) {
            if (file_exists($produkFile)) {
                $decoded = json_decode((string) file_get_contents($produkFile), true);
                session(['produk' => is_array($decoded) ? $decoded : $this->defaultProduk()]);
            } else {
                $defaultProduk = $this->defaultProduk();
                session(['produk' => $defaultProduk]);
                $this->saveProdukFile($defaultProduk);
            }
        }

        // 3. Transaksi
        if (! session()->has('transaksi')) {
            session([
                'transaksi' => [
                    ['id' => 't1',  'tanggal' => '2026-07-15', 'keterangan' => 'Penjualan Kopi Arabika',  'jenis' => 'Pemasukan',   'jumlah' => 385000, 'produk_id' => 'p1'],
                    ['id' => 't2',  'tanggal' => '2026-07-20', 'keterangan' => 'Biaya Sewa Tempat',        'jenis' => 'Pengeluaran', 'jumlah' => 500000, 'produk_id' => null],
                    ['id' => 't3',  'tanggal' => '2026-07-25', 'keterangan' => 'Penjualan Sabun Herbal',   'jenis' => 'Pemasukan',   'jumlah' => 120000, 'produk_id' => 'p5'],
                    ['id' => 't4',  'tanggal' => '2026-08-05', 'keterangan' => 'Penjualan Tas Kanvas',     'jenis' => 'Pemasukan',   'jumlah' => 315000, 'produk_id' => 'p4'],
                    ['id' => 't5',  'tanggal' => '2026-08-12', 'keterangan' => 'Beli Bahan Baku',          'jenis' => 'Pengeluaran', 'jumlah' => 300000, 'produk_id' => null],
                    ['id' => 't6',  'tanggal' => '2026-08-20', 'keterangan' => 'Penjualan Kopi Arabika',   'jenis' => 'Pemasukan',   'jumlah' => 440000, 'produk_id' => 'p1'],
                    ['id' => 't7',  'tanggal' => '2026-08-28', 'keterangan' => 'Biaya Listrik',            'jenis' => 'Pengeluaran', 'jumlah' => 150000, 'produk_id' => null],
                    ['id' => 't8',  'tanggal' => '2026-09-05', 'keterangan' => 'Penjualan Teh Hijau',      'jenis' => 'Pemasukan',   'jumlah' => 160000, 'produk_id' => 'p2'],
                    ['id' => 't9',  'tanggal' => '2026-09-12', 'keterangan' => 'Penjualan Kopi Arabika',   'jenis' => 'Pemasukan',   'jumlah' => 275000, 'produk_id' => 'p1'],
                    ['id' => 't10', 'tanggal' => '2026-09-18', 'keterangan' => 'Beli Bahan Baku',          'jenis' => 'Pengeluaran', 'jumlah' => 200000, 'produk_id' => null],
                    ['id' => 't11', 'tanggal' => '2026-09-22', 'keterangan' => 'Penjualan Sabun Herbal',   'jenis' => 'Pemasukan',   'jumlah' => 75000,  'produk_id' => 'p5'],
                    ['id' => 't12', 'tanggal' => '2026-09-25', 'keterangan' => 'Biaya Operasional',        'jenis' => 'Pengeluaran', 'jumlah' => 80000,  'produk_id' => null],
                    ['id' => 't13', 'tanggal' => '2026-09-28', 'keterangan' => 'Penjualan Tas Kanvas',     'jenis' => 'Pemasukan',   'jumlah' => 225000, 'produk_id' => 'p4'],
                ],
            ]);
        }

        // 4. Stok Log
        if (! session()->has('stok_log')) {
            session([
                'stok_log' => [
                    ['id' => 'sl1', 'tanggal' => '2026-09-20', 'produk_id' => 'p1', 'jenis' => 'Masuk',  'jumlah' => 30, 'keterangan' => 'Restock dari supplier'],
                    ['id' => 'sl2', 'tanggal' => '2026-09-22', 'produk_id' => 'p2', 'jenis' => 'Masuk',  'jumlah' => 20, 'keterangan' => 'Pembelian batch baru'],
                    ['id' => 'sl3', 'tanggal' => '2026-09-25', 'produk_id' => 'p1', 'jenis' => 'Keluar', 'jumlah' => 5,  'keterangan' => 'Penjualan'],
                    ['id' => 'sl4', 'tanggal' => '2026-09-27', 'produk_id' => 'p3', 'jenis' => 'Masuk',  'jumlah' => 10, 'keterangan' => 'Restock gula'],
                    ['id' => 'sl5', 'tanggal' => '2026-09-28', 'produk_id' => 'p4', 'jenis' => 'Keluar', 'jumlah' => 3,  'keterangan' => 'Penjualan tas'],
                ],
            ]);
        }

        // 5. Laporan Tersimpan
        if (! session()->has('laporan_tersimpan')) {
            session([
                'laporan_tersimpan' => [
                    ['id' => 'l1', 'judul' => 'Evaluasi Bisnis Juli 2026',    'kategori' => 'Evaluasi Bulanan',   'tanggal_dari' => '2026-07-01', 'tanggal_sampai' => '2026-07-31', 'status' => 'Selesai',        'catatan' => 'Bulan pertama operasi, operasional stabil.',         'dibuat' => '2026-08-01', 'pembuat' => 'Admin'],
                    ['id' => 'l2', 'judul' => 'Performa Penjualan Agustus',   'kategori' => 'Penjualan & Produk', 'tanggal_dari' => '2026-08-01', 'tanggal_sampai' => '2026-08-31', 'status' => 'Selesai',        'catatan' => 'Peningkatan repeat order kopi Arabika 250g.',        'dibuat' => '2026-09-01', 'pembuat' => 'Admin'],
                ],
            ]);
        }

        // 6. Catatan Rekomendasi
        if (! session()->has('catatan_rekomendasi')) {
            session([
                'catatan_rekomendasi' => [
                    ['id' => 'cr1', 'judul' => 'Tingkatkan stok Kopi Arabika', 'isi' => 'Produk terlaris. Tambah minimal 50 pack/bulan untuk hindari kehabisan.', 'prioritas' => 'Tinggi', 'dibuat' => '2026-09-28'],
                    ['id' => 'cr2', 'judul' => 'Promosi Teh Hijau',            'isi' => 'Stok menipis, jarang terjual. Pertimbangkan diskon atau bundle promo.',  'prioritas' => 'Sedang', 'dibuat' => '2026-09-29'],
                ],
            ]);
        }

        // 7. Profil
        if (! session()->has('profil')) {
            $curUname = session('user', 'admin');
            $users = session('users', []);
            $curUser = $users[$curUname] ?? [];
            session([
                'profil' => [
                    'no_hp' => $curUser['no_hp'] ?? '08123456789',
                    'nama_usaha' => $curUser['nama_usaha'] ?? 'AlpetBizz Store',
                    'kategori' => $curUser['kategori'] ?? 'Makanan & Minuman',
                    'alamat' => $curUser['alamat'] ?? 'Malang',
                    'foto' => $curUser['foto'] ?? '',
                ],
            ]);
        }
    }

    /**
     * Default product dataset.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function defaultProduk(): array
    {
        return [
            ['id' => 'p1', 'nama' => 'Kopi Arabika 250g', 'kategori' => 'Makanan & Minuman', 'harga_beli' => 30000, 'harga_jual' => 55000, 'satuan' => 'pack', 'stok' => 25, 'stok_min' => 5],
            ['id' => 'p2', 'nama' => 'Teh Hijau 100g',    'kategori' => 'Makanan & Minuman', 'harga_beli' => 12000, 'harga_jual' => 20000, 'satuan' => 'pack', 'stok' => 8,  'stok_min' => 10],
            ['id' => 'p3', 'nama' => 'Gula Pasir 1kg',    'kategori' => 'Makanan & Minuman', 'harga_beli' => 13000, 'harga_jual' => 17000, 'satuan' => 'kg',   'stok' => 3,  'stok_min' => 5],
            ['id' => 'p4', 'nama' => 'Tas Kanvas Polos',  'kategori' => 'Fashion',            'harga_beli' => 25000, 'harga_jual' => 45000, 'satuan' => 'pcs',  'stok' => 15, 'stok_min' => 3],
            ['id' => 'p5', 'nama' => 'Sabun Herbal',      'kategori' => 'Kecantikan',         'harga_beli' => 8000,  'harga_jual' => 15000, 'satuan' => 'pcs',  'stok' => 30, 'stok_min' => 10],
        ];
    }

    /**
     * Save product list to JSON file in storage.
     *
     * @param  array<int, array<string, mixed>>  $produk
     */
    protected function saveProdukFile(array $produk): void
    {
        $dir = storage_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            storage_path('app/produk.json'),
            json_encode(array_values($produk), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}
