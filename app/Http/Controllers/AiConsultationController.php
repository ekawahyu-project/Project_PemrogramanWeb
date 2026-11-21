<?php

namespace App\Http\Controllers;

use App\Models\AiConsultation;
use App\Models\User;
use App\Services\GeminiBusinessAdvisorService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class AiConsultationController extends Controller
{
    public function __construct(public GeminiBusinessAdvisorService $advisor) {}

    /**
     * Tampilkan halaman Konsultasi AI Bisnis berbasis Gemini.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $currentPage = 'konsultasi_ai';
        $messages = $user->aiConsultations()
            ->orderBy('id')
            ->limit(60)
            ->get();

        $snapshot = $this->advisor->buildBusinessSnapshot($user);
        $isConfigured = $this->advisor->isConfigured();
        $modelName = $this->advisor->modelName();

        $quickPrompts = [
            [
                'label' => 'Evaluasi Keuangan',
                'prompt' => 'Analisis kondisi arus kas, laba bersih, dan net profit margin toko saya saat ini. Apa 3 langkah paling prioritas untuk meningkatkan keuntungan?',
            ],
            [
                'label' => 'Prioritas Restock',
                'prompt' => 'Berdasarkan data stok menipis dan produk terlaris toko saya, produk mana yang paling mendesak di-restock dan bagaimana strategi mengatur modal belanjanya?',
            ],
            [
                'label' => 'Strategi Promo & Harga',
                'prompt' => 'Buatkan ide promo atau bundling untuk produk dengan margin tinggi dan produk yang belum terjual di katalog saya.',
            ],
            [
                'label' => 'Kelola Kasbon & Hutang',
                'prompt' => 'Lihat data hutang dan piutang (kasbon) saya sekarang. Bagaimana cara sopan menagih piutang jatuh tempo dan menjaga arus kas tetap aman?',
            ],
        ];

        return view('konsultasi_ai', compact(
            'currentPage',
            'messages',
            'snapshot',
            'isConfigured',
            'modelName',
            'quickPrompts'
        ));
    }

    /**
     * Kirim pesan konsultasi ke Gemini API dan simpan riwayat percakapan.
     */
    public function send(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'pesan' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'pesan.required' => 'Tuliskan pertanyaan bisnis Anda terlebih dahulu.',
            'pesan.min' => 'Pertanyaan minimal 2 karakter.',
            'pesan.max' => 'Pertanyaan maksimal 2000 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $prompt = trim((string) $validator->validated()['pesan']);

        $recentHistory = $user->aiConsultations()
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        try {
            $reply = $this->advisor->ask($user, $prompt, $recentHistory);
        } catch (DomainException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        [$userMessage, $aiMessage] = DB::transaction(function () use ($user, $prompt, $reply): array {
            $userMsg = $user->aiConsultations()->create([
                'role' => AiConsultation::ROLE_USER,
                'content' => $prompt,
            ]);

            $modelMsg = $user->aiConsultations()->create([
                'role' => AiConsultation::ROLE_MODEL,
                'content' => $reply,
            ]);

            return [$userMsg, $modelMsg];
        });

        return response()->json([
            'ok' => true,
            'user_message' => [
                'id' => $userMessage->id,
                'role' => $userMessage->role,
                'content' => $userMessage->content,
                'time' => $userMessage->created_at?->format('H:i') ?? now()->format('H:i'),
            ],
            'ai_message' => [
                'id' => $aiMessage->id,
                'role' => $aiMessage->role,
                'content' => $aiMessage->content,
                'time' => $aiMessage->created_at?->format('H:i') ?? now()->format('H:i'),
            ],
        ]);
    }

    /**
     * Hapus seluruh riwayat percakapan konsultasi AI milik pengguna aktif.
     */
    public function reset(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->aiConsultations()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Riwayat konsultasi berhasil dibersihkan.',
            ]);
        }

        return redirect('/konsultasi-ai');
    }
}
