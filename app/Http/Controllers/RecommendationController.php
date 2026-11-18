<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recommendation;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    /**
     * Tampilkan halaman rekomendasi dan catatan tindak lanjut.
     */
    public function index(Request $request): View
    {
        $currentPage = 'rekomendasi';
        $prioritasOpt = Recommendation::PRIORITIES;

        $incomeTransactions = Transaction::income()
            ->whereNotNull('product_id')
            ->with('product')
            ->get();

        $produkStats = [];
        foreach ($incomeTransactions as $trx) {
            $pid = $trx->product_id;
            if (! isset($produkStats[$pid])) {
                $produkStats[$pid] = [
                    'count' => 0,
                    'revenue' => 0,
                    'nama' => $trx->product?->nama ?? 'Dihapus',
                ];
            }

            $produkStats[$pid]['count']++;
            $produkStats[$pid]['revenue'] += $trx->jumlah;
        }

        uasort($produkStats, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        $lowStock = Product::lowStock()->orderBy('id')->get();
        $unsoldProd = Product::whereDoesntHave('transactions', fn ($query) => $query->income())
            ->orderBy('id')
            ->get();

        $catatanList = Recommendation::orderByDesc('id')->get();
        $editId = $request->query('edit');
        $editCat = $editId ? Recommendation::find($editId) : null;

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
        $action = $request->input('action', '');

        if ($action === 'tambah') {
            return $this->store($request);
        }

        if ($action === 'update') {
            return $this->update($request);
        }

        if ($action === 'hapus') {
            return $this->destroy($request);
        }

        return redirect('/rekomendasi');
    }

    /**
     * Tambah catatan rekomendasi.
     */
    protected function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'prioritas' => ['required', Rule::in(Recommendation::PRIORITIES)],
        ]);

        if ($validator->fails()) {
            return redirect('/rekomendasi');
        }

        $validated = $validator->validated();

        Recommendation::create([
            'judul' => trim($validated['judul']),
            'isi' => trim($validated['isi']),
            'prioritas' => $validated['prioritas'],
            'dibuat' => now()->toDateString(),
        ]);

        return redirect('/rekomendasi');
    }

    /**
     * Update catatan rekomendasi.
     */
    protected function update(Request $request): RedirectResponse
    {
        $recommendation = Recommendation::find($request->input('id'));

        if (! $recommendation) {
            return redirect('/rekomendasi');
        }

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'prioritas' => ['required', Rule::in(Recommendation::PRIORITIES)],
        ]);

        if ($validator->fails()) {
            return redirect('/rekomendasi?edit='.$recommendation->id);
        }

        $validated = $validator->validated();

        $recommendation->update([
            'judul' => trim($validated['judul']),
            'isi' => trim($validated['isi']),
            'prioritas' => $validated['prioritas'],
        ]);

        return redirect('/rekomendasi');
    }

    /**
     * Hapus catatan rekomendasi.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        Recommendation::find($request->input('id'))?->delete();

        return redirect('/rekomendasi');
    }
}
