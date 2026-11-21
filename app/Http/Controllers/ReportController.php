<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const MONTH_NAMES = [
        '01' => 'Jan',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Apr',
        '05' => 'Mei',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Agu',
        '09' => 'Sep',
        '10' => 'Okt',
        '11' => 'Nov',
        '12' => 'Des',
    ];

    /**
     * Tampilkan halaman laporan analitik dan laporan tersimpan milik pengguna aktif.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $currentPage = 'laporan';

        $transaksiList = $user->transactions()->orderBy('tanggal')->get();

        $bulanData = [];
        $totalMasuk = 0;
        $totalKeluar = 0;
        $pemasukanCount = 0;
        $pengeluaranCount = 0;

        foreach ($transaksiList as $t) {
            $key = $t->tanggal->format('Y-m');
            if (! isset($bulanData[$key])) {
                $bulanData[$key] = ['pemasukan' => 0, 'pengeluaran' => 0];
            }

            if ($t->isIncome()) {
                $bulanData[$key]['pemasukan'] += $t->jumlah;
                $totalMasuk += $t->jumlah;
                $pemasukanCount++;
            } else {
                $bulanData[$key]['pengeluaran'] += $t->jumlah;
                $totalKeluar += $t->jumlah;
                $pengeluaranCount++;
            }
        }

        ksort($bulanData);

        $labels = array_map(function (string $key): string {
            [$year, $month] = explode('-', $key);

            return (self::MONTH_NAMES[$month] ?? $month).' '.$year;
        }, array_keys($bulanData));

        $pemasukanData = array_column(array_values($bulanData), 'pemasukan');
        $pengeluaranData = array_column(array_values($bulanData), 'pengeluaran');
        $totalLaba = $totalMasuk - $totalKeluar;

        $laporanTersimpan = $user->reports()->orderByDesc('id')->get();
        $editId = $request->query('edit');
        $editLap = is_numeric($editId) ? $user->reports()->find((int) $editId) : null;

        $kategoriOptions = Report::CATEGORIES;
        $statusOptions = Report::STATUSES;

        return view('laporan', compact(
            'currentPage',
            'totalMasuk',
            'totalKeluar',
            'totalLaba',
            'pemasukanCount',
            'pengeluaranCount',
            'labels',
            'pemasukanData',
            'pengeluaranData',
            'laporanTersimpan',
            'editId',
            'editLap',
            'kategoriOptions',
            'statusOptions'
        ));
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
     * Simpan laporan baru milik pengguna aktif.
     */
    protected function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(Report::CATEGORIES)],
            'tanggal_dari' => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'status' => ['required', Rule::in(Report::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return redirect('/laporan');
        }

        $validated = $validator->validated();

        $user->reports()->create([
            'judul' => trim($validated['judul']),
            'kategori' => $validated['kategori'],
            'tanggal_dari' => $validated['tanggal_dari'],
            'tanggal_sampai' => $validated['tanggal_sampai'],
            'status' => $validated['status'],
            'catatan' => trim((string) ($validated['catatan'] ?? '')) ?: null,
            'pembuat' => $user->nama ?: 'Admin',
        ]);

        return redirect('/laporan');
    }

    /**
     * Update laporan tersimpan milik pengguna aktif.
     */
    protected function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        $report = is_numeric($id) ? $user->reports()->find((int) $id) : null;

        if (! $report) {
            return redirect('/laporan');
        }

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(Report::CATEGORIES)],
            'tanggal_dari' => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'status' => ['required', Rule::in(Report::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return redirect('/laporan?edit='.$report->id);
        }

        $validated = $validator->validated();

        $report->update([
            'judul' => trim($validated['judul']),
            'kategori' => $validated['kategori'],
            'tanggal_dari' => $validated['tanggal_dari'],
            'tanggal_sampai' => $validated['tanggal_sampai'],
            'status' => $validated['status'],
            'catatan' => trim((string) ($validated['catatan'] ?? '')) ?: null,
        ]);

        return redirect('/laporan');
    }

    /**
     * Hapus laporan tersimpan milik pengguna aktif.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $id = $request->input('id');
        if (is_numeric($id)) {
            $user->reports()->find((int) $id)?->delete();
        }

        return redirect('/laporan');
    }
}
