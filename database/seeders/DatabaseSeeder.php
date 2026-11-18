<?php

namespace Database\Seeders;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Recommendation;
use App\Models\Report;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => 'admin123',
                'nama' => 'Administrator',
                'email' => 'admin@gmail.com',
                'no_hp' => '08123456789',
                'nama_usaha' => 'AlpetBizz Store',
                'kategori_usaha' => 'Makanan & Minuman',
                'alamat_usaha' => 'Malang',
            ]
        );
        $productsData = [
            'p1' => ['nama' => 'Kopi Arabika 250g', 'kategori' => 'Minuman', 'harga_beli' => 30000, 'harga_jual' => 55000, 'satuan' => 'pack', 'stok' => 25, 'stok_min' => 5],
            'p2' => ['nama' => 'Teh Hijau 100g', 'kategori' => 'Minuman', 'harga_beli' => 12000, 'harga_jual' => 20000, 'satuan' => 'pack', 'stok' => 8, 'stok_min' => 10],
            'p3' => ['nama' => 'Gula Pasir 1kg', 'kategori' => 'Bahan Baku', 'harga_beli' => 13000, 'harga_jual' => 17000, 'satuan' => 'kg', 'stok' => 3, 'stok_min' => 5],
            'p4' => ['nama' => 'Tas Kanvas Polos', 'kategori' => 'Fashion', 'harga_beli' => 25000, 'harga_jual' => 45000, 'satuan' => 'pcs', 'stok' => 15, 'stok_min' => 3],
            'p5' => ['nama' => 'Sabun Herbal', 'kategori' => 'Kecantikan', 'harga_beli' => 8000, 'harga_jual' => 15000, 'satuan' => 'pcs', 'stok' => 30, 'stok_min' => 10],
        ];

        $products = [];
        foreach ($productsData as $key => $item) {
            $products[$key] = Product::firstOrCreate(['nama' => $item['nama']], $item);
        }

        if (Transaction::count() === 0) {
            $transactions = [
                ['tanggal' => '2026-07-15', 'keterangan' => 'Penjualan Kopi Arabika', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 385000, 'product_id' => $products['p1']->id],
                ['tanggal' => '2026-07-20', 'keterangan' => 'Biaya Sewa Tempat', 'jenis' => Transaction::TYPE_EXPENSE, 'jumlah' => 500000, 'product_id' => null],
                ['tanggal' => '2026-07-25', 'keterangan' => 'Penjualan Sabun Herbal', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 120000, 'product_id' => $products['p5']->id],
                ['tanggal' => '2026-08-05', 'keterangan' => 'Penjualan Tas Kanvas', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 315000, 'product_id' => $products['p4']->id],
                ['tanggal' => '2026-08-12', 'keterangan' => 'Beli Bahan Baku', 'jenis' => Transaction::TYPE_EXPENSE, 'jumlah' => 300000, 'product_id' => null],
                ['tanggal' => '2026-08-20', 'keterangan' => 'Penjualan Kopi Arabika', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 440000, 'product_id' => $products['p1']->id],
                ['tanggal' => '2026-08-28', 'keterangan' => 'Biaya Listrik', 'jenis' => Transaction::TYPE_EXPENSE, 'jumlah' => 150000, 'product_id' => null],
                ['tanggal' => '2026-09-05', 'keterangan' => 'Penjualan Teh Hijau', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 160000, 'product_id' => $products['p2']->id],
                ['tanggal' => '2026-09-12', 'keterangan' => 'Penjualan Kopi Arabika', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 275000, 'product_id' => $products['p1']->id],
                ['tanggal' => '2026-09-18', 'keterangan' => 'Beli Bahan Baku', 'jenis' => Transaction::TYPE_EXPENSE, 'jumlah' => 200000, 'product_id' => null],
                ['tanggal' => '2026-09-22', 'keterangan' => 'Penjualan Sabun Herbal', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 75000, 'product_id' => $products['p5']->id],
                ['tanggal' => '2026-09-25', 'keterangan' => 'Biaya Operasional', 'jenis' => Transaction::TYPE_EXPENSE, 'jumlah' => 80000, 'product_id' => null],
                ['tanggal' => '2026-09-28', 'keterangan' => 'Penjualan Tas Kanvas', 'jenis' => Transaction::TYPE_INCOME, 'jumlah' => 225000, 'product_id' => $products['p4']->id],
            ];

            foreach ($transactions as $trx) {
                Transaction::create($trx);
            }
        }

        if (StockLog::count() === 0) {
            $stockLogs = [
                ['tanggal' => '2026-09-20', 'product_id' => $products['p1']->id, 'jenis' => StockLog::TYPE_IN, 'jumlah' => 30, 'keterangan' => 'Restock dari supplier'],
                ['tanggal' => '2026-09-22', 'product_id' => $products['p2']->id, 'jenis' => StockLog::TYPE_IN, 'jumlah' => 20, 'keterangan' => 'Pembelian batch baru'],
                ['tanggal' => '2026-09-25', 'product_id' => $products['p1']->id, 'jenis' => StockLog::TYPE_OUT, 'jumlah' => 5, 'keterangan' => 'Penjualan'],
                ['tanggal' => '2026-09-27', 'product_id' => $products['p3']->id, 'jenis' => StockLog::TYPE_IN, 'jumlah' => 10, 'keterangan' => 'Restock gula'],
                ['tanggal' => '2026-09-28', 'product_id' => $products['p4']->id, 'jenis' => StockLog::TYPE_OUT, 'jumlah' => 3, 'keterangan' => 'Penjualan tas'],
            ];

            foreach ($stockLogs as $log) {
                StockLog::create($log);
            }
        }

        if (Report::count() === 0) {
            Report::create([
                'user_id' => $admin->id,
                'judul' => 'Evaluasi Bisnis Juli 2026',
                'kategori' => 'Evaluasi Bulanan',
                'tanggal_dari' => '2026-07-01',
                'tanggal_sampai' => '2026-07-31',
                'status' => 'Selesai',
                'catatan' => 'Bulan pertama operasi, operasional stabil.',
                'pembuat' => $admin->nama,
            ]);

            Report::create([
                'user_id' => $admin->id,
                'judul' => 'Performa Penjualan Agustus',
                'kategori' => 'Penjualan & Produk',
                'tanggal_dari' => '2026-08-01',
                'tanggal_sampai' => '2026-08-31',
                'status' => 'Selesai',
                'catatan' => 'Peningkatan repeat order kopi Arabika 250g.',
                'pembuat' => $admin->nama,
            ]);
        }

        if (Recommendation::count() === 0) {
            Recommendation::create([
                'judul' => 'Tingkatkan stok Kopi Arabika',
                'isi' => 'Produk terlaris. Tambah minimal 50 pack/bulan untuk hindari kehabisan.',
                'prioritas' => 'Tinggi',
                'dibuat' => '2026-09-28',
            ]);

            Recommendation::create([
                'judul' => 'Promosi Teh Hijau',
                'isi' => 'Stok menipis, jarang terjual. Pertimbangkan diskon atau bundle promo.',
                'prioritas' => 'Sedang',
                'dibuat' => '2026-09-29',
            ]);
        }

        if (Debt::count() === 0) {
            $debts = [
                [
                    'nama_pihak' => 'Warung Kopi Senja',
                    'no_hp' => '081234567801',
                    'jenis' => Debt::TYPE_RECEIVABLE,
                    'jumlah' => 550000,
                    'dibayar' => 250000,
                    'tanggal' => '2026-09-18',
                    'jatuh_tempo' => '2026-10-18',
                    'status' => Debt::STATUS_PARTIAL,
                    'keterangan' => 'Kasbon 10 pack Kopi Arabika 250g',
                ],
                [
                    'nama_pihak' => 'Ibu Ratna (Reseller)',
                    'no_hp' => '081298765432',
                    'jenis' => Debt::TYPE_RECEIVABLE,
                    'jumlah' => 450000,
                    'dibayar' => 0,
                    'tanggal' => '2026-09-24',
                    'jatuh_tempo' => '2026-10-24',
                    'status' => Debt::STATUS_UNPAID,
                    'keterangan' => 'Pengambilan 10 pcs Tas Kanvas Polos',
                ],
                [
                    'nama_pihak' => 'PT Nusantara Gula Murni',
                    'no_hp' => '081133445566',
                    'jenis' => Debt::TYPE_PAYABLE,
                    'jumlah' => 650000,
                    'dibayar' => 300000,
                    'tanggal' => '2026-09-20',
                    'jatuh_tempo' => '2026-10-20',
                    'status' => Debt::STATUS_PARTIAL,
                    'keterangan' => 'Tagihan suplai bahan baku gula & kemasan',
                ],
                [
                    'nama_pihak' => 'Kafe Sudut Kota',
                    'no_hp' => '081345678912',
                    'jenis' => Debt::TYPE_RECEIVABLE,
                    'jumlah' => 300000,
                    'dibayar' => 300000,
                    'tanggal' => '2026-09-10',
                    'jatuh_tempo' => '2026-09-25',
                    'status' => Debt::STATUS_PAID,
                    'keterangan' => 'Order rutin teh hijau & sabun herbal',
                ],
            ];

            foreach ($debts as $d) {
                Debt::create($d);
            }
        }
    }
}
