<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password'       => 'admin123',
                'nama'           => 'Administrator',
                'email'          => 'admin@gmail.com',
                'no_hp'          => '08123456789',
                'nama_usaha'     => 'AlpetBizz Store',
                'kategori_usaha' => 'Makanan & Minuman',
                'alamat_usaha'   => 'Malang',
            ]
        );
        $this->call(ProductSeeder::class);
    }
}