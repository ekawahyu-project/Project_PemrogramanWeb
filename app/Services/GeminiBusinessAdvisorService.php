<?php

namespace App\Services;

use App\Models\AiConsultation;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use DomainException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiBusinessAdvisorService
{
    private const MODELS_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models';

    private const ENDPOINT_TEMPLATE = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function isConfigured(): bool
    {
        return trim((string) config('services.gemini.key', '')) !== '';
    }

    public function modelName(): string
    {
        $model = trim((string) config('services.gemini.model', 'gemini-flash-lite-latest'));

        return $model !== '' ? $model : 'gemini-flash-lite-latest';
    }

    /**
     * Kumpulkan ringkasan data operasional toko untuk ditampilkan di UI dan dijadikan konteks AI.
     *
     * @return array{
     *     profil: array{nama_pemilik: string, nama_usaha: string, kategori_usaha: string, alamat_usaha: string},
     *     keuangan: array{
     *         pemasukan: int,
     *         pengeluaran: int,
     *         laba_bersih: int,
     *         margin_pct: float,
     *         jumlah_transaksi: int,
     *         tren_bulanan: array<string, array{pemasukan: int, pengeluaran: int}>,
     *         transaksi_terbaru: array<int, array{tanggal: string, jenis: string, keterangan: string, jumlah: int, produk: ?string}>
     *     },
     *     inventaris: array{
     *         total_sku: int,
     *         total_unit: int,
     *         modal_stok: int,
     *         nilai_jual_stok: int,
     *         potensi_laba_stok: int,
     *         stok_kritis_count: int,
     *         produk_kritis: array<int, array{nama: string, stok: int, stok_min: int, satuan: string}>,
     *         produk_terlaris: array<int, array{nama: string, frekuensi: int, omzet: int}>,
     *         produk_belum_terjual: array<int, string>,
     *         daftar_produk: array<int, array{nama: string, kategori: string, harga_beli: int, harga_jual: int, margin_pct: float, stok: int, stok_min: int, satuan: string}>
     *     },
     *     kasbon: array{
     *         total_piutang: int,
     *         sisa_piutang: int,
     *         kolektibilitas_pct: float,
     *         sisa_hutang: int,
     *         tagihan_aktif: int,
     *         jatuh_tempo: array<int, array{nama_pihak: string, jenis: string, sisa: int, jatuh_tempo: string, overdue: bool}>
     *     }
     * }
     */
    public function buildBusinessSnapshot(User $user): array
    {
        $transactions = $user->transactions()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $products = $user->products()->orderBy('id')->get();
        $productsById = $products->keyBy('id');

        $pemasukan = (int) $transactions->where('jenis', Transaction::TYPE_INCOME)->sum('jumlah');
        $pengeluaran = (int) $transactions->where('jenis', Transaction::TYPE_EXPENSE)->sum('jumlah');
        $labaBersih = $pemasukan - $pengeluaran;
        $marginPct = $pemasukan > 0 ? round(($labaBersih / $pemasukan) * 100, 1) : 0.0;

        $trenBulanan = [];
        foreach ($transactions->sortBy('tanggal') as $trx) {
            $bulan = $trx->tanggal->format('Y-m');
            if (! isset($trenBulanan[$bulan])) {
                $trenBulanan[$bulan] = ['pemasukan' => 0, 'pengeluaran' => 0];
            }

            if ($trx->isIncome()) {
                $trenBulanan[$bulan]['pemasukan'] += (int) $trx->jumlah;
            } else {
                $trenBulanan[$bulan]['pengeluaran'] += (int) $trx->jumlah;
            }
        }
        $trenBulanan = array_slice($trenBulanan, -4, 4, true);

        $transaksiTerbaru = $transactions->take(5)->map(fn (Transaction $t): array => [
            'tanggal' => $t->tanggal->format('Y-m-d'),
            'jenis' => $t->jenis,
            'keterangan' => $t->keterangan,
            'jumlah' => (int) $t->jumlah,
            'produk' => $t->product_id !== null ? $productsById->get($t->product_id)?->nama : null,
        ])->values()->all();

        $totalSku = $products->count();
        $totalUnit = (int) $products->sum('stok');
        $modalStok = (int) $products->sum(fn (Product $p): int => $p->harga_beli * $p->stok);
        $nilaiJualStok = (int) $products->sum(fn (Product $p): int => $p->harga_jual * $p->stok);
        $potensiLabaStok = max(0, $nilaiJualStok - $modalStok);

        $produkKritisCollection = $products->filter(fn (Product $p): bool => $p->isLowStock());
        $produkKritis = $produkKritisCollection->take(8)->map(fn (Product $p): array => [
            'nama' => $p->nama,
            'stok' => (int) $p->stok,
            'stok_min' => (int) $p->stok_min,
            'satuan' => $p->satuan,
        ])->values()->all();

        $salesByProduct = [];
        foreach ($transactions as $trx) {
            $matchedProduct = $trx->product_id !== null ? $productsById->get($trx->product_id) : null;
            if (! $trx->isIncome() || $matchedProduct === null) {
                continue;
            }

            $pid = (int) $trx->product_id;
            if (! isset($salesByProduct[$pid])) {
                $salesByProduct[$pid] = [
                    'nama' => $matchedProduct->nama,
                    'frekuensi' => 0,
                    'omzet' => 0,
                ];
            }

            $salesByProduct[$pid]['frekuensi']++;
            $salesByProduct[$pid]['omzet'] += (int) $trx->jumlah;
        }

        usort($salesByProduct, fn (array $a, array $b): int => [$b['frekuensi'], $b['omzet']] <=> [$a['frekuensi'], $a['omzet']]);
        $produkTerlaris = array_slice($salesByProduct, 0, 5);

        $soldProductIds = $transactions
            ->where('jenis', Transaction::TYPE_INCOME)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->all();

        $produkBelumTerjual = $products
            ->filter(fn (Product $p): bool => ! in_array($p->id, $soldProductIds, true))
            ->take(5)
            ->pluck('nama')
            ->values()
            ->all();

        $daftarProduk = $products->take(20)->map(fn (Product $p): array => [
            'nama' => $p->nama,
            'kategori' => $p->kategori,
            'harga_beli' => (int) $p->harga_beli,
            'harga_jual' => (int) $p->harga_jual,
            'margin_pct' => $p->harga_jual > 0 ? round((($p->harga_jual - $p->harga_beli) / $p->harga_jual) * 100, 1) : 0.0,
            'stok' => (int) $p->stok,
            'stok_min' => (int) $p->stok_min,
            'satuan' => $p->satuan,
        ])->values()->all();

        $debts = $user->debts()->orderBy('jatuh_tempo')->get();
        $piutangList = $debts->where('jenis', Debt::TYPE_RECEIVABLE);
        $hutangList = $debts->where('jenis', Debt::TYPE_PAYABLE);

        $totalPiutang = (int) $piutangList->sum('jumlah');
        $piutangDibayar = (int) $piutangList->sum('dibayar');
        $sisaPiutang = max(0, $totalPiutang - $piutangDibayar);
        $kolektibilitasPct = $totalPiutang > 0 ? round(($piutangDibayar / $totalPiutang) * 100, 1) : 100.0;
        $sisaHutang = (int) $hutangList->sum(fn (Debt $d): int => $d->remainingAmount());
        $tagihanAktif = $debts->where('status', '!=', Debt::STATUS_PAID)->count();

        $jatuhTempo = $debts
            ->filter(fn (Debt $d): bool => ! $d->isPaid() && $d->jatuh_tempo !== null)
            ->take(5)
            ->map(fn (Debt $d): array => [
                'nama_pihak' => $d->nama_pihak,
                'jenis' => $d->jenis,
                'sisa' => $d->remainingAmount(),
                'jatuh_tempo' => $d->jatuh_tempo->format('Y-m-d'),
                'overdue' => $d->isOverdue(),
            ])
            ->values()
            ->all();

        return [
            'profil' => [
                'nama_pemilik' => (string) ($user->nama ?: 'Pemilik Usaha'),
                'nama_usaha' => (string) ($user->nama_usaha ?: 'Toko UMKM AlpetBizz'),
                'kategori_usaha' => (string) ($user->kategori_usaha ?: 'Umum / Toko Kelontong'),
                'alamat_usaha' => (string) ($user->alamat_usaha ?: '-'),
            ],
            'keuangan' => [
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'laba_bersih' => $labaBersih,
                'margin_pct' => $marginPct,
                'jumlah_transaksi' => $transactions->count(),
                'tren_bulanan' => $trenBulanan,
                'transaksi_terbaru' => $transaksiTerbaru,
            ],
            'inventaris' => [
                'total_sku' => $totalSku,
                'total_unit' => $totalUnit,
                'modal_stok' => $modalStok,
                'nilai_jual_stok' => $nilaiJualStok,
                'potensi_laba_stok' => $potensiLabaStok,
                'stok_kritis_count' => $produkKritisCollection->count(),
                'produk_kritis' => $produkKritis,
                'produk_terlaris' => $produkTerlaris,
                'produk_belum_terjual' => $produkBelumTerjual,
                'daftar_produk' => $daftarProduk,
            ],
            'kasbon' => [
                'total_piutang' => $totalPiutang,
                'sisa_piutang' => $sisaPiutang,
                'kolektibilitas_pct' => $kolektibilitasPct,
                'sisa_hutang' => $sisaHutang,
                'tagihan_aktif' => $tagihanAktif,
                'jatuh_tempo' => $jatuhTempo,
            ],
        ];
    }

    /**
     * Kirim pertanyaan konsultasi bisnis ke Gemini API beserta konteks data toko dan riwayat percakapan.
     *
     * @param  Collection<int, AiConsultation>  $history
     */
    public function ask(User $user, string $prompt, Collection $history): string
    {
        $apiKey = trim((string) config('services.gemini.key', ''));
        if ($apiKey === '') {
            throw new DomainException('API Key Gemini belum dikonfigurasi. Tambahkan GEMINI_API_KEY di file .env (lokal) atau Environment Variables Vercel.');
        }

        $model = $this->modelName();
        $timeout = max(5, (int) config('services.gemini.timeout', 25));
        $snapshot = $this->buildBusinessSnapshot($user);

        $contents = [];
        foreach ($history->take(-10) as $message) {
            $role = $message->role === AiConsultation::ROLE_MODEL ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message->content]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $this->buildSystemInstruction($snapshot)],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.65,
                'maxOutputTokens' => 2048,
            ],
        ];

        $response = $this->sendGenerateContent($apiKey, $model, $payload, $timeout);

        if (in_array($response->status(), [404, 429, 503], true)) {
            $fallbackModel = $this->discoverFallbackModel($apiKey, $model);
            if ($fallbackModel !== null) {
                $model = $fallbackModel;
                $response = $this->sendGenerateContent($apiKey, $model, $payload, $timeout);
            }
        }

        if ($response->failed()) {
            $status = $response->status();
            $apiMessage = (string) $response->json('error.message', '');

            if ($status === 401 || $status === 403) {
                throw new DomainException('API Key Gemini tidak valid atau akses ditolak. Periksa kembali GEMINI_API_KEY Anda.');
            }

            if ($status === 429) {
                throw new RuntimeException('Kuota / batas permintaan (rate limit) Gemini API sedang penuh. Tunggu sebentar lalu coba lagi.');
            }

            if ($status === 404) {
                throw new DomainException("Model Gemini '{$model}' tidak ditemukan. Periksa konfigurasi GEMINI_MODEL.");
            }

            $detail = $apiMessage !== '' ? " ({$apiMessage})" : '';

            throw new RuntimeException("Gemini API mengembalikan galat HTTP {$status}{$detail}.");
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $replyText = collect(is_array($parts) ? $parts : [])
            ->filter(fn (mixed $part): bool => is_array($part) && ! ($part['thought'] ?? false) && is_string($part['text'] ?? null) && trim($part['text']) !== '')
            ->pluck('text')
            ->implode("\n\n");

        if (trim($replyText) === '') {
            $finishReason = (string) $response->json('candidates.0.finishReason', '');
            if ($finishReason !== '' && $finishReason !== 'STOP') {
                throw new RuntimeException("Gemini AI tidak dapat memberikan jawaban (alasan: {$finishReason}). Coba ubah kalimat pertanyaan Anda.");
            }

            throw new RuntimeException('Gemini AI mengembalikan respons kosong. Silakan ulangi pertanyaan Anda.');
        }

        return trim($replyText);
    }

    /**
     * Kirim request generateContent ke model Gemini tertentu.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function sendGenerateContent(string $apiKey, string $model, array $payload, int $timeout): Response
    {
        $url = sprintf(self::ENDPOINT_TEMPLATE, rawurlencode($model));

        try {
            return Http::withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
                ->acceptJson()
                ->asJson()
                ->connectTimeout(5)
                ->timeout($timeout)
                ->post($url, $payload);
        } catch (ConnectionException) {
            throw new RuntimeException('Gagal terhubung ke server Gemini AI (timeout atau masalah jaringan). Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Cari otomatis model Flash Gemini aktif yang mendukung generateContent jika model default tidak tersedia (404).
     */
    protected function discoverFallbackModel(string $apiKey, string $failedModel): ?string
    {
        try {
            $listResponse = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(8)
                ->get(self::MODELS_ENDPOINT);
        } catch (ConnectionException) {
            return null;
        }

        if ($listResponse->failed()) {
            return null;
        }

        $models = $listResponse->json('models', []);
        if (! is_array($models)) {
            return null;
        }

        $candidates = [];
        foreach ($models as $item) {
            if (! is_array($item)) {
                continue;
            }

            $methods = $item['supportedGenerationMethods'] ?? [];
            if (! is_array($methods) || ! in_array('generateContent', $methods, true)) {
                continue;
            }

            $rawName = (string) ($item['name'] ?? '');
            $shortName = str_starts_with($rawName, 'models/') ? substr($rawName, 7) : $rawName;
            if ($shortName === '' || $shortName === $failedModel || ! str_starts_with($shortName, 'gemini-')) {
                continue;
            }

            if (str_contains($shortName, 'embedding') || str_contains($shortName, 'tts') || str_contains($shortName, 'image') || str_contains($shortName, 'audio') || str_contains($shortName, 'robotics')) {
                continue;
            }

            $candidates[] = $shortName;
        }

        if (empty($candidates)) {
            return null;
        }

        $score = static function (string $name): int {
            if ($name === 'gemini-flash-lite-latest') {
                return 1000;
            }
            if ($name === 'gemini-flash-latest') {
                return 950;
            }

            $pts = 0;
            if (str_contains($name, 'flash')) {
                $pts += 500;
            }
            if (str_contains($name, 'lite')) {
                $pts += 50;
            }
            if (str_contains($name, 'preview') || str_contains($name, 'exp') || str_contains($name, 'thinking')) {
                $pts -= 60;
            }
            if (preg_match('/gemini-(\d+)(?:\.(\d+))?/', $name, $m) === 1) {
                $pts += ((int) $m[1]) * 40 + ((int) ($m[2] ?? 0)) * 4;
            }

            return $pts;
        };

        usort($candidates, fn (string $a, string $b): int => $score($b) <=> $score($a));

        return $candidates[0];
    }

    /**
     * Susun system instruction terstruktur berisi snapshot data live usaha pengguna.
     *
     * @param  array<string, mixed>  $snapshot
     */
    protected function buildSystemInstruction(array $snapshot): string
    {
        $fmtRp = static fn (int $amount): string => 'Rp '.number_format($amount, 0, ',', '.');
        $profil = $snapshot['profil'];
        $keu = $snapshot['keuangan'];
        $inv = $snapshot['inventaris'];
        $kas = $snapshot['kasbon'];

        $lines = [
            'Kamu adalah "AlpetBizz AI", konsultan bisnis UMKM dan toko kelontong profesional yang ramah, praktis, dan berbasis data.',
            'Jawab selalu dalam bahasa Indonesia yang mudah dipahami pemilik UMKM, ringkas namun tuntas, gunakan poin bernomor atau bullet bila perlu, serta berikan langkah aksi konkret.',
            'Gunakan angka dan nama produk/pihak nyata dari DATA TOKO LIVE di bawah ini saat relevan. Jangan pernah mengarang angka yang tidak ada di data.',
            'Jika pengguna bertanya di luar topik bisnis/UMKM/keuangan/stok/pemasaran, arahkan kembali dengan sopan ke topik pengelolaan bisnis mereka.',
            '',
            '=== DATA TOKO LIVE SAAT INI ===',
            "Pemilik: {$profil['nama_pemilik']} | Nama Usaha: {$profil['nama_usaha']} | Kategori: {$profil['kategori_usaha']} | Alamat: {$profil['alamat_usaha']}",
            '',
            '[1] RINGKASAN ARUS KAS',
            '- Total Pemasukan: '.$fmtRp((int) $keu['pemasukan']),
            '- Total Pengeluaran: '.$fmtRp((int) $keu['pengeluaran']),
            '- Laba Bersih: '.($keu['laba_bersih'] >= 0 ? '+' : '-').$fmtRp(abs((int) $keu['laba_bersih']))." (Net Profit Margin: {$keu['margin_pct']}%)",
            "- Total Transaksi Tercatat: {$keu['jumlah_transaksi']}",
        ];

        if (! empty($keu['tren_bulanan'])) {
            $trenStr = [];
            foreach ($keu['tren_bulanan'] as $bulan => $val) {
                $trenStr[] = "{$bulan}: Masuk ".$fmtRp((int) $val['pemasukan']).', Keluar '.$fmtRp((int) $val['pengeluaran']);
            }
            $lines[] = '- Tren Bulanan: '.implode(' | ', $trenStr);
        }

        $lines[] = '';
        $lines[] = '[2] INVENTARIS & KATALOG PRODUK';
        $lines[] = "- Total SKU: {$inv['total_sku']} | Total Unit: {$inv['total_unit']} | Modal Stok: ".$fmtRp((int) $inv['modal_stok']).' | Nilai Jual: '.$fmtRp((int) $inv['nilai_jual_stok']).' | Potensi Laba Stok: '.$fmtRp((int) $inv['potensi_laba_stok']);
        $lines[] = "- Produk Stok Kritis (<= batas min): {$inv['stok_kritis_count']} SKU";

        if (! empty($inv['produk_kritis'])) {
            $kritisStr = array_map(
                fn (array $p): string => "{$p['nama']} (sisa {$p['stok']} {$p['satuan']}, min {$p['stok_min']})",
                $inv['produk_kritis']
            );
            $lines[] = '  Daftar Kritis: '.implode('; ', $kritisStr);
        }

        if (! empty($inv['produk_terlaris'])) {
            $larisStr = array_map(
                fn (array $p): string => "{$p['nama']} ({$p['frekuensi']}x terjual, omzet ".$fmtRp((int) $p['omzet']).')',
                $inv['produk_terlaris']
            );
            $lines[] = '- Produk Terlaris: '.implode('; ', $larisStr);
        }

        if (! empty($inv['produk_belum_terjual'])) {
            $lines[] = '- Produk Belum Ada Catatan Penjualan: '.implode(', ', $inv['produk_belum_terjual']);
        }

        if (! empty($inv['daftar_produk'])) {
            $prodStr = array_map(
                fn (array $p): string => "{$p['nama']} [{$p['kategori']}] Beli ".$fmtRp((int) $p['harga_beli']).' / Jual '.$fmtRp((int) $p['harga_jual'])." (margin {$p['margin_pct']}%, stok {$p['stok']}/min {$p['stok_min']})",
                $inv['daftar_produk']
            );
            $lines[] = '- Detail Katalog: '.implode(' | ', $prodStr);
        }

        $lines[] = '';
        $lines[] = '[3] HUTANG & PIUTANG (KASBON)';
        $lines[] = '- Total Piutang: '.$fmtRp((int) $kas['total_piutang']).' | Sisa Piutang Belum Cair: '.$fmtRp((int) $kas['sisa_piutang'])." (Kolektibilitas: {$kas['kolektibilitas_pct']}%)";
        $lines[] = '- Sisa Hutang ke Supplier: '.$fmtRp((int) $kas['sisa_hutang'])." | Tagihan Aktif: {$kas['tagihan_aktif']} pihak";

        if (! empty($kas['jatuh_tempo'])) {
            $jtStr = array_map(
                fn (array $j): string => "{$j['nama_pihak']} ({$j['jenis']}, sisa ".$fmtRp((int) $j['sisa']).", jatuh tempo {$j['jatuh_tempo']}".($j['overdue'] ? ' - LEWAT JATUH TEMPO' : '').')',
                $kas['jatuh_tempo']
            );
            $lines[] = '- Jatuh Tempo Terdekat: '.implode('; ', $jtStr);
        }

        return implode("\n", $lines);
    }
}
