<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan analitik dan laporan tersimpan.
     */
    public function index(): View
    {
        $currentPage = 'laporan';

        return view('laporan', compact('currentPage'));
    }

    /**
     * Tangani request CRUD laporan tersimpan.
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = $request->input('action', '');

        if ($action === 'simpan') {
            return $this->store($request);
        }

        if ($action === 'update') {
            return $this->update($request);
        }

        if ($action === 'hapus') {
            return $this->destroy($request);
        }

        return redirect('/laporan');
    }

    /**
     * Simpan laporan baru.
     */
    protected function store(Request $request): RedirectResponse
    {
        $judul = trim((string) $request->input('judul', ''));
        $kategori = trim((string) $request->input('kategori', 'Evaluasi Bulanan'));
        $dari = (string) $request->input('tanggal_dari', '');
        $sampai = (string) $request->input('tanggal_sampai', '');
        $status = trim((string) $request->input('status', 'Selesai'));
        $catatan = trim((string) $request->input('catatan', ''));

        if ($judul && $dari && $sampai && $dari <= $sampai) {
            $laporanList = session('laporan_tersimpan', []);
            $laporanList[] = [
                'id' => uniqid('l'),
                'judul' => $judul,
                'kategori' => $kategori,
                'tanggal_dari' => $dari,
                'tanggal_sampai' => $sampai,
                'status' => $status,
                'catatan' => $catatan,
                'dibuat' => date('Y-m-d'),
                'pembuat' => session('user_nama', session('user', 'Admin')),
            ];
            session(['laporan_tersimpan' => $laporanList]);
        }

        return redirect('/laporan');
    }

    /**
     * Update laporan tersimpan.
     */
    protected function update(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $laporanList = session('laporan_tersimpan', []);

        foreach ($laporanList as &$l) {
            if (($l['id'] ?? '') === $id) {
                $l['judul'] = trim((string) $request->input('judul', $l['judul'] ?? ''));
                $l['kategori'] = trim((string) $request->input('kategori', $l['kategori'] ?? 'Evaluasi Bulanan'));
                $l['tanggal_dari'] = (string) $request->input('tanggal_dari', $l['tanggal_dari'] ?? '');
                $l['tanggal_sampai'] = (string) $request->input('tanggal_sampai', $l['tanggal_sampai'] ?? '');
                $l['status'] = trim((string) $request->input('status', $l['status'] ?? 'Selesai'));
                $l['catatan'] = trim((string) $request->input('catatan', $l['catatan'] ?? ''));
                break;
            }
        }
        unset($l);

        session(['laporan_tersimpan' => $laporanList]);

        return redirect('/laporan');
    }

    /**
     * Hapus laporan tersimpan.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $laporanList = session('laporan_tersimpan', []);

        $laporanList = array_values(array_filter($laporanList, fn ($l) => ($l['id'] ?? '') !== $id));
        session(['laporan_tersimpan' => $laporanList]);

        return redirect('/laporan');
    }
}
