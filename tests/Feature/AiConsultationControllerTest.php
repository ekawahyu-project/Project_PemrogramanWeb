<?php

namespace Tests\Feature;

use App\Models\AiConsultation;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiConsultationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_ai_consultation(): void
    {
        $this->get('/konsultasi-ai')->assertRedirect('/login');
        $this->post('/konsultasi-ai/kirim', ['pesan' => 'Halo'])->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_ai_consultation_page_with_live_business_context(): void
    {
        $user = User::factory()->create([
            'nama' => 'Budi UMKM',
            'nama_usaha' => 'Toko Berkah',
        ]);

        Product::factory()->for($user)->lowStock()->create(['nama' => 'Gula Pasir 1kg', 'stok' => 2, 'stok_min' => 5]);
        Transaction::factory()->for($user)->income()->create(['jumlah' => 450000]);
        Debt::factory()->for($user)->create([
            'nama_pihak' => 'Warung Melati',
            'jenis' => Debt::TYPE_RECEIVABLE,
            'jumlah' => 200000,
            'dibayar' => 50000,
            'status' => Debt::STATUS_PARTIAL,
        ]);

        $response = $this->actingAs($user)->get('/konsultasi-ai');

        $response->assertOk();
        $response->assertSee('Konsultasi AI Bisnis');
        $response->assertSee('Toko Berkah');
        $response->assertSee('Gula Pasir 1kg');
        $response->assertSee('Warung Melati');
    }

    public function test_user_can_send_question_to_gemini_with_store_context_and_persist_history(): void
    {
        Http::preventStrayRequests();

        config([
            'services.gemini.key' => 'test-gemini-key-123',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'role' => 'model',
                            'parts' => [
                                ['text' => '**Saran Restock:** Segera tambah stok Kopi Arabika 250g.'],
                            ],
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['nama_usaha' => 'Kedai Nusantara']);
        Product::factory()->for($user)->lowStock()->create(['nama' => 'Kopi Arabika 250g', 'stok' => 2, 'stok_min' => 10]);

        $response = $this->actingAs($user)->postJson('/konsultasi-ai/kirim', [
            'pesan' => 'Produk mana yang harus saya restock dulu?',
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('user_message.role', AiConsultation::ROLE_USER);
        $response->assertJsonPath('ai_message.role', AiConsultation::ROLE_MODEL);
        $response->assertJsonPath('ai_message.content', '**Saran Restock:** Segera tambah stok Kopi Arabika 250g.');

        Http::assertSent(function (HttpClientRequest $request): bool {
            $body = $request->data();
            $systemText = (string) ($body['system_instruction']['parts'][0]['text'] ?? '');

            return $request->hasHeader('x-goog-api-key', 'test-gemini-key-123')
                && str_contains($systemText, 'Kedai Nusantara')
                && str_contains($systemText, 'Kopi Arabika 250g');
        });

        $this->assertDatabaseCount('ai_consultations', 2);
        $this->assertDatabaseHas('ai_consultations', [
            'user_id' => $user->id,
            'role' => AiConsultation::ROLE_USER,
            'content' => 'Produk mana yang harus saya restock dulu?',
        ]);
    }

    public function test_returns_clear_error_when_gemini_api_key_is_missing_or_upstream_fails(): void
    {
        Http::preventStrayRequests();

        config(['services.gemini.key' => '']);
        $user = User::factory()->create();

        $missingKey = $this->actingAs($user)->postJson('/konsultasi-ai/kirim', [
            'pesan' => 'Halo AI, bantu analisa toko saya',
        ]);

        $missingKey->assertStatus(422);
        $missingKey->assertJsonPath('ok', false);
        $this->assertDatabaseCount('ai_consultations', 0);

        config(['services.gemini.key' => 'bad-key']);
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'API key not valid'],
            ], 403),
        ]);

        $invalidKey = $this->actingAs($user)->postJson('/konsultasi-ai/kirim', [
            'pesan' => 'Halo AI, bantu analisa toko saya',
        ]);

        $invalidKey->assertStatus(422);
        $invalidKey->assertJsonPath('ok', false);
        $this->assertDatabaseCount('ai_consultations', 0);
    }

    public function test_user_can_reset_only_their_own_ai_consultation_history(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        AiConsultation::factory()->for($userA)->create(['content' => 'Chat A']);
        AiConsultation::factory()->for($userB)->create(['content' => 'Chat B']);

        $response = $this->actingAs($userA)->postJson('/konsultasi-ai/reset');

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $this->assertDatabaseMissing('ai_consultations', ['user_id' => $userA->id]);
        $this->assertDatabaseHas('ai_consultations', ['user_id' => $userB->id, 'content' => 'Chat B']);
    }

    public function test_service_auto_discovers_available_flash_model_when_configured_model_returns_404(): void
    {
        Http::preventStrayRequests();

        config([
            'services.gemini.key' => 'test-key-404',
            'services.gemini.model' => 'gemini-old-model',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-old-model:generateContent' => Http::response([
                'error' => ['message' => 'Model not found'],
            ], 404),
            'https://generativelanguage.googleapis.com/v1beta/models' => Http::response([
                'models' => [
                    [
                        'name' => 'models/gemini-flash-latest',
                        'supportedGenerationMethods' => ['generateContent'],
                    ],
                ],
            ], 200),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'role' => 'model',
                            'parts' => [
                                ['text' => 'internal thought', 'thought' => true],
                                ['text' => 'Jawaban bisnis dari model aktif.'],
                            ],
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/konsultasi-ai/kirim', [
            'pesan' => 'Bagaimana cara meningkatkan omzet?',
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('ai_message.content', 'Jawaban bisnis dari model aktif.');
    }
}
