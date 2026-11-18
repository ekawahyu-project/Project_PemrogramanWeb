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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pihak', 100);
            $table->string('no_hp', 30)->nullable();
            $table->string('jenis', 20)->default('Piutang');
            $table->unsignedBigInteger('jumlah');
            $table->unsignedBigInteger('dibayar')->default(0);
            $table->date('tanggal');
            $table->date('jatuh_tempo')->nullable();
            $table->string('status', 20)->default('Belum Lunas');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
