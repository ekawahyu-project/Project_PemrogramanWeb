<?php

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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul', 255);
            $table->string('kategori', 100);
            $table->date('tanggal_dari');
            $table->date('tanggal_sampai');
            $table->string('status', 50)->default('Selesai');
            $table->text('catatan')->nullable();
            $table->string('pembuat', 100)->default('Admin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
