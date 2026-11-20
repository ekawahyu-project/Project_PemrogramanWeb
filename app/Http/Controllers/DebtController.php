<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;

class DebtController extends Controller
{
    /**
     * Batas nominal wajar untuk satu catatan (Rp 10 miliar).
     */
    public const MAX_AMOUNT = 10_000_000_000;

    /**
     * Tampilkan halaman manajemen Hutang & Piutang (Kasbon) milik pengguna yang login.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $currentPage = 'hutang_piutang';
        $flash = session('flash');
        $errorsList = session('errors_list', []);
        $notif = $this->buildNotification($flash, $errorsList);
        $editId = $request->query('edit');
        $editDebt = $editId ? $user->debts()->find($editId) : null;
        if ($editDebt && $editDebt->isPaid()) {
            $editDebt = null;
        }

        $debtList = $user->debts()
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [Debt::STATUS_PAID])
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
            'errorsList',
            'notif',
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
        /** @var User $user */
        $user = $request->user();

        $validator = $this->makeValidator($request);

        if ($validator->fails()) {
            return redirect('/hutang-piutang#form-hutang')
                ->withInput()
                ->with('errors_list', $validator->errors()->all())
                ->with('flash', [
                    'type' => 'danger',
                    'msg' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();
        $jumlah = (int) $validated['jumlah'];
        $dibayar = (int) ($validated['dibayar'] ?? 0);

        $user->debts()->create([
            'nama_pihak' => trim($validated['nama_pihak']),
            'no_hp' => trim((string) ($validated['no_hp'] ?? '')) ?: null,
            'jenis' => $validated['jenis'],
            'jumlah' => $jumlah,
            'dibayar' => $dibayar,
            'tanggal' => $validated['tanggal'],
            'jatuh_tempo' => ($validated['jatuh_tempo'] ?? null) ?: null,
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
        $debt = $this->findOwnedDebt($request);

        if (! $debt) {
            return $this->notFoundResponse();
        }

        if ($debt->isPaid()) {
            return redirect('/hutang-piutang')->with('flash', [
                'type' => 'danger',
                'msg' => 'Catatan yang sudah Lunas tidak dapat diubah lagi.',
            ]);
        }

        $validator = $this->makeValidator($request);

        if ($validator->fails()) {
            return redirect('/hutang-piutang?edit='.$debt->id)
                ->withInput()
                ->with('errors_list', $validator->errors()->all())
                ->with('flash', [
                    'type' => 'danger',
                    'msg' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();
        $jumlah = (int) $validated['jumlah'];
        $dibayar = (int) ($validated['dibayar'] ?? 0);

        $debt->update([
            'nama_pihak' => trim($validated['nama_pihak']),
            'no_hp' => trim((string) ($validated['no_hp'] ?? '')) ?: null,
            'jenis' => $validated['jenis'],
            'jumlah' => $jumlah,
            'dibayar' => $dibayar,
            'tanggal' => $validated['tanggal'],
            'jatuh_tempo' => ($validated['jatuh_tempo'] ?? null) ?: null,
            'status' => Debt::resolveStatus($jumlah, $dibayar),
            'keterangan' => trim((string) ($validated['keterangan'] ?? '')) ?: null,
        ]);

        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'success',
            'msg' => 'Data catatan berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus catatan hutang / piutang.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $debt = $this->findOwnedDebt($request);

        if (! $debt) {
            return $this->notFoundResponse();
        }

        $debt->delete();

        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'success',
            'msg' => 'Catatan berhasil dihapus.',
        ]);
    }

    /**
     * Buat validator untuk form tambah / ubah catatan hutang piutang.
     */
    protected function makeValidator(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'nama_pihak' => ['required', 'string', 'min:2', 'max:100'],
            'no_hp' => ['nullable', 'string', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'],
            'jenis' => ['required', Rule::in(Debt::TYPES)],
            'jumlah' => ['required', 'integer', 'min:1', 'max:'.self::MAX_AMOUNT],
            'dibayar' => ['nullable', 'integer', 'min:0', 'lte:jumlah'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jatuh_tempo' => ['nullable', 'date', 'after_or_equal:tanggal'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_pihak.required' => 'Nama pelanggan / supplier wajib diisi.',
            'nama_pihak.min' => 'Nama pelanggan / supplier minimal 2 karakter.',
            'nama_pihak.max' => 'Nama pelanggan / supplier maksimal 100 karakter.',
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan angka saja, contoh: 081234567890.',
            'jenis.required' => 'Jenis catatan wajib dipilih.',
            'jenis.in' => 'Jenis catatan harus Piutang atau Hutang.',
            'jumlah.required' => 'Total nominal wajib diisi.',
            'jumlah.integer' => 'Total nominal harus berupa angka bulat tanpa titik/koma.',
            'jumlah.min' => 'Total nominal harus lebih dari Rp 0.',
            'jumlah.max' => 'Total nominal tidak wajar (maksimal Rp 10.000.000.000).',
            'dibayar.integer' => 'Nominal yang sudah dibayar harus berupa angka bulat.',
            'dibayar.min' => 'Nominal yang sudah dibayar tidak boleh negatif.',
            'dibayar.lte' => 'Nominal yang sudah dibayar tidak boleh melebihi total nominal.',
            'tanggal.required' => 'Tanggal catat wajib diisi.',
            'tanggal.date' => 'Format tanggal catat tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal catat tidak boleh melebihi hari ini.',
            'jatuh_tempo.date' => 'Format tanggal jatuh tempo tidak valid.',
            'jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal catat.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ]);
    }

    /**
     * Cari catatan berdasarkan ID, hanya milik pengguna yang sedang login.
     */
    protected function findOwnedDebt(Request $request): ?Debt
    {
        /** @var User $user */
        $user = $request->user();
        $id = $request->input('id');

        if (! is_numeric($id)) {
            return null;
        }

        return $user->debts()->find((int) $id);
    }

    /**
     * Susun payload notifikasi SweetAlert dari error validasi atau flash session.
     *
     * @param  array<int, string>  $errorsList
     * @return array<string, string>|null
     */
    protected function buildNotification(mixed $flash, array $errorsList): ?array
    {
        if (! empty($errorsList)) {
            $daftar = implode('', array_map(
                fn (string $e): string => '<li style="margin-bottom:4px">• '.e($e).'</li>',
                $errorsList
            ));

            return [
                'icon' => 'error',
                'title' => 'Gagal!',
                'html' => '<ul style="text-align:left;list-style:none;padding:0;font-size:14px">'.$daftar.'</ul>',
                'confirmButtonText' => 'Coba lagi',
            ];
        }

        if (is_array($flash) && ! empty($flash['msg'])) {
            $isSuccess = ($flash['type'] ?? '') === 'success';

            return [
                'icon' => $isSuccess ? 'success' : 'error',
                'title' => $isSuccess ? 'Sukses!' : 'Gagal!',
                'text' => (string) $flash['msg'],
                'confirmButtonText' => $isSuccess ? 'OK' : 'Coba lagi',
            ];
        }

        return null;
    }

    /**
     * Respons standar ketika catatan tidak ditemukan / bukan milik pengguna.
     */
    protected function notFoundResponse(): RedirectResponse
    {
        return redirect('/hutang-piutang')->with('flash', [
            'type' => 'danger',
            'msg' => 'Data catatan tidak ditemukan atau Anda tidak memiliki akses.',
        ]);
    }
}
