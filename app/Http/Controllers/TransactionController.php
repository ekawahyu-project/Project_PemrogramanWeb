<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Batas nominal maksimum untuk satu transaksi (Rp 10 miliar).
     */
    public const MAX_AMOUNT = 10_000_000_000;

    /**
     * Tampilkan halaman data transaksi milik pengguna aktif.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $currentPage = 'transaksi';
        $flash = session('flash');
        $editId = $request->query('edit');

        $produkList = $user->products()->orderBy('nama')->get();
        $productsById = $produkList->keyBy('id');

        $transaksiList = $user->transactions()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->each(function (Transaction $trx) use ($productsById): void {
                $trx->setRelation('product', $trx->product_id !== null ? $productsById->get($trx->product_id) : null);
            });

        $editTrx = is_numeric($editId) ? $transaksiList->firstWhere('id', (int) $editId) : null;

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
        return match ($request->input('action', '')) {
            'tambah' => $this->store($request),
            'update' => $this->update($request),
            'hapus' => $this->destroy($request),
            default => redirect('/transaksi'),
        };
    }

    /**
     * Tambah transaksi baru.
     */
    protected function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        [$payload, $error] = $this->parseAndValidate($request, $user, true);

        if ($error !== null) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => $error]);
        }

        $user->transactions()->create($payload);

        return redirect('/transaksi')->with('flash', ['type' => 'success', 'msg' => 'Transaksi berhasil ditambahkan.']);
    }

    /**
     * Perbarui data transaksi milik pengguna aktif.
     */
    protected function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        $transaction = is_numeric($id) ? $user->transactions()->find((int) $id) : null;

        if (! $transaction) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi tidak ditemukan.']);
        }

        [$payload, $error] = $this->parseAndValidate($request, $user, false);

        if ($error !== null) {
            return redirect('/transaksi?edit='.$transaction->id)->with('flash', ['type' => 'danger', 'msg' => $error]);
        }

        $transaction->update($payload);

        return redirect('/transaksi')->with('flash', ['type' => 'success', 'msg' => 'Transaksi berhasil diperbarui.']);
    }

    /**
     * Normalisasi dan validasi input transaksi bersama untuk tambah dan ubah.
     *
     * @return array{0: array<string, mixed>, 1: ?string}
     */
    protected function parseAndValidate(Request $request, User $user, bool $checkEmptyDescriptionFirst): array
    {
        $jumlahRaw = preg_replace('/\D/', '', (string) $request->input('jumlah', '0'));
        $jumlah = $jumlahRaw !== '' ? (int) $jumlahRaw : 0;
        $keterangan = trim((string) $request->input('keterangan', ''));
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        $jenis = (string) $request->input('jenis', Transaction::TYPE_INCOME);
        $productId = $request->input('produk_id') ?: null;

        if ($checkEmptyDescriptionFirst && $keterangan === '') {
            return [[], 'Keterangan tidak boleh kosong.'];
        }

        if ($jumlah <= 0) {
            return [[], 'Jumlah nominal harus lebih dari 0.'];
        }

        $validator = Validator::make([
            'keterangan' => $keterangan,
            'jumlah' => $jumlah,
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'product_id' => $productId,
        ], [
            'keterangan' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:'.self::MAX_AMOUNT],
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', Rule::in(Transaction::TYPES)],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('user_id', $user->id)],
        ], [
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'jumlah.max' => 'Nominal transaksi melebihi batas maksimum (Rp 10.000.000.000).',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return [[], $validator->errors()->first()];
        }

        return [[
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'product_id' => $productId ? (int) $productId : null,
        ], null];
    }

    /**
     * Hapus transaksi milik pengguna aktif.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        $transaction = is_numeric($id) ? $user->transactions()->find((int) $id) : null;

        if (! $transaction) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi tidak ditemukan.']);
        }

        $transaction->delete();

        return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi berhasil dihapus.']);
    }
}
