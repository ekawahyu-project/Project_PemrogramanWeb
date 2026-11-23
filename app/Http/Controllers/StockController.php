<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Services\StockMovementService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private StockMovementService $stockService) {}

    /**
     * Tampilkan halaman mutasi stok milik pengguna aktif.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $currentPage = 'stok';
        $error = session('error', '');
        $flash = session('flash');

        $produkList = $user->products()->orderBy('id')->get();
        $productsById = $produkList->keyBy('id');
        $lowStock = $produkList->filter(fn (Product $p): bool => $p->isLowStock())->values();
        $stokLogList = $user->stockLogs()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->each(function (StockLog $log) use ($productsById): void {
                $log->setRelation('product', $productsById->get($log->product_id));
            });

        $editId = $request->query('edit');
        $editLog = is_numeric($editId) ? $stokLogList->firstWhere('id', (int) $editId) : null;

        return view('stok', compact(
            'currentPage',
            'error',
            'flash',
            'produkList',
            'lowStock',
            'stokLogList',
            'editId',
            'editLog'
        ));
    }

    /**
     * Tangani operasi pencatatan dan penyesuaian stok.
     */
    public function handle(Request $request): RedirectResponse
    {
        return match ($request->input('action', '')) {
            'catat' => $this->store($request),
            'update_log' => $this->update($request),
            'hapus_log' => $this->destroy($request),
            default => redirect('/stok'),
        };
    }

    /**
     * Catat pergerakan stok baru.
     */
    protected function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), $this->stockRules($user));

        if ($validator->fails()) {
            return redirect('/stok')->with('error', 'Pilih produk dan masukkan jumlah lebih dari 0.');
        }

        $validated = $validator->validated();

        try {
            $this->stockService->record(
                $user,
                (int) $validated['produk_id'],
                $validated['jenis'],
                (int) $validated['jumlah'],
                $validated['tanggal'],
                trim((string) ($validated['keterangan'] ?? ''))
            );
        } catch (DomainException $e) {
            return redirect('/stok')->with('error', $e->getMessage());
        }

        return redirect('/stok')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => 'Pergerakan stok berhasil dicatat.',
        ]);
    }

    /**
     * Perbarui log mutasi stok.
     */
    protected function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        $stockLog = is_numeric($id) ? $user->stockLogs()->find((int) $id) : null;
        $validator = Validator::make($request->all(), $this->stockRules($user));

        if (! $stockLog || $validator->fails()) {
            return redirect('/stok')->with('error', 'Data mutasi tidak valid.');
        }

        $validated = $validator->validated();

        try {
            $this->stockService->update(
                $user,
                $stockLog,
                (int) $validated['produk_id'],
                $validated['jenis'],
                (int) $validated['jumlah'],
                $validated['tanggal'],
                trim((string) ($validated['keterangan'] ?? ''))
            );
        } catch (DomainException $e) {
            return redirect('/stok?edit='.$stockLog->id)->with('error', $e->getMessage());
        }

        return redirect('/stok')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => 'Catatan stok berhasil diperbarui.',
        ]);
    }

    /**
     * Aturan validasi bersama untuk pencatatan & pembaruan mutasi stok.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function stockRules(User $user): array
    {
        return [
            'produk_id' => ['required', 'integer', Rule::exists('products', 'id')->where('user_id', $user->id)],
            'jenis' => ['required', Rule::in(StockLog::TYPES)],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Hapus log mutasi stok dan balikkan perubahannya.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        $stockLog = is_numeric($id) ? $user->stockLogs()->find((int) $id) : null;

        if (! $stockLog) {
            return redirect('/stok')->with('error', 'Data mutasi tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $this->stockService->reverseAndDelete($user, $stockLog);

        return redirect('/stok')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => 'Log mutasi berhasil dihapus dan stok telah dibalikkan.',
        ]);
    }
}
