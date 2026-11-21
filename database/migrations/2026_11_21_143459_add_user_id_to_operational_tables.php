<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan isolasi kepemilikan pengguna (user_id) dan indeks komposit
     * pada tabel operasional agar data antar akun UMKM terpisah secara ketat.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'user_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        $hasLegacyProductUnique = Schema::hasIndex('products', 'products_nama_unique');
        $hasScopedProductUnique = Schema::hasIndex('products', 'products_user_id_nama_unique');

        if ($hasLegacyProductUnique || ! $hasScopedProductUnique) {
            Schema::table('products', function (Blueprint $table) use ($hasLegacyProductUnique, $hasScopedProductUnique): void {
                if ($hasLegacyProductUnique) {
                    $table->dropUnique('products_nama_unique');
                }

                if (! $hasScopedProductUnique) {
                    $table->unique(['user_id', 'nama'], 'products_user_id_nama_unique');
                }
            });
        }

        if (! Schema::hasColumn('transactions', 'user_id')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('transactions', 'transactions_user_id_tanggal_id_index')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->index(['user_id', 'tanggal', 'id'], 'transactions_user_id_tanggal_id_index');
            });
        }

        if (! Schema::hasColumn('stock_logs', 'user_id')) {
            Schema::table('stock_logs', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('stock_logs', 'stock_logs_user_id_tanggal_id_index')) {
            Schema::table('stock_logs', function (Blueprint $table): void {
                $table->index(['user_id', 'tanggal', 'id'], 'stock_logs_user_id_tanggal_id_index');
            });
        }

        if (! Schema::hasColumn('recommendations', 'user_id')) {
            Schema::table('recommendations', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('recommendations', 'recommendations_user_id_index')) {
            Schema::table('recommendations', function (Blueprint $table): void {
                $table->index('user_id', 'recommendations_user_id_index');
            });
        }

        if (! Schema::hasIndex('reports', 'reports_user_id_index')) {
            Schema::table('reports', function (Blueprint $table): void {
                $table->index('user_id', 'reports_user_id_index');
            });
        }

        if (! Schema::hasIndex('debts', 'debts_user_id_index')) {
            Schema::table('debts', function (Blueprint $table): void {
                $table->index('user_id', 'debts_user_id_index');
            });
        }

        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId !== null) {
            foreach (['products', 'transactions', 'stock_logs', 'recommendations', 'reports'] as $tableName) {
                DB::table($tableName)->whereNull('user_id')->update(['user_id' => $firstUserId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('debts', 'debts_user_id_index')) {
            Schema::table('debts', function (Blueprint $table): void {
                $table->dropIndex('debts_user_id_index');
            });
        }

        if (Schema::hasIndex('reports', 'reports_user_id_index')) {
            Schema::table('reports', function (Blueprint $table): void {
                $table->dropIndex('reports_user_id_index');
            });
        }

        if (Schema::hasIndex('recommendations', 'recommendations_user_id_index')) {
            Schema::table('recommendations', function (Blueprint $table): void {
                $table->dropIndex('recommendations_user_id_index');
            });
        }

        if (Schema::hasColumn('recommendations', 'user_id')) {
            Schema::table('recommendations', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasIndex('stock_logs', 'stock_logs_user_id_tanggal_id_index')) {
            Schema::table('stock_logs', function (Blueprint $table): void {
                $table->dropIndex('stock_logs_user_id_tanggal_id_index');
            });
        }

        if (Schema::hasColumn('stock_logs', 'user_id')) {
            Schema::table('stock_logs', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasIndex('transactions', 'transactions_user_id_tanggal_id_index')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->dropIndex('transactions_user_id_tanggal_id_index');
            });
        }

        if (Schema::hasColumn('transactions', 'user_id')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasIndex('products', 'products_user_id_nama_unique')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropUnique('products_user_id_nama_unique');
            });
        }

        if (Schema::hasColumn('products', 'user_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (! Schema::hasIndex('products', 'products_nama_unique')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->unique('nama', 'products_nama_unique');
            });
        }
    }
};
