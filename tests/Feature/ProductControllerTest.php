<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_and_delete_product(): void
    {
        $user = User::factory()->create();

        $createResponse = $this->actingAs($user)->post('/produk', [
            'action' => 'tambah',
            'nama' => 'Kopi Gayo',
            'kategori' => 'Minuman',
            'satuan' => 'pack',
            'harga_beli' => 20000,
            'harga_jual' => 35000,
            'stok' => 15,
            'stok_min' => 5,
        ]);

        $createResponse->assertRedirect('/produk');
        $product = Product::where('nama', 'Kopi Gayo')->first();
        $this->assertNotNull($product);
        $this->assertSame(35000, $product->harga_jual);

        $updateResponse = $this->actingAs($user)->post('/produk', [
            'action' => 'update',
            'id' => $product->id,
            'nama' => 'Kopi Gayo Premium',
            'kategori' => 'Minuman',
            'satuan' => 'pack',
            'harga_beli' => 25000,
            'harga_jual' => 45000,
            'stok' => 20,
            'stok_min' => 5,
        ]);

        $updateResponse->assertRedirect('/produk');
        $product->refresh();
        $this->assertSame('Kopi Gayo Premium', $product->nama);
        $this->assertSame(20, $product->stok);

        $trx = Transaction::factory()->income($product)->create();

        $deleteResponse = $this->actingAs($user)->post('/produk', [
            'action' => 'hapus',
            'id' => $product->id,
        ]);

        $deleteResponse->assertRedirect('/produk');
        $this->assertModelMissing($product);
        $trx->refresh();
        $this->assertNull($trx->product_id);
    }

    public function test_product_validation_rejects_selling_price_below_buying_price(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/produk', [
            'action' => 'tambah',
            'nama' => 'Produk Rugi',
            'kategori' => 'Makanan',
            'satuan' => 'pcs',
            'harga_beli' => 50000,
            'harga_jual' => 30000,
            'stok' => 5,
            'stok_min' => 1,
        ]);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('errors_list');
        $this->assertDatabaseMissing('products', ['nama' => 'Produk Rugi']);
    }
}
