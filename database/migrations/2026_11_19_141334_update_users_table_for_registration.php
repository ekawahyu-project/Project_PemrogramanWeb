
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nama');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique();
            $table->string('no_hp', 20)->nullable();
            $table->string('nama_usaha', 100)->nullable();
            $table->string('kategori_usaha', 50)->nullable();
            $table->text('alamat_usaha')->nullable();
            $table->string('foto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);

            $table->dropColumn([
                'username',
                'no_hp',
                'nama_usaha',
                'kategori_usaha',
                'alamat_usaha',
                'foto',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nama', 'name');
        });
    }
};
