<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ID ditulis eksplisit (1 sampai 5) agar cocok dengan p1 sampai p5 yang lama
        $rows = [
            ['id' => 1, 'nama' => 'Kopi Arabika 250g', 'kategori' => 'Minuman',   'harga_beli' => 30000, 'harga_jual' => 55000, 'satuan' => 'pack', 'stok' => 25, 'stok_min' => 5],
            ['id' => 2, 'nama' => 'Teh Hijau 100g',    'kategori' => 'Minuman',   'harga_beli' => 12000, 'harga_jual' => 20000, 'satuan' => 'pack', 'stok' => 8,  'stok_min' => 10],
            ['id' => 3, 'nama' => 'Gula Pasir 1kg',    'kategori' => 'Makanan',   'harga_beli' => 13000, 'harga_jual' => 17000, 'satuan' => 'kg',   'stok' => 3,  'stok_min' => 5],
            ['id' => 4, 'nama' => 'Tas Kanvas Polos',  'kategori' => 'Fashion',   'harga_beli' => 25000, 'harga_jual' => 45000, 'satuan' => 'pcs',  'stok' => 15, 'stok_min' => 3],
            ['id' => 5, 'nama' => 'Sabun Herbal',      'kategori' => 'Kecantikan', 'harga_beli' => 8000,  'harga_jual' => 15000, 'satuan' => 'pcs',  'stok' => 30, 'stok_min' => 10],
        ];

        $rows = array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $rows);

        DB::table('products')->upsert(
            $rows,
            ['id'],
            ['nama', 'kategori', 'harga_beli', 'harga_jual', 'satuan', 'stok', 'stok_min', 'updated_at']
        );
    }
}