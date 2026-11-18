<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recommendation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendation_displays_analytics_and_supports_crud(): void
    {
        $user = User::factory()->create();
        $bestSeller = Product::factory()->create(['nama' => 'Kopi Arabika']);
        $unsoldLow = Product::factory()->lowStock()->create(['nama' => 'Teh Hijau']);
        Transaction::factory()->income($bestSeller)->create(['jumlah' => 110000]);

        $indexResponse = $this->actingAs($user)->get('/rekomendasi');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Kopi Arabika');
        $indexResponse->assertSee('Teh Hijau');

        $storeResponse = $this->actingAs($user)->post('/rekomendasi', [
            'action' => 'tambah',
            'judul' => 'Promo Akhir Bulan',
            'isi' => 'Buat paket bundling Teh Hijau.',
            'prioritas' => 'Tinggi',
        ]);

        $storeResponse->assertRedirect('/rekomendasi');
        $note = Recommendation::where('judul', 'Promo Akhir Bulan')->first();
        $this->assertNotNull($note);

        $updateResponse = $this->actingAs($user)->post('/rekomendasi', [
            'action' => 'update',
            'id' => $note->id,
            'judul' => 'Promo Akhir Bulan Updated',
            'isi' => 'Diskon 10%',
            'prioritas' => 'Sedang',
        ]);

        $updateResponse->assertRedirect('/rekomendasi');
        $note->refresh();
        $this->assertSame('Promo Akhir Bulan Updated', $note->judul);

        $deleteResponse = $this->actingAs($user)->post('/rekomendasi', [
            'action' => 'hapus',
            'id' => $note->id,
        ]);

        $deleteResponse->assertRedirect('/rekomendasi');
        $this->assertModelMissing($note);
    }
}
