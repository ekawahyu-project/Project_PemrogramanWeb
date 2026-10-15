<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Tampilkan halaman data transaksi.
     */
    public function index(Request $request): View
    {
        $currentPage = 'transaksi';
        $editId = $request->query('edit');
        $editTrx = null;
        $transaksi = session('transaksi', []);

        if ($editId) {
            foreach ($transaksi as $t) {
                if (($t['id'] ?? '') === $editId) {
                    $editTrx = $t;
                    break;
                }
            }
        }

        return view('transaksi', compact('currentPage', 'editId', 'editTrx'));
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
        $jumlahStr = (string) $request->input('jumlah', '0');
        $jumlah = (int) preg_replace('/\D/', '', $jumlahStr);
        $keterangan = trim((string) $request->input('keterangan', ''));
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        $jenis = (string) $request->input('jenis', 'Pemasukan');
        $produkId = (string) $request->input('produk_id', '') ?: null;

        if ($keterangan === '') {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Keterangan tidak boleh kosong.']);
        }

        if ($jumlah <= 0) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Jumlah nominal harus lebih dari 0.']);
        }

        $transaksiList = session('transaksi', []);
        $transaksiList[] = [
            'id' => uniqid('t'),
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'produk_id' => $produkId,
        ];

        session(['transaksi' => $transaksiList]);

        return redirect('/transaksi')->with('flash', ['type' => 'success', 'msg' => 'Transaksi berhasil ditambahkan.']);
    }

    /**
     * Perbarui data transaksi.
     */
    protected function update(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $jumlahStr = (string) $request->input('jumlah', '0');
        $jumlah = (int) preg_replace('/\D/', '', $jumlahStr);
        $keterangan = trim((string) $request->input('keterangan', ''));
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        $jenis = (string) $request->input('jenis', 'Pemasukan');
        $produkId = (string) $request->input('produk_id', '') ?: null;

        $transaksiList = session('transaksi', []);
        $found = false;

        foreach ($transaksiList as &$t) {
            if (($t['id'] ?? '') === $id) {
                $t['tanggal'] = $tanggal;
                $t['keterangan'] = $keterangan;
                $t['jenis'] = $jenis;
                $t['produk_id'] = $produkId;
                if ($jumlah > 0) {
                    $t['jumlah'] = $jumlah;
                }
                $found = true;
                break;
            }
        }
        unset($t);

        if ($found) {
            session(['transaksi' => $transaksiList]);

            return redirect('/transaksi')->with('flash', ['type' => 'success', 'msg' => 'Transaksi berhasil diperbarui.']);
        }

        return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi tidak ditemukan.']);
    }

    /**
     * Hapus transaksi.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $transaksiList = session('transaksi', []);
        $before = count($transaksiList);

        $transaksiList = array_values(array_filter($transaksiList, fn ($t) => ($t['id'] ?? '') !== $id));

        session(['transaksi' => $transaksiList]);

        if (count($transaksiList) < $before) {
            return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi berhasil dihapus.']);
        }

        return redirect('/transaksi')->with('flash', ['type' => 'danger', 'msg' => 'Transaksi tidak ditemukan.']);
    }
}
