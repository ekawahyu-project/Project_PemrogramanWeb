<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    /**
     * Tampilkan halaman rekomendasi dan catatan tindak lanjut.
     */
    public function index(Request $request): View
    {
        $currentPage = 'rekomendasi';
        $editId = $request->query('edit');
        $editCat = null;
        $catatan = session('catatan_rekomendasi', []);

        if ($editId) {
            foreach ($catatan as $c) {
                if (($c['id'] ?? '') === $editId) {
                    $editCat = $c;
                    break;
                }
            }
        }

        return view('rekomendasi', compact('currentPage', 'editId', 'editCat'));
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
        $judul = trim((string) $request->input('judul', ''));
        $isi = trim((string) $request->input('isi', ''));
        $prioritas = (string) $request->input('prioritas', 'Sedang');

        if ($judul && $isi) {
            $catatanList = session('catatan_rekomendasi', []);
            $catatanList[] = [
                'id' => uniqid('cr'),
                'judul' => $judul,
                'isi' => $isi,
                'prioritas' => $prioritas,
                'dibuat' => date('Y-m-d'),
            ];
            session(['catatan_rekomendasi' => $catatanList]);
        }

        return redirect('/rekomendasi');
    }

    /**
     * Update catatan rekomendasi.
     */
    protected function update(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $catatanList = session('catatan_rekomendasi', []);

        foreach ($catatanList as &$c) {
            if (($c['id'] ?? '') === $id) {
                $c['judul'] = trim((string) $request->input('judul', $c['judul'] ?? ''));
                $c['isi'] = trim((string) $request->input('isi', $c['isi'] ?? ''));
                $c['prioritas'] = (string) $request->input('prioritas', $c['prioritas'] ?? 'Sedang');
                break;
            }
        }
        unset($c);

        session(['catatan_rekomendasi' => $catatanList]);

        return redirect('/rekomendasi');
    }

    /**
     * Hapus catatan rekomendasi.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $catatanList = session('catatan_rekomendasi', []);

        $catatanList = array_values(array_filter($catatanList, fn ($c) => ($c['id'] ?? '') !== $id));
        session(['catatan_rekomendasi' => $catatanList]);

        return redirect('/rekomendasi');
    }
}
