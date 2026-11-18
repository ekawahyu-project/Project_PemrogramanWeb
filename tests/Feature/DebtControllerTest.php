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
}
