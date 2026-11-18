<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_movement_updates_product_stock_and_reverses_on_update_and_delete(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stok' => 10]);

        $storeResponse = $this->actingAs($user)->post('/stok', [
            'action' => 'catat',
            'produk_id' => $product->id,
            'jenis' => StockLog::TYPE_IN,
            'jumlah' => 5,
            'tanggal' => '2026-11-05',
            'keterangan' => 'Restock',
        ]);

        $storeResponse->assertRedirect('/stok');
        $product->refresh();
        $this->assertSame(15, $product->stok);

        $log = StockLog::first();
        $this->assertNotNull($log);

        $updateResponse = $this->actingAs($user)->post('/stok', [
            'action' => 'update_log',
            'id' => $log->id,
            'produk_id' => $product->id,
            'jenis' => StockLog::TYPE_OUT,
            'jumlah' => 3,
            'tanggal' => '2026-11-06',
            'keterangan' => 'Koreksi keluar',
        ]);

        $updateResponse->assertRedirect('/stok');
        $product->refresh();
        $this->assertSame(7, $product->stok);

        $deleteResponse = $this->actingAs($user)->post('/stok', [
            'action' => 'hapus_log',
            'id' => $log->id,
        ]);

        $deleteResponse->assertRedirect('/stok');
        $product->refresh();
        $this->assertSame(10, $product->stok);
        $this->assertModelMissing($log);
    }

    public function test_stock_outgoing_rejects_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['nama' => 'Gula Pasir', 'stok' => 3]);

        $response = $this->actingAs($user)->post('/stok', [
            'action' => 'catat',
            'produk_id' => $product->id,
            'jenis' => StockLog::TYPE_OUT,
            'jumlah' => 10,
            'tanggal' => '2026-11-05',
            'keterangan' => 'Terjual',
        ]);

        $response->assertRedirect('/stok');
        $response->assertSessionHas('error', 'Stok Gula Pasir tidak mencukupi (tersedia: 3).');
        $product->refresh();
        $this->assertSame(3, $product->stok);
        $this->assertDatabaseCount('stock_logs', 0);
    }
}
