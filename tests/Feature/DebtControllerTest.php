<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_debt_page_displays_analytics_and_supports_full_crud_and_settlement(): void
    {
        $user = User::factory()->create();

        // 1. CREATE (Tambah Kasbon / Piutang Pelanggan)
        $storeResponse = $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'tambah',
            'nama_pihak' => 'Warung Kopi Senja',
            'no_hp' => '08123456789',
            'jenis' => Debt::TYPE_RECEIVABLE,
            'jumlah' => 500000,
            'dibayar' => 200000,
            'tanggal' => '2026-10-01',
            'jatuh_tempo' => '2026-10-15',
            'keterangan' => 'Kasbon 10 pack Kopi',
        ]);

        $storeResponse->assertRedirect('/hutang-piutang');
        $debt = Debt::where('nama_pihak', 'Warung Kopi Senja')->first();
        $this->assertNotNull($debt);
        $this->assertSame($user->id, $debt->user_id);
        $this->assertSame(Debt::STATUS_PARTIAL, $debt->status);
        $this->assertSame(300000, $debt->remainingAmount());

        // 2. READ (Tampilkan halaman Hutang & Piutang)
        $indexResponse = $this->actingAs($user)->get('/hutang-piutang');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Warung Kopi Senja');
        $indexResponse->assertSee('Rp 300.000');

        // 3. UPDATE (Update cicilan & keterangan)
        $updateResponse = $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'update',
            'id' => $debt->id,
            'nama_pihak' => 'Warung Kopi Senja Updated',
            'no_hp' => '08123456789',
            'jenis' => Debt::TYPE_RECEIVABLE,
            'jumlah' => 500000,
            'dibayar' => 350000,
            'tanggal' => '2026-10-01',
            'jatuh_tempo' => '2026-10-20',
            'keterangan' => 'Cicilan kedua masuk',
        ]);

        $updateResponse->assertRedirect('/hutang-piutang');
        $debt->refresh();
        $this->assertSame('Warung Kopi Senja Updated', $debt->nama_pihak);
        $this->assertSame(350000, $debt->dibayar);
        $this->assertSame(Debt::STATUS_PARTIAL, $debt->status);

        // 4. LUNASI (Tandai Lunas 1 Klik)
        $payResponse = $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'lunasi',
            'id' => $debt->id,
        ]);

        $payResponse->assertRedirect('/hutang-piutang');
        $debt->refresh();
        $this->assertSame(500000, $debt->dibayar);
        $this->assertSame(Debt::STATUS_PAID, $debt->status);

        // 5. DELETE (Hapus catatan)
        $deleteResponse = $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'hapus',
            'id' => $debt->id,
        ]);

        $deleteResponse->assertRedirect('/hutang-piutang');
        $this->assertModelMissing($debt);
    }

    public function test_debt_validation_rejects_invalid_and_unreasonable_inputs_with_clear_messages(): void
    {
        $user = User::factory()->create();

        // Nilai tidak wajar: dibayar > jumlah
        $overpaid = $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'tambah',
            'nama_pihak' => 'Toko Maju',
            'no_hp' => '08123456789',
            'jenis' => Debt::TYPE_RECEIVABLE,
            'jumlah' => 100000,
            'dibayar' => 250000,
            'tanggal' => '2026-10-01',
        ]);

        $overpaid->assertSessionHas('flash.type', 'danger');
        $overpaid->assertSessionHas('flash.msg', 'Nominal yang sudah dibayar tidak boleh melebihi total nominal.');
        $this->assertDatabaseCount('debts', 0);

        // Format nomor HP salah (berisi huruf)
        $badPhone = $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'tambah',
            'nama_pihak' => 'Toko Maju',
            'no_hp' => 'abc- salah',
            'jenis' => Debt::TYPE_RECEIVABLE,
            'jumlah' => 100000,
            'dibayar' => 0,
            'tanggal' => '2026-10-01',
        ]);

        $badPhone->assertSessionHas('flash.msg', 'Format nomor HP tidak valid. Gunakan angka saja, contoh: 081234567890.');
        $this->assertDatabaseCount('debts', 0);
    }

    public function test_debt_security_prevents_unauthenticated_access_sql_injection_and_xss(): void
    {
        // Guest tidak bisa akses lewat URL langsung
        $this->get('/hutang-piutang')->assertRedirect('/login');

        $user = User::factory()->create();

        // Input berisi XSS & SQL Injection disimpan aman via prepared statement dan ditampilkan ter-escape
        $this->actingAs($user)->post('/hutang-piutang', [
            'action' => 'tambah',
            'nama_pihak' => "<script>alert('xss')</script>",
            'no_hp' => '08123456789',
            'jenis' => Debt::TYPE_PAYABLE,
            'jumlah' => 150000,
            'dibayar' => 0,
            'tanggal' => '2026-10-01',
            'keterangan' => "'; DROP TABLE debts; --",
        ]);

        $page = $this->actingAs($user)->get('/hutang-piutang');
        $page->assertOk();
        $page->assertSee('&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;', false);
        $page->assertDontSee("<script>alert('xss')</script>", false);
    }
}
