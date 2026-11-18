<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_database_aggregates_and_recent_transactions(): void
    {
        $user = User::factory()->create(['nama' => 'Alpet Alousius']);
        Product::factory()->lowStock()->create(['nama' => 'Teh Hijau']);
        Transaction::factory()->income()->create([
            'keterangan' => 'Penjualan Kopi',
            'jumlah' => 250000,
            'tanggal' => '2026-10-10',
        ]);
        Transaction::factory()->expense()->create([
            'keterangan' => 'Beli Kemasan',
            'jumlah' => 100000,
            'tanggal' => '2026-10-11',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Alpet Alousius');
        $response->assertSee('Rp 250.000');
        $response->assertSee('Rp 100.000');
        $response->assertSee('Rp 150.000');
        $response->assertSee('Penjualan Kopi');
        $response->assertSee('Beli Kemasan');
    }
}
