<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction() || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('auth', function (Request $request): array {
            $identifier = mb_strtolower(trim((string) $request->input('username', '')));
            $ip = (string) $request->ip();

            $tooManyResponse = function (Request $request, array $headers): JsonResponse|RedirectResponse {
                $message = 'Terlalu banyak percobaan. Silakan tunggu 1 menit sebelum mencoba lagi.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => $message,
                    ], 429, $headers);
                }

                $isRegister = $request->input('action') === 'register';

                return redirect('/login'.($isRegister ? '?tab=register' : ''))
                    ->withInput($request->except('password', 'password2'))
                    ->with('error', $message)
                    ->with('tab', $isRegister ? 'register' : 'login')
                    ->withHeaders($headers);
            };

            return [
                Limit::perMinute(10)->by("auth-account:{$identifier}|{$ip}")->response($tooManyResponse),
                Limit::perMinute(20)->by("auth-ip:{$ip}")->response($tooManyResponse),
            ];
        });

        RateLimiter::for('ai-consultation', function (Request $request): Limit {
            $key = 'ai-user:'.($request->user()?->id ?? $request->ip());

            return Limit::perMinute(12)->by($key)->response(function (Request $request, array $headers): JsonResponse {
                return response()->json([
                    'ok' => false,
                    'message' => 'Terlalu banyak permintaan konsultasi AI dalam 1 menit. Silakan tunggu sebentar lalu coba lagi.',
                ], 429, $headers);
            });
        });

        View::share(
            'inputClass',
            'w-full px-4 py-3 bg-[#181F11] border border-white/12 rounded-xl text-sm text-[#F4F7F0] placeholder:text-[#8E9882]/65 outline-none focus:border-[#C4FF0D] focus:ring-2 focus:ring-[#C4FF0D]/15 transition'
        );

        View::composer('includes.sidebar', function (BladeView $view): void {
            $view->with('sidebarNav', [
                ['href' => '/dashboard', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['href' => '/transaksi', 'label' => 'Transaksi', 'page' => 'transaksi'],
                ['href' => '/produk', 'label' => 'Produk', 'page' => 'produk'],
                ['href' => '/stok', 'label' => 'Stok', 'page' => 'stok'],
                ['href' => '/hutang-piutang', 'label' => 'Hutang & Piutang', 'page' => 'hutang_piutang'],
                ['href' => '/laporan', 'label' => 'Laporan', 'page' => 'laporan'],
                ['href' => '/konsultasi-ai', 'label' => 'Konsultasi AI', 'page' => 'konsultasi_ai'],
                ['href' => '/profil', 'label' => 'Profil', 'page' => 'profil'],
            ]);

            $view->with('sidebarIcons', [
                'dashboard' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="2"/></svg>',
                'transaksi' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
                'produk' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                'stok' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
                'hutang_piutang' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                'laporan' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                'konsultasi_ai' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>',
                'rekomendasi' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
                'profil' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'logout' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
            ]);
        });
    }
}
