<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Tampilkan halaman data transaksi.
     */
    public function index(Request $request): View
    {
        $currentPage = 'transaksi';
        $flash = session('flash');
        $editId = $request->query('edit');
        $editTrx = $editId ? Transaction::find($editId) : null;

        $transaksiList = Transaction::with('product')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $produkList = Product::orderBy('nama')->get();

        return view('transaksi', compact(
            'currentPage',
            'flash',
            'editId',
            'editTrx',
            'transaksiList',
            'produkList'
        ));
    }

    /**
     * Tangani request CRUD transaksi.
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

        return redirect('/transaksi');
    }

    /**
     * Tambah transaksi baru.
     */
    protected function store(Request $request): RedirectResponse
    {
        $jumlah = (int) preg_replace('/\D/', '', (string) $request->input('jumlah', '0'));
        $keterangan = trim((string) $request->input('keterangan', ''));
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        $jenis = (string) $request->input('jenis', Transaction::TYPE_INCOME);
        $productId = $request->input('produk_id') ?: null;

        if ($keterangan === '') {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Keterangan tidak boleh kosong.']);
        }

        if ($jumlah <= 0) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Jumlah nominal harus lebih dari 0.']);
        }

        $validator = Validator::make([
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'product_id' => $productId,
        ], [
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', Rule::in(Transaction::TYPES)],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        if ($validator->fails()) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => $validator->errors()->first()]);
        }

        Transaction::create([
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'product_id' => $productId ? (int) $productId : null,
        ]);

        return redirect('/transaksi')->with('flash', ['type' => 'success', 'msg' => 'Transaksi berhasil ditambahkan.']);
    }

    /**
     * Perbarui data transaksi.
     */
    protected function update(Request $request): RedirectResponse
    {
        $transaction = Transaction::find($request->input('id'));

        if (! $transaction) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi tidak ditemukan.']);
        }

        $jumlah = (int) preg_replace('/\D/', '', (string) $request->input('jumlah', '0'));
        $keterangan = trim((string) $request->input('keterangan', ''));
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        $jenis = (string) $request->input('jenis', Transaction::TYPE_INCOME);
        $productId = $request->input('produk_id') ?: null;

        $validator = Validator::make([
            'keterangan' => $keterangan,
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'product_id' => $productId,
        ], [
            'keterangan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', Rule::in(Transaction::TYPES)],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        if ($validator->fails()) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => $validator->errors()->first()]);
        }

        $transaction->update([
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'product_id' => $productId ? (int) $productId : null,
            'jumlah' => $jumlah > 0 ? $jumlah : $transaction->jumlah,
        ]);

        return redirect('/transaksi')->with('flash', ['type' => 'success', 'msg' => 'Transaksi berhasil diperbarui.']);
    }

    /**
     * Hapus transaksi.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $transaction = Transaction::find($request->input('id'));

        if (! $transaction) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi tidak ditemukan.']);
        }

        $transaction->delete();

        return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi berhasil dihapus.']);
    }
}
