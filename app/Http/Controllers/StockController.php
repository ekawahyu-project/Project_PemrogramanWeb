<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
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
     * Tampilkan halaman mutasi stok.
     */
    public function index(Request $request): View
    {
        $currentPage = 'stok';
        $error = session('error', '');
        $flash = session('flash');

        $produkList = Product::orderBy('id')->get();
        $lowStock = Product::lowStock()->orderBy('id')->get();
        $stokLogList = StockLog::with('product')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $editId = $request->query('edit');
        $editLog = $editId ? StockLog::find($editId) : null;

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
        $action = $request->input('action', '');

        if ($action === 'catat') {
            return $this->store($request);
        }

        if ($action === 'update_log') {
            return $this->update($request);
        }

        if ($action === 'hapus_log') {
            return $this->destroy($request);
        }

        return redirect('/stok');
    }

    /**
     * Catat pergerakan stok baru.
     */
    protected function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'produk_id' => ['required', 'integer', 'exists:products,id'],
            'jenis' => ['required', Rule::in(StockLog::TYPES)],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect('/stok')->with('error', 'Pilih produk dan masukkan jumlah lebih dari 0.');
        }

        $validated = $validator->validated();

        try {
            $this->stockService->record(
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
        $id = (string) $request->input('id', '');
        $stockLog = StockLog::find($id);

        $validator = Validator::make($request->all(), [
            'produk_id' => ['required', 'integer', 'exists:products,id'],
            'jenis' => ['required', Rule::in(StockLog::TYPES)],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $stockLog || $validator->fails()) {
            return redirect('/stok')->with('error', 'Data mutasi tidak valid.');
        }

        $validated = $validator->validated();

        try {
            $this->stockService->update(
                $stockLog,
                (int) $validated['produk_id'],
                $validated['jenis'],
                (int) $validated['jumlah'],
                $validated['tanggal'],
                trim((string) ($validated['keterangan'] ?? ''))
            );
        } catch (DomainException $e) {
            return redirect('/stok?edit='.$id)->with('error', $e->getMessage());
        }

        return redirect('/stok')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => 'Catatan stok berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus log mutasi stok dan balikkan perubahannya.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $stockLog = StockLog::find($request->input('id'));

        if ($stockLog) {
            $this->stockService->reverseAndDelete($stockLog);
        }

        return redirect('/stok')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => 'Log mutasi berhasil dihapus dan stok telah dibalikkan.',
        ]);
    }
}
