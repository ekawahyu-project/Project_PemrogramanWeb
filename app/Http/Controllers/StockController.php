<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Tampilkan halaman mutasi stok.
     */
    public function index(Request $request): View
    {
        $currentPage = 'stok';
        $editId = $request->query('edit');
        $editLog = null;
        $stokLog = session('stok_log', []);

        if ($editId) {
            foreach ($stokLog as $l) {
                if (($l['id'] ?? '') === $editId) {
                    $editLog = $l;
                    break;
                }
            }
        }

        return view('stok', compact('currentPage', 'editId', 'editLog'));
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
        $produkId = (string) $request->input('produk_id', '');
        $jenis = (string) $request->input('jenis', 'Masuk');
        $jumlah = (int) $request->input('jumlah', 0);
        $ket = trim((string) $request->input('keterangan', ''));
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));

        if (! $produkId || $jumlah <= 0) {
            return redirect('/stok')->with('error', 'Pilih produk dan masukkan jumlah lebih dari 0.');
        }

        $produkList = session('produk', []);
        $stokLogList = session('stok_log', []);
        $valid = true;
        $errorMessage = '';

        foreach ($produkList as &$p) {
            if (($p['id'] ?? '') === $produkId) {
                if ($jenis === 'Keluar' && ($p['stok'] ?? 0) < $jumlah) {
                    $errorMessage = "Stok {$p['nama']} tidak mencukupi (tersedia: {$p['stok']}).";
                    $valid = false;
                } else {
                    $p['stok'] = $jenis === 'Masuk' ? ($p['stok'] ?? 0) + $jumlah : ($p['stok'] ?? 0) - $jumlah;
                }
                break;
            }
        }
        unset($p);

        if (! $valid) {
            return redirect('/stok')->with('error', $errorMessage);
        }

        $stokLogList[] = [
            'id' => uniqid('sl'),
            'tanggal' => $tanggal,
            'produk_id' => $produkId,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'keterangan' => $ket,
        ];

        session([
            'produk' => $produkList,
            'stok_log' => $stokLogList,
        ]);
        simpanProduk();

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
        $produkId = (string) $request->input('produk_id', '');
        $jenis = (string) $request->input('jenis', 'Masuk');
        $jumlah = (int) $request->input('jumlah', 0);
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        $ket = trim((string) $request->input('keterangan', ''));

        $stokLogList = session('stok_log', []);
        $produkList = session('produk', []);

        $oldLogIndex = null;
        foreach ($stokLogList as $idx => $l) {
            if (($l['id'] ?? '') === $id) {
                $oldLogIndex = $idx;
                break;
            }
        }

        if ($oldLogIndex === null || ! $produkId || $jumlah <= 0) {
            return redirect('/stok')->with('error', 'Data mutasi tidak valid.');
        }

        $oldLog = $stokLogList[$oldLogIndex];
        $produkBackup = $produkList;

        // Balikkan efek log lama
        foreach ($produkList as &$p) {
            if (($p['id'] ?? '') === ($oldLog['produk_id'] ?? '')) {
                $p['stok'] = ($oldLog['jenis'] ?? '') === 'Masuk'
                    ? max(0, ($p['stok'] ?? 0) - (int) ($oldLog['jumlah'] ?? 0))
                    : ($p['stok'] ?? 0) + (int) ($oldLog['jumlah'] ?? 0);
                break;
            }
        }
        unset($p);

        // Terapkan efek log baru
        $valid = true;
        $errorMessage = '';
        foreach ($produkList as &$p) {
            if (($p['id'] ?? '') === $produkId) {
                if ($jenis === 'Keluar' && ($p['stok'] ?? 0) < $jumlah) {
                    $errorMessage = "Stok {$p['nama']} tidak mencukupi untuk mutasi ini (tersedia: {$p['stok']}).";
                    $valid = false;
                } else {
                    $p['stok'] = $jenis === 'Masuk' ? ($p['stok'] ?? 0) + $jumlah : ($p['stok'] ?? 0) - $jumlah;
                }
                break;
            }
        }
        unset($p);

        if (! $valid) {
            session(['produk' => $produkBackup]);

            return redirect('/stok?edit='.$id)->with('error', $errorMessage);
        }

        $stokLogList[$oldLogIndex] = [
            'id' => $id,
            'tanggal' => $tanggal,
            'produk_id' => $produkId,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'keterangan' => $ket,
        ];

        session([
            'produk' => $produkList,
            'stok_log' => $stokLogList,
        ]);
        simpanProduk();

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
        $id = (string) $request->input('id', '');
        $stokLogList = session('stok_log', []);
        $produkList = session('produk', []);

        foreach ($stokLogList as $log) {
            if (($log['id'] ?? '') === $id) {
                foreach ($produkList as &$p) {
                    if (($p['id'] ?? '') === ($log['produk_id'] ?? '')) {
                        $p['stok'] = ($log['jenis'] ?? '') === 'Masuk'
                            ? max(0, ($p['stok'] ?? 0) - (int) ($log['jumlah'] ?? 0))
                            : ($p['stok'] ?? 0) + (int) ($log['jumlah'] ?? 0);
                        break;
                    }
                }
                unset($p);
                break;
            }
        }

        $stokLogList = array_values(array_filter($stokLogList, fn ($l) => ($l['id'] ?? '') !== $id));

        session([
            'produk' => $produkList,
            'stok_log' => $stokLogList,
        ]);
        simpanProduk();

        return redirect('/stok')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => 'Log mutasi berhasil dihapus dan stok telah dibalikkan.',
        ]);
    }
}
