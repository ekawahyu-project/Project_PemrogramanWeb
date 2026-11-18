<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLog;
use DomainException;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    /**
     * Catat pergerakan stok baru dan perbarui stok produk secara atomik.
     *
     * @throws DomainException
     */
    public function record(int $productId, string $jenis, int $jumlah, string $tanggal, string $keterangan): StockLog
    {
        return DB::transaction(function () use ($productId, $jenis, $jumlah, $tanggal, $keterangan): StockLog {
            $product = Product::where('id', $productId)->lockForUpdate()->first();

            if (! $product) {
                throw new DomainException('Produk tidak ditemukan.');
            }

            if ($jenis === StockLog::TYPE_OUT && $product->stok < $jumlah) {
                throw new DomainException("Stok {$product->nama} tidak mencukupi (tersedia: {$product->stok}).");
            }

            $product->stok = $jenis === StockLog::TYPE_IN
                ? $product->stok + $jumlah
                : $product->stok - $jumlah;
            $product->save();

            return StockLog::create([
                'product_id' => $product->id,
                'tanggal' => $tanggal,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
            ]);
        });
    }

    /**
     * Perbarui log mutasi stok dan hitung ulang stok produk terkait.
     *
     * @throws DomainException
     */
    public function update(StockLog $stockLog, int $productId, string $jenis, int $jumlah, string $tanggal, string $keterangan): StockLog
    {
        return DB::transaction(function () use ($stockLog, $productId, $jenis, $jumlah, $tanggal, $keterangan): StockLog {
            $oldProduct = Product::where('id', $stockLog->product_id)->lockForUpdate()->first();

            if ($oldProduct) {
                $oldProduct->stok = $stockLog->isIncoming()
                    ? max(0, $oldProduct->stok - $stockLog->jumlah)
                    : $oldProduct->stok + $stockLog->jumlah;
                $oldProduct->save();
            }

            $targetProduct = $productId === $stockLog->product_id && $oldProduct
                ? $oldProduct
                : Product::where('id', $productId)->lockForUpdate()->first();

            if (! $targetProduct) {
                throw new DomainException('Produk tidak ditemukan.');
            }

            if ($jenis === StockLog::TYPE_OUT && $targetProduct->stok < $jumlah) {
                throw new DomainException("Stok {$targetProduct->nama} tidak mencukupi untuk mutasi ini (tersedia: {$targetProduct->stok}).");
            }

            $targetProduct->stok = $jenis === StockLog::TYPE_IN
                ? $targetProduct->stok + $jumlah
                : $targetProduct->stok - $jumlah;
            $targetProduct->save();

            $stockLog->update([
                'product_id' => $targetProduct->id,
                'tanggal' => $tanggal,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
            ]);

            return $stockLog;
        });
    }

    /**
     * Hapus log mutasi stok dan kembalikan perubahan stok produk.
     */
    public function reverseAndDelete(StockLog $stockLog): void
    {
        DB::transaction(function () use ($stockLog): void {
            $product = Product::where('id', $stockLog->product_id)->lockForUpdate()->first();

            if ($product) {
                $product->stok = $stockLog->isIncoming()
                    ? max(0, $product->stok - $stockLog->jumlah)
                    : $product->stok + $stockLog->jumlah;
                $product->save();
            }

            $stockLog->delete();
        });
    }
}
