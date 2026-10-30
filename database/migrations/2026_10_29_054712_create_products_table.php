<?php

// untuk membuat table pada bagian produk
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('kategori', 50);
            $table->unsignedBigInteger('harga_beli');
            $table->unsignedBigInteger('harga_jual');
            $table->string('satuan', 20);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_min')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
