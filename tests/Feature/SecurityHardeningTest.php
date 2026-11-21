<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recommendation;
use App\Models\Report;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_view_modify_or_delete_another_users_business_data(): void
    {
        $owner = User::factory()->create(['nama' => 'Pemilik A', 'nama_usaha' => 'Toko A']);
        $intruder = User::factory()->create(['nama' => 'Pemilik B', 'nama_usaha' => 'Toko B']);

        $productA = Product::factory()->for($owner)->create([
            'nama' => 'Kopi Rahasia A',
            'stok' => 20,
            'harga_beli' => 10000,
            'harga_jual' => 25000,
        ]);

        $stockLogA = StockLog::factory()->for($owner)->create([
            'product_id' => $productA->id,
            'jenis' => StockLog::TYPE_IN,
            'jumlah' => 5,
            'keterangan' => 'Mutasi Rahasia A',
        ]);

        $transactionA = Transaction::factory()->for($owner)->income($productA)->create([
            'keterangan' => 'Penjualan Rahasia A',
            'jumlah' => 750000,
        ]);

        $reportA = Report::factory()->for($owner)->create([
            'judul' => 'Laporan Rahasia A',
            'pembuat' => 'Pemilik A',
        ]);

        $recommendationA = Recommendation::factory()->for($owner)->create([
            'judul' => 'Catatan Rahasia A',
        ]);

        // Intruder tidak boleh melihat data Owner A di semua halaman
        $this->actingAs($intruder)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Penjualan Rahasia A')
            ->assertDontSee('Rp 750.000');

        $this->actingAs($intruder)->get('/produk')
            ->assertOk()
            ->assertDontSee('Kopi Rahasia A');

        $this->actingAs($intruder)->get('/stok')
            ->assertOk()
            ->assertDontSee('Kopi Rahasia A')
            ->assertDontSee('Mutasi Rahasia A');

        $this->actingAs($intruder)->get('/transaksi')
            ->assertOk()
            ->assertDontSee('Penjualan Rahasia A');

        $this->actingAs($intruder)->get('/laporan')
            ->assertOk()
            ->assertDontSee('Laporan Rahasia A')
            ->assertDontSee('Rp 750.000');

        $this->actingAs($intruder)->get('/rekomendasi')
            ->assertOk()
            ->assertDontSee('Catatan Rahasia A')
            ->assertDontSee('Kopi Rahasia A');

        $this->actingAs($intruder)->get('/konsultasi-ai')
            ->assertOk()
            ->assertDontSee('Kopi Rahasia A')
            ->assertDontSee('Rp 750.000');

        // Intruder tidak boleh ubah atau hapus Produk milik Owner A
        $this->actingAs($intruder)->post('/produk', [
            'action' => 'update',
            'id' => $productA->id,
            'nama' => 'Dibajak',
            'kategori' => 'Minuman',
            'satuan' => 'pack',
            'harga_beli' => 10000,
            'harga_jual' => 20000,
            'stok' => 1,
            'stok_min' => 1,
        ])->assertRedirect('/produk?edit='.$productA->id);
        $this->assertSame('Kopi Rahasia A', $productA->fresh()->nama);

        $this->actingAs($intruder)->post('/produk', [
            'action' => 'hapus',
            'id' => $productA->id,
        ])->assertRedirect('/produk');
        $this->assertModelExists($productA);

        // Intruder BOLEH membuat produk dengan nama yang sama seperti milik Owner A (composite unique per user)
        $this->actingAs($intruder)->post('/produk', [
            'action' => 'tambah',
            'nama' => 'Kopi Rahasia A',
            'kategori' => 'Minuman',
            'satuan' => 'pack',
            'harga_beli' => 15000,
            'harga_jual' => 30000,
            'stok' => 10,
            'stok_min' => 2,
        ])->assertRedirect('/produk');
        $this->assertDatabaseHas('products', [
            'user_id' => $intruder->id,
            'nama' => 'Kopi Rahasia A',
        ]);

        // Intruder tidak boleh mencatat, mengubah, atau menghapus mutasi stok milik Owner A
        $this->actingAs($intruder)->post('/stok', [
            'action' => 'catat',
            'produk_id' => $productA->id,
            'jenis' => StockLog::TYPE_OUT,
            'jumlah' => 5,
            'tanggal' => '2026-11-10',
            'keterangan' => 'Coba kurangi stok orang',
        ])->assertRedirect('/stok');
        $this->assertSame(20, $productA->fresh()->stok);

        $this->actingAs($intruder)->post('/stok', [
            'action' => 'hapus_log',
            'id' => $stockLogA->id,
        ])->assertRedirect('/stok');
        $this->assertModelExists($stockLogA);
        $this->assertSame(20, $productA->fresh()->stok);

        // Intruder tidak boleh mengaitkan transaksi ke produk Owner A, maupun ubah/hapus transaksi Owner A
        $this->actingAs($intruder)->post('/transaksi', [
            'action' => 'tambah',
            'tanggal' => '2026-11-10',
            'jenis' => Transaction::TYPE_INCOME,
            'keterangan' => 'Coba kaitkan produk A',
            'jumlah' => 50000,
            'produk_id' => $productA->id,
        ])->assertRedirect('/transaksi');
        $this->assertDatabaseMissing('transactions', ['keterangan' => 'Coba kaitkan produk A']);

        $this->actingAs($intruder)->post('/transaksi', [
            'action' => 'update',
            'id' => $transactionA->id,
            'tanggal' => '2026-11-10',
            'jenis' => Transaction::TYPE_EXPENSE,
            'keterangan' => 'Dibajak',
            'jumlah' => 1000,
        ])->assertRedirect('/transaksi');
        $this->assertSame('Penjualan Rahasia A', $transactionA->fresh()->keterangan);

        $this->actingAs($intruder)->post('/transaksi', [
            'action' => 'hapus',
            'id' => $transactionA->id,
        ])->assertRedirect('/transaksi');
        $this->assertModelExists($transactionA);

        // Intruder tidak boleh ubah atau hapus Laporan dan Rekomendasi milik Owner A
        $this->actingAs($intruder)->post('/laporan', [
            'action' => 'update',
            'id' => $reportA->id,
            'judul' => 'Dibajak',
            'kategori' => 'Evaluasi Bulanan',
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
            'status' => 'Draft',
        ])->assertRedirect('/laporan');
        $this->assertSame('Laporan Rahasia A', $reportA->fresh()->judul);

        $this->actingAs($intruder)->post('/laporan', [
            'action' => 'hapus',
            'id' => $reportA->id,
        ])->assertRedirect('/laporan');
        $this->assertModelExists($reportA);

        $this->actingAs($intruder)->post('/rekomendasi', [
            'action' => 'hapus',
            'id' => $recommendationA->id,
        ])->assertRedirect('/rekomendasi');
        $this->assertModelExists($recommendationA);
    }

    public function test_security_headers_are_present_on_responses(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeaderMissing('X-Powered-By');
    }

    public function test_registration_rejects_weak_passwords(): void
    {
        $shortPassword = $this->post('/login', [
            'action' => 'register',
            'nama' => 'Siti',
            'username' => 'siti_toko',
            'email' => 'siti@example.com',
            'password' => 'abc123',
            'password2' => 'abc123',
        ]);

        $shortPassword->assertRedirect('/login?tab=register');
        $shortPassword->assertSessionHas('error', 'Kata sandi minimal 8 karakter.');
        $this->assertDatabaseMissing('users', ['username' => 'siti_toko']);

        $lettersOnly = $this->post('/login', [
            'action' => 'register',
            'nama' => 'Siti',
            'username' => 'siti_toko',
            'email' => 'siti@example.com',
            'password' => 'abcdefgh',
            'password2' => 'abcdefgh',
        ]);

        $lettersOnly->assertRedirect('/login?tab=register');
        $lettersOnly->assertSessionHas('error', 'Kata sandi harus mengandung kombinasi huruf dan angka.');
        $this->assertDatabaseMissing('users', ['username' => 'siti_toko']);
    }

    public function test_login_endpoint_is_rate_limited_after_repeated_attempts(): void
    {
        RateLimiter::clear('auth-account:brute_target|127.0.0.1');
        RateLimiter::clear('auth-ip:127.0.0.1');

        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', [
                'action' => 'login',
                'username' => 'brute_target',
                'password' => 'salah123',
            ])->assertRedirect('/login');
        }

        $throttled = $this->post('/login', [
            'action' => 'login',
            'username' => 'brute_target',
            'password' => 'salah123',
        ]);

        $throttled->assertRedirect('/login');
        $throttled->assertSessionHas('error', 'Terlalu banyak percobaan. Silakan tunggu 1 menit sebelum mencoba lagi.');
    }

    public function test_ai_consultation_send_endpoint_is_rate_limited(): void
    {
        Http::preventStrayRequests();
        config([
            'services.gemini.key' => 'test-rate-limit-key',
            'services.gemini.model' => 'gemini-flash-lite-latest',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'role' => 'model',
                            'parts' => [['text' => 'Oke siap!']],
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        for ($i = 0; $i < 12; $i++) {
            $this->actingAs($user)->postJson('/konsultasi-ai/kirim', [
                'pesan' => 'Tes pertanyaan '.$i,
            ])->assertOk();
        }

        $throttled = $this->actingAs($user)->postJson('/konsultasi-ai/kirim', [
            'pesan' => 'Pertanyaan ke-13',
        ]);

        $throttled->assertStatus(429);
        $throttled->assertJsonPath('ok', false);
    }

    public function test_transaction_update_rejects_zero_or_excessive_amount(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->income()->create([
            'keterangan' => 'Penjualan Valid',
            'jumlah' => 100000,
        ]);

        $zeroResponse = $this->actingAs($user)->post('/transaksi', [
            'action' => 'update',
            'id' => $transaction->id,
            'tanggal' => '2026-11-10',
            'jenis' => Transaction::TYPE_INCOME,
            'keterangan' => 'Penjualan Valid',
            'jumlah' => 0,
        ]);

        $zeroResponse->assertRedirect('/transaksi?edit='.$transaction->id);
        $zeroResponse->assertSessionHas('flash.type', 'danger');
        $this->assertSame(100000, $transaction->fresh()->jumlah);

        $excessiveResponse = $this->actingAs($user)->post('/transaksi', [
            'action' => 'update',
            'id' => $transaction->id,
            'tanggal' => '2026-11-10',
            'jenis' => Transaction::TYPE_INCOME,
            'keterangan' => 'Penjualan Valid',
            'jumlah' => 99_999_999_999,
        ]);

        $excessiveResponse->assertRedirect('/transaksi?edit='.$transaction->id);
        $excessiveResponse->assertSessionHas('flash.type', 'danger');
        $this->assertSame(100000, $transaction->fresh()->jumlah);
    }

    public function test_dashboard_executes_consolidated_queries_only(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for($user)->create();
        Transaction::factory()->for($user)->income($product)->count(5)->create();

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->actingAs($user)->get('/dashboard')->assertOk();

        // Hanya 3 query: transactions + products + debts (turun dari ~10 query)
        $this->assertCount(3, $queries);
    }
}
