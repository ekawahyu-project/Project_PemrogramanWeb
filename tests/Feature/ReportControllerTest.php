<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_and_crud_work_with_eloquent(): void
    {
        $user = User::factory()->create(['nama' => 'Alpet']);
        Transaction::factory()->income()->create(['tanggal' => '2026-08-01', 'jumlah' => 300000]);
        Transaction::factory()->expense()->create(['tanggal' => '2026-08-05', 'jumlah' => 100000]);

        $indexResponse = $this->actingAs($user)->get('/laporan');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Rp 300.000');
        $indexResponse->assertSee('Rp 100.000');
        $indexResponse->assertSee('Rp 200.000');

        $storeResponse = $this->actingAs($user)->post('/laporan', [
            'action' => 'simpan',
            'judul' => 'Laporan Oktober 2026',
            'kategori' => 'Evaluasi Bulanan',
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
            'status' => 'Selesai',
            'catatan' => 'Stabil',
        ]);

        $storeResponse->assertRedirect('/laporan');
        $report = Report::where('judul', 'Laporan Oktober 2026')->first();
        $this->assertNotNull($report);
        $this->assertSame($user->id, $report->user_id);

        $updateResponse = $this->actingAs($user)->post('/laporan', [
            'action' => 'update',
            'id' => $report->id,
            'judul' => 'Laporan Oktober Revisi',
            'kategori' => 'Keuangan & Kas',
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
            'status' => 'Draft',
            'catatan' => 'Revisi catatan',
        ]);

        $updateResponse->assertRedirect('/laporan');
        $report->refresh();
        $this->assertSame('Laporan Oktober Revisi', $report->judul);

        $deleteResponse = $this->actingAs($user)->post('/laporan', [
            'action' => 'hapus',
            'id' => $report->id,
        ]);

        $deleteResponse->assertRedirect('/laporan');
        $this->assertModelMissing($report);
    }
}
