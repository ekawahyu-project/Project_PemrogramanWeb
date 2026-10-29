<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nama', 'kategori', 'harga_beli',
    'harga_jual', 'satuan', 'stok', 'stok_min',
])]
class Product extends Model
{

    protected function casts(): array
    {
        return [
            'harga_beli' => 'integer',
            'harga_jual' => 'integer',
            'stok'       => 'integer',
            'stok_min'   => 'integer',
        ];
    }
}