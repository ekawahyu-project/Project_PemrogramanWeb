<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    /**
     * Tampilkan halaman rekomendasi dan catatan tindak lanjut milik pengguna aktif.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $currentPage = 'rekomendasi';
        $prioritasOpt = Recommendation::PRIORITIES;

        $incomeTransactions = $user->transactions()
            ->income()
            ->whereNotNull('product_id')
            ->get();

        $products = $user->products()->orderBy('id')->get();
        $productsById = $products->keyBy('id');

        $produkStats = [];
        foreach ($incomeTransactions as $trx) {
            $pid = $trx->product_id;
            if (! isset($produkStats[$pid])) {
                $produkStats[$pid] = [
                    'count' => 0,
                    'revenue' => 0,
                    'nama' => $productsById->get($pid)?->nama ?? 'Dihapus',
                ];
            }

            $produkStats[$pid]['count']++;
            $produkStats[$pid]['revenue'] += $trx->jumlah;
        }

        uasort($produkStats, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        $lowStock = $products->filter(fn (Product $p): bool => $p->isLowStock())->values();
        $soldProductIds = $incomeTransactions->pluck('product_id')->filter()->unique()->all();
        $unsoldProd = $products
            ->filter(fn (Product $p): bool => ! in_array($p->id, $soldProductIds, true))
            ->values();

        $catatanList = $user->recommendations()->orderByDesc('id')->get();
        $editId = $request->query('edit');
        $editCat = is_numeric($editId) ? $catatanList->firstWhere('id', (int) $editId) : null;

        return view('rekomendasi', compact(
            'currentPage',
            'prioritasOpt',
            'produkStats',
            'lowStock',
            'unsoldProd',
            'catatanList',
            'editId',
            'editCat'
        ));
    }

    /**
     * Tangani request CRUD catatan rekomendasi.
     */
    public function handle(Request $request): RedirectResponse
    {
        return match ($request->input('action', '')) {
            'tambah' => $this->store($request),
            'update' => $this->update($request),
            'hapus' => $this->destroy($request),
            default => redirect('/rekomendasi'),
        };
    }

    /**
     * Tambah catatan rekomendasi milik pengguna aktif.
     */
    protected function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $attributes = $this->validatedRecommendationAttributes($request);

        if ($attributes === null) {
            return redirect('/rekomendasi');
        }

        $user->recommendations()->create($attributes + [
            'dibuat' => now()->toDateString(),
        ]);

        return redirect('/rekomendasi');
    }

    /**
     * Update catatan rekomendasi milik pengguna aktif.
     */
    protected function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        $recommendation = is_numeric($id) ? $user->recommendations()->find((int) $id) : null;

        if (! $recommendation) {
            return redirect('/rekomendasi');
        }

        $attributes = $this->validatedRecommendationAttributes($request);

        if ($attributes === null) {
            return redirect('/rekomendasi?edit='.$recommendation->id);
        }

        $recommendation->update($attributes);

        return redirect('/rekomendasi');
    }

    /**
     * Validasi dan normalisasi atribut catatan rekomendasi.
     *
     * @return array<string, mixed>|null
     */
    protected function validatedRecommendationAttributes(Request $request): ?array
    {
        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:2000'],
            'prioritas' => ['required', Rule::in(Recommendation::PRIORITIES)],
        ]);

        if ($validator->fails()) {
            return null;
        }

        $validated = $validator->validated();

        return [
            'judul' => trim($validated['judul']),
            'isi' => trim($validated['isi']),
            'prioritas' => $validated['prioritas'],
        ];
    }

    /**
     * Hapus catatan rekomendasi milik pengguna aktif.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        if (is_numeric($id)) {
            $user->recommendations()->find((int) $id)?->delete();
        }

        return redirect('/rekomendasi');
    }
}
