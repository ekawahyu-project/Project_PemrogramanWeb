<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'name') && ! Schema::hasColumn('users', 'nama')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('name', 'nama');
            });
        }

        $missingColumns = [
            'username' => ! Schema::hasColumn('users', 'username'),
            'no_hp' => ! Schema::hasColumn('users', 'no_hp'),
            'nama_usaha' => ! Schema::hasColumn('users', 'nama_usaha'),
            'kategori_usaha' => ! Schema::hasColumn('users', 'kategori_usaha'),
            'alamat_usaha' => ! Schema::hasColumn('users', 'alamat_usaha'),
            'foto' => ! Schema::hasColumn('users', 'foto'),
        ];

        if (in_array(true, $missingColumns, true)) {
            Schema::table('users', function (Blueprint $table) use ($missingColumns) {
                if ($missingColumns['username']) {
                    $table->string('username', 50)->nullable()->unique();
                }

                if ($missingColumns['no_hp']) {
                    $table->string('no_hp', 20)->nullable();
                }

                if ($missingColumns['nama_usaha']) {
                    $table->string('nama_usaha', 100)->nullable();
                }

                if ($missingColumns['kategori_usaha']) {
                    $table->string('kategori_usaha', 50)->nullable();
                }

                if ($missingColumns['alamat_usaha']) {
                    $table->text('alamat_usaha')->nullable();
                }

                if ($missingColumns['foto']) {
                    $table->string('foto')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Kolom profil usaha sudah didefinisikan pada migrasi awal create_users_table.
    }
};
