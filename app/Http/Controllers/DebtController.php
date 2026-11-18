<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DebtController extends Controller
{
    /**
     * Tampilkan halaman manajemen Hutang & Piutang (Kasbon).
     */
    public function index(Request $request): View
    {
        $currentPage = 'hutang_piutang';
        $flash = session('flash');
        $editId = $request->query('edit');
        $editDebt = $editId ? Debt::find($editId) : null;

        $debtList = Debt::orderByRaw("CASE WHEN status = 'Lunas' THEN 1 ELSE 0 END")
            ->orderBy('jatuh_tempo')
            ->orderByDesc('id')
            ->get();

        $piutangList = $debtList->where('jenis', Debt::TYPE_RECEIVABLE);
        $hutangList = $debtList->where('jenis', Debt::TYPE_PAYABLE);

        $totalPiutang = (int) $piutangList->sum('jumlah');
        $piutangDibayar = (int) $piutangList->sum('dibayar');
        $sisaPiutang = max(0, $totalPiutang - $piutangDibayar);

        $totalHutang = (int) $hutangList->sum('jumlah');
        $hutangDibayar = (int) $hutangList->sum('dibayar');
        $sisaHutang = max(0, $totalHutang - $hutangDibayar);

        $kolektibilitasPct = $totalPiutang > 0
            ? round(($piutangDibayar / $totalPiutang) * 100, 1)
            : 100.0;

        $jatuhTempoList = $debtList->filter(fn (Debt $d) => ! $d->isPaid() && $d->jatuh_tempo !== null)
            ->sortBy('jatuh_tempo')
            ->take(4);

        return view('hutang_piutang', compact(
            'currentPage',
            'flash',
            'editId',
            'editDebt',
            'debtList',
            'totalPiutang',
            'piutangDibayar',
            'sisaPiutang',
            'totalHutang',
            'hutangDibayar',
            'sisaHutang',
            'kolektibilitasPct',
            'jatuhTempoList'
        ));
    }

    /**
     * Tangani operasi CRUD Hutang & Piutang.
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = (string) $request->input('action', '');

        if ($action === 'tambah') {
            return $this->store($request);
        }

        if ($action === 'update') {
            return $this->update($request);
        }

        if ($action === 'lunasi') {
            return $this->markAsPaid($request);
        }

        if ($action === 'hapus') {
            return $this->destroy($request);
        }

        return redirect('/hutang-piutang');
    }

    /**
     * Simpan catatan kasbon pelanggan atau hutang supplier baru.
     */
    protected function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pihak' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'jenis' => ['required', Rule::in(Debt::TYPES)],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dibayar' => ['nullable', 'integer', 'min:0'],
            'tanggal' => ['required', 'date'],
            'jatuh_tempo' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect('/hutang-piutang')->with('flash', [
                'type' => 'danger',
                'msg' => 'Mohon lengkapi nama pihak, jenis, tanggal, dan nominal dengan benar.',
            ]);
        }

        $validated = $validator->validated();
        $jumlah = (int) $validated['jumlah'];
        $dibayar = min($jumlah, (int) ($validated['dibayar'] ?? 0));

        Debt::create([
            'nama_pihak' => trim($validated['nama_pihak']),
            'no_hp' => trim((string) ($validated['no_hp'] ?? '')) ?: null,
            'jenis' => $validated['jenis'],
            'jumlah' => $jumlah,
            'dibayar' => $dibayar,
            'tanggal' => $validated['tanggal'],
            'jatuh_tempo' => $validated['jatuh_tempo'] ?: null,
            'status' => Debt::resolveStatus($jumlah, $dibayar),
            'keterangan' => trim((string) ($validated['keterangan'] ?? '')) ?: null,
        ]);

        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'success',
            'msg' => 'Catatan '.strtolower($validated['jenis']).' berhasil ditambahkan.',
        ]);
    }

    /**
     * Perbarui catatan hutang / piutang dan pembayaran cicilan.
     */
    protected function update(Request $request): RedirectResponse
    {
        $debt = Debt::find($request->input('id'));

        if (! $debt) {
            return redirect('/hutang-piutang')->with('flash', [
                'type' => 'danger',
                'msg' => 'Data catatan tidak ditemukan.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'nama_pihak' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'jenis' => ['required', Rule::in(Debt::TYPES)],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dibayar' => ['nullable', 'integer', 'min:0'],
            'tanggal' => ['required', 'date'],
            'jatuh_tempo' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect('/hutang-piutang?edit='.$debt->id)->with('flash', [
                'type' => 'danger',
                'msg' => 'Input tidak valid, periksa kembali formulir.',
            ]);
        }

        $validated = $validator->validated();
        $jumlah = (int) $validated['jumlah'];
        $dibayar = min($jumlah, (int) ($validated['dibayar'] ?? 0));

        $debt->update([
            'nama_pihak' => trim($validated['nama_pihak']),
            'no_hp' => trim((string) ($validated['no_hp'] ?? '')) ?: null,
            'jenis' => $validated['jenis'],
            'jumlah' => $jumlah,
            'dibayar' => $dibayar,
            'tanggal' => $validated['tanggal'],
            'jatuh_tempo' => $validated['jatuh_tempo'] ?: null,
            'status' => Debt::resolveStatus($jumlah, $dibayar),
            'keterangan' => trim((string) ($validated['keterangan'] ?? '')) ?: null,
        ]);

        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'success',
            'msg' => 'Data catatan berhasil diperbarui.',
        ]);
    }

    /**
     * Tandai catatan hutang / piutang langsung lunas 100%.
     */
    protected function markAsPaid(Request $request): RedirectResponse
    {
        $debt = Debt::find($request->input('id'));

        if ($debt) {
            $debt->update([
                'dibayar' => $debt->jumlah,
                'status' => Debt::STATUS_PAID,
            ]);
        }

        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'success',
            'msg' => 'Catatan telah ditandai Lunas!',
        ]);
    }

    /**
     * Hapus catatan hutang / piutang.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        Debt::find($request->input('id'))?->delete();

        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'success',
            'msg' => 'Catatan berhasil dihapus.',
        ]);
    }
}
