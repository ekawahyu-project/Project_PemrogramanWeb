<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_transactions(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['nama' => 'Sabun Herbal']);

        $storeResponse = $this->actingAs($user)->post('/transaksi', [
            'action' => 'tambah',
            'tanggal' => '2026-11-01',
            'jenis' => Transaction::TYPE_INCOME,
            'keterangan' => 'Penjualan Sabun',
            'jumlah' => 75000,
            'produk_id' => $product->id,
        ]);

        $storeResponse->assertRedirect('/transaksi');
        $transaction = Transaction::where('keterangan', 'Penjualan Sabun')->first();
        $this->assertNotNull($transaction);
        $this->assertSame($product->id, $transaction->product_id);

        $indexResponse = $this->actingAs($user)->get('/transaksi');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Penjualan Sabun');
        $indexResponse->assertSee('Sabun Herbal');

        $updateResponse = $this->actingAs($user)->post('/transaksi', [
            'action' => 'update',
            'id' => $transaction->id,
            'tanggal' => '2026-11-02',
            'jenis' => Transaction::TYPE_EXPENSE,
            'keterangan' => 'Biaya Pengiriman',
            'jumlah' => 50000,
            'produk_id' => '',
        ]);

        $updateResponse->assertRedirect('/transaksi');
        $transaction->refresh();
        $this->assertSame(Transaction::TYPE_EXPENSE, $transaction->jenis);
        $this->assertNull($transaction->product_id);

        $deleteResponse = $this->actingAs($user)->post('/transaksi', [
            'action' => 'hapus',
            'id' => $transaction->id,
        ]);

        $deleteResponse->assertRedirect('/transaksi');
        $this->assertModelMissing($transaction);
    }

    public function test_transaction_store_rejects_empty_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/transaksi', [
            'action' => 'tambah',
            'tanggal' => '2026-11-01',
            'jenis' => Transaction::TYPE_INCOME,
            'keterangan' => '',
            'jumlah' => 50000,
        ]);

        $response->assertRedirect('/transaksi');
        $this->assertDatabaseCount('transactions', 0);
    }
}
