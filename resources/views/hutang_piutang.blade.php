<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hutang &amp; Piutang | AlpetBizz</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        mono: ['Poppins', 'sans-serif']
                    }
                }
            }
        };
    </script>
</head>
<body class="bento-body font-sans antialiased text-[#F4F7F0]">
    @include('includes.sidebar')

    @php
        $netPiutangHutang = $sisaPiutang - $sisaHutang;
        $totalAktif = max($sisaPiutang + $sisaHutang, 1);
        $pctPiutang = $sisaPiutang > 0 ? round(($sisaPiutang / $totalAktif) * 100, 1) : 50.0;
        $pctHutang = $sisaHutang > 0 ? round(($sisaHutang / $totalAktif) * 100, 1) : 50.0;
        $cntBelumLunas = $debtList->where('status', '!=', 'Lunas')->count();
        $cntLunas = $debtList->where('status', 'Lunas')->count();
        $maxDebtVal = max((int) $debtList->max('jumlah'), 1);
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <!-- Gaya toast notifikasi (di dalam main agar ikut termuat oleh spa.js) -->
        <style>
            .swal2-container {
                z-index: 10050 !important;
            }
            .swal-toast-bento {
                border: 1px solid rgba(196, 255, 13, 0.35) !important;
                border-radius: 16px !important;
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
            }
            .swal2-timer-progress-bar {
                background: #C4FF0D !important;
            }
            .swal2-icon.swal2-success [class^='swal2-success-line'] {
                background-color: #C4FF0D !important;
            }
            .swal2-icon.swal2-success .swal2-success-ring {
                border-color: rgba(196, 255, 13, 0.3) !important;
            }
        </style>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Hutang Piutang Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Hutang &amp; Piutang (Kasbon)</h2>
                </div>
            </nav>

            @if (! empty($errorsList) && ! $editDebt)
            <div class="rounded-[22px] bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm p-4">
                <p class="font-semibold mb-1 text-rose-200">Periksa kembali formulir:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errorsList as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- ══════════════════════════════════════════════════════════
                 ROW 1: WEB3BENTO 4-COLUMN ANALYTICS GRID
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <!-- 1. HERO RECEIVABLE vs PAYABLE TILE (col-span-2) -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 justify-between">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#9AA68E]">Sisa Piutang Pelanggan (Kasbon)</p>
                            <p class="mt-1.5 text-3xl sm:text-4xl font-bold leading-none tracking-[-0.03em] text-white">
                                Rp {{ number_format($sisaPiutang, 0, ',', '.') }}
                            </p>
                            <p class="mt-2 text-sm text-[#9AA68E]">
                                Dana usaha di pelanggan yang belum cair · Total sudah ditagih <span class="text-[#C4FF0D] font-semibold">Rp {{ number_format($piutangDibayar, 0, ',', '.') }}</span>
                            </p>
                        </div>
                        <span class="rounded-full px-3.5 py-1.5 text-xs sm:text-sm font-semibold"
                              style="background: {{ $netPiutangHutang >= 0 ? 'rgba(196,255,13,0.15)' : 'rgba(192,132,252,0.18)' }}; color: {{ $netPiutangHutang >= 0 ? '#C4FF0D' : '#C084FC' }};">
                            {{ $netPiutangHutang >= 0 ? 'Net Piutang +' : 'Net Hutang −' }}Rp {{ number_format(abs($netPiutangHutang), 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- 3 Mini KPI Sub-Boxes (Responsive 1 col mobile, 3 col sm+) -->
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Sisa Piutang</p>
                            <p class="text-base sm:text-lg font-bold text-[#C4FF0D] mt-1">Rp {{ number_format($sisaPiutang, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">Kasbon pelanggan</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Sisa Hutang</p>
                            <p class="text-base sm:text-lg font-bold text-[#C084FC] mt-1">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">Tagihan supplier</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Sudah Lunas</p>
                            <p class="text-base sm:text-lg font-bold text-white mt-1">{{ $cntLunas }} catatan</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">Dari {{ $debtList->count() }} total</p>
                        </div>
                    </div>

                    <!-- Interactive Kasbon & Hutang Strip -->
                    <div class="mt-6">
                        <div id="bento-pnl-strip" class="flex items-end gap-1.5" style="height: 56px;">
                            @if ($debtList->isNotEmpty())
                                @foreach ($debtList->take(16) as $d)
                                    @php
                                        $hPct = max(22, min(100, round(($d->jumlah / $maxDebtVal) * 100)));
                                        $isRec = $d->isReceivable();
                                    @endphp
                                    <button type="button"
                                            data-pnl-label="{{ $d->nama_pihak }} ({{ $d->jenis }}) · Sisa Rp {{ number_format($d->remainingAmount(), 0, ',', '.') }}"
                                            data-base-opacity="0.85"
                                            class="flex-1 rounded-[4px] transition-opacity focus:outline-none"
                                            style="height: {{ $hPct }}%; background: {{ $isRec ? '#C4FF0D' : '#C084FC' }}; opacity: 0.85;"></button>
                                @endforeach
                            @else
                                @foreach ([40, 65, 50, 80, 60, 90] as $dH)
                                    <span class="flex-1 rounded-[4px] bg-[#C4FF0D]/40" style="height: {{ $dH }}%;"></span>
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-[#9AA68E]">
                            <span>Daftar Catatan</span>
                            <span id="bento-pnl-readout" data-default="Hover bar untuk cek sisa tagihan" class="text-[#F4F7F0] font-medium truncate max-w-[280px]">Hover bar untuk cek sisa tagihan</span>
                            <span>Status Live</span>
                        </div>
                    </div>
                </section>

                <!-- 2. DUE DATE RADAR & PROPORTION TILE (col-span-1) -->
                <section id="bento-allocation-tile" class="bento-tile justify-between">
                    <div>
                        <header class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-[#9AA68E]">Radar Jatuh Tempo</h3>
                            <span class="text-xs text-amber-300 font-semibold">Pengingat</span>
                        </header>

                        <div class="mt-3.5 flex h-7 gap-1">
                            <span data-alloc-segment class="rounded-[6px] transition-all duration-300"
                                  style="flex-basis: {{ max(18, $pctPiutang) }}%; background: #C4FF0D;"></span>
                            <span data-alloc-segment class="rounded-[6px] transition-all duration-300"
                                  style="flex-basis: {{ max(18, $pctHutang) }}%; background: #C084FC;"></span>
                        </div>
                        <div class="mt-2 flex justify-between text-xs text-[#9AA68E]">
                            <span class="text-[#C4FF0D] font-medium">Piutang {{ $pctPiutang }}%</span>
                            <span class="text-[#C084FC] font-medium">Hutang {{ $pctHutang }}%</span>
                        </div>

                        <div class="mt-4 space-y-2.5">
                            @forelse ($jatuhTempoList as $jt)
                                <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs sm:text-sm font-semibold text-white truncate">{{ $jt->nama_pihak }}</p>
                                        <span class="text-xs font-semibold {{ $jt->isOverdue() ? 'text-rose-400' : 'text-amber-300' }}">
                                            {{ $jt->jatuh_tempo?->format('d M Y') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#9AA68E] mt-1">
                                        {{ $jt->jenis }} · Sisa <span class="text-white font-semibold">Rp {{ number_format($jt->remainingAmount(), 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            @empty
                                <p class="text-xs sm:text-sm text-[#9AA68E] py-6 text-center">Tidak ada tagihan jatuh tempo terdekat.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3.5 border-t border-white/[0.08] flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-[#9AA68E]">Tagihan aktif</span>
                        <span class="text-white font-semibold">{{ $cntBelumLunas }} pihak</span>
                    </div>
                </section>

                <!-- 3. SOLID LIME ACCENT TILE: Kolektibilitas Kasbon (col-span-1) -->
                <section class="bento-tile-accent justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-sm font-bold opacity-85">Tingkat Pelunasan</h3>
                        </header>

                        <p class="mt-4 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em]">
                            {{ number_format($kolektibilitasPct, 1) }}<span class="text-2xl">%</span>
                        </p>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Rasio Piutang Pelanggan Tertagih</p>

                        <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                <span class="opacity-80">Piutang Cair</span>
                                <span>Rp {{ number_format($piutangDibayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ min(100, max(8, $kolektibilitasPct)) }}%;"></div>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <dt class="opacity-80">Total Piutang</dt>
                                <dd class="font-bold">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="opacity-80">Total Hutang</dt>
                                <dd class="font-bold">Rp {{ number_format($totalHutang, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-[#152208]/15">
                        <a href="#form-hutang"
                           class="block w-full text-center rounded-2xl py-3 text-xs sm:text-sm font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            + Catat Kasbon Baru
                        </a>
                    </div>
                </section>

            </div>

            <!-- ══════════════════════════════════════════════════════════
                 ROW 2: COMPOSER FORM + INTERACTIVE LEDGER TABLE (12-Column Responsive Split)
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">

                <!-- 4. COMPOSER FORM TILE (4/12 on desktop, full width on mobile/tablet) -->
                @php
                    $isCreateOld = old('action') !== 'update';
                @endphp
                <section id="form-hutang" class="bento-tile xl:col-span-4 xl:sticky xl:top-6 justify-between">
                    <div>
                        <header class="mb-5 flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                + Catat Kasbon / Hutang
                            </h3>
                            <span class="text-xs font-semibold uppercase px-2.5 py-1 rounded-full bg-white/[0.06] text-[#9AA68E]">
                                Baru
                            </span>
                        </header>

                        <form method="POST" action="/hutang-piutang" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2 gap-3.5 items-end">
                            @csrf
                            <input type="hidden" name="action" value="tambah">

                            <div class="sm:col-span-2 xl:col-span-1 2xl:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jenis Catatan</label>
                                <select name="jenis" class="{{ $inputClass }}">
                                    <option value="Piutang" {{ ($isCreateOld ? old('jenis', 'Piutang') : 'Piutang') === 'Piutang' ? 'selected' : '' }}>Piutang Pelanggan (Kasbon)</option>
                                    <option value="Hutang"  {{ ($isCreateOld ? old('jenis', '') : '') === 'Hutang' ? 'selected' : '' }}>Hutang ke Supplier</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Nama Pihak *</label>
                                <input type="text" name="nama_pihak" required minlength="2" maxlength="100"
                                    placeholder="Contoh: Warung Kopi Senja"
                                    value="{{ $isCreateOld ? old('nama_pihak', '') : '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">No. WhatsApp / HP</label>
                                <input type="tel" name="no_hp" maxlength="16" inputmode="numeric"
                                    pattern="(\+62|62|0)[0-9]{8,13}"
                                    title="Nomor HP diawali 0, 62, atau +62 dan berisi 9–16 digit angka"
                                    placeholder="08123456789"
                                    value="{{ $isCreateOld ? old('no_hp', '') : '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Total Nominal (Rp) *</label>
                                <input type="number" name="jumlah" required min="1" max="10000000000" step="1" placeholder="0"
                                    value="{{ $isCreateOld ? old('jumlah', '') : '' }}"
                                    class="no-spinner {{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Sudah Dibayar (Rp)</label>
                                <input type="number" name="dibayar" min="0" max="10000000000" step="1" placeholder="0"
                                    value="{{ $isCreateOld ? old('dibayar', 0) : 0 }}"
                                    class="no-spinner {{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Tanggal Catat *</label>
                                <input type="date" name="tanggal" required max="{{ date('Y-m-d') }}"
                                    value="{{ $isCreateOld ? old('tanggal', date('Y-m-d')) : date('Y-m-d') }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jatuh Tempo</label>
                                <input type="date" name="jatuh_tempo"
                                    value="{{ $isCreateOld ? old('jatuh_tempo', date('Y-m-d', strtotime('+14 days'))) : date('Y-m-d', strtotime('+14 days')) }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 xl:col-span-1 2xl:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Keterangan Barang / Nota</label>
                                <input type="text" name="keterangan" maxlength="255"
                                    placeholder="Contoh: Kasbon 10 pack Kopi Arabika"
                                    value="{{ $isCreateOld ? old('keterangan', '') : '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 xl:col-span-1 2xl:col-span-2 pt-2 flex flex-col gap-2.5">
                                <button type="submit" class="w-full btn-lime">
                                    + Simpan Catatan
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 5. INTERACTIVE LEDGER TABLE TILE (8/12 on desktop) -->
                <section class="bento-tile xl:col-span-8 !p-0 !overflow-hidden justify-between">
                    <div>
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-base sm:text-lg font-bold text-white">Daftar Kasbon Pelanggan &amp; Hutang Supplier</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Pantau progres cicilan, jatuh tempo, atau klik Edit untuk mencatat pembayaran.</p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
                                <input type="text"
                                       data-bento-search="debt-table-body"
                                       placeholder="Cari nama / keterangan..."
                                       class="px-4 py-2 rounded-full bg-[#181F11] border border-white/12 text-xs sm:text-sm text-white placeholder:text-[#9AA68E]/60 outline-none w-full sm:flex-1 lg:w-52">
                                <div class="bento-pills self-start sm:self-auto" data-bento-filter-group="debt-table-body" role="radiogroup" aria-label="Filter Jenis">
                                    <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                    <button type="button" data-filter-val="Piutang" class="bento-pill">Piutang</button>
                                    <button type="button" data-filter-val="Hutang" class="bento-pill">Hutang</button>
                                </div>
                            </div>
                        </header>

                        @if ($debtList->isEmpty())
                            <p class="text-center text-[#9AA68E] text-sm py-16">Belum ada catatan kasbon atau hutang.</p>
                        @else
                            <div class="overflow-x-auto w-full">
                                <table class="bento-table text-sm min-w-[720px]">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Pihak &amp; Keterangan</th>
                                            <th class="text-left">Jenis</th>
                                            <th class="text-right">Total &amp; Sisa</th>
                                            <th class="text-center">Progres Bayar</th>
                                            <th class="text-center">Jatuh Tempo</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="debt-table-body">
                                        @foreach ($debtList as $d)
                                            @php
                                                $pctPay = $d->progressPercentage();
                                                $pctBarStyle = 'width:' . $pctPay . '%';
                                            @endphp
                                            <tr data-filter-item data-filter-tag="{{ $d->jenis }}"
                                                class="{{ (string) $d->id === (string) $editId ? 'bg-[#C4FF0D]/[0.07]' : '' }}">
                                                <td>
                                                    <p class="font-semibold text-white">{{ $d->nama_pihak }}</p>
                                                    <p class="text-xs text-[#9AA68E] truncate max-w-[220px] mt-0.5">
                                                        {{ $d->keterangan ?: 'Tanpa catatan' }}
                                                        @if ($d->no_hp)
                                                            · {{ $d->no_hp }}
                                                        @endif
                                                    </p>
                                                </td>
                                                <td>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                                        {{ $d->isReceivable() ? 'bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30' : 'bg-[#C084FC]/15 text-[#C084FC] border border-[#C084FC]/30' }}">
                                                        {{ $d->jenis }}
                                                    </span>
                                                </td>
                                                <td class="text-right whitespace-nowrap">
                                                    <p class="font-bold text-white">Rp {{ number_format($d->jumlah, 0, ',', '.') }}</p>
                                                    <p class="text-xs {{ $d->remainingAmount() > 0 ? 'text-amber-300' : 'text-[#C4FF0D]' }}">
                                                        Sisa: Rp {{ number_format($d->remainingAmount(), 0, ',', '.') }}
                                                    </p>
                                                </td>
                                                <td class="text-center">
                                                    <div class="flex flex-col items-center gap-1.5">
                                                        <span class="text-xs font-medium text-[#9AA68E]">{{ $pctPay }}%</span>
                                                        <div class="w-24 h-2 bg-[#181F11] rounded-full overflow-hidden border border-white/[0.06]">
                                                            <div class="h-full {{ $d->isPaid() ? 'bg-[#C4FF0D]' : 'bg-[#C084FC]' }} rounded-full" style="{{ $pctBarStyle }}"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center whitespace-nowrap text-xs sm:text-sm">
                                                    @if ($d->jatuh_tempo)
                                                        <span class="{{ $d->isOverdue() ? 'text-rose-400 font-semibold' : 'text-[#9AA68E]' }}">
                                                            {{ $d->jatuh_tempo->format('d M Y') }}
                                                        </span>
                                                    @else
                                                        <span class="text-[#9AA68E]">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center whitespace-nowrap">
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $d->statusBadgeClass() }}">
                                                        {{ $d->status }}
                                                    </span>
                                                </td>
                                                <td class="text-center whitespace-nowrap">
                                                    <div class="inline-grid grid-cols-[56px_68px] items-center gap-2">
                                                        @if (! $d->isPaid())
                                                            <button type="button"
                                                                    onclick="openEditDebtModal(this)"
                                                                    data-id="{{ $d->id }}"
                                                                    data-jenis="{{ $d->jenis }}"
                                                                    data-nama="{{ $d->nama_pihak }}"
                                                                    data-hp="{{ $d->no_hp ?? '' }}"
                                                                    data-jumlah="{{ $d->jumlah }}"
                                                                    data-dibayar="{{ $d->dibayar }}"
                                                                    data-tanggal="{{ $d->tanggal->format('Y-m-d') }}"
                                                                    data-jatuh-tempo="{{ $d->jatuh_tempo?->format('Y-m-d') ?? '' }}"
                                                                    data-keterangan="{{ $d->keterangan ?? '' }}"
                                                                    class="w-full py-1.5 rounded-lg bg-[#C4FF0D]/12 text-[#C4FF0D] hover:bg-[#C4FF0D] hover:text-[#152208] border border-[#C4FF0D]/30 text-xs font-semibold transition cursor-pointer">
                                                                Edit
                                                            </button>
                                                        @else
                                                            <span aria-hidden="true"></span>
                                                        @endif
                                                        <form method="POST" action="/hutang-piutang">
                                                            @csrf
                                                            <input type="hidden" name="action" value="hapus">
                                                            <input type="hidden" name="id" value="{{ $d->id }}">
                                                            <button type="button"
                                                                    data-nama="{{ $d->nama_pihak }}"
                                                                    onclick="konfirmasiHapusDebt(this)"
                                                                    class="w-full py-1.5 rounded-lg bg-rose-500/12 text-rose-400 hover:bg-rose-500 hover:text-white border border-rose-500/30 text-xs font-semibold transition cursor-pointer">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="px-5 sm:px-6 py-4 border-t border-white/[0.06] bg-[#151B0F] flex flex-wrap items-center justify-between gap-2 text-xs text-[#9AA68E]">
                        <span>Total {{ $debtList->count() }} catatan kasbon &amp; hutang</span>
                        <span class="text-[#C4FF0D] font-medium">Receivable &amp; Payable Ledger</span>
                    </div>
                </section>

            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             WEB3BENTO EDIT MODAL OVERLAY (CRUD UPDATE TANPA RELOAD)
             ══════════════════════════════════════════════════════════ -->
        <div id="edit-debt-modal"
             data-bento-portal-modal="true"
             role="dialog"
             aria-modal="true"
             aria-labelledby="edit-debt-modal-title"
             class="fixed inset-0 z-[9990] hidden items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-md">
            <div id="edit-debt-modal-panel"
                 class="relative w-full max-w-xl rounded-[26px] bg-[#12170D] border border-[#C4FF0D]/35 shadow-[0_28px_90px_rgba(0,0,0,0.85)] overflow-hidden max-h-[90vh] flex flex-col transform transition-all duration-200 scale-95 opacity-0">
                <!-- Glow Bar Atas -->
                <div class="h-1 w-full shrink-0 bg-gradient-to-r from-[#C4FF0D] via-[#8BC34A] to-[#C084FC]"></div>

                <!-- Header Modal -->
                <div class="px-5 py-4 border-b border-white/[0.08] flex items-start justify-between gap-4 shrink-0">
                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C4FF0D]/15 border border-[#C4FF0D]/30 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-[#C4FF0D]">
                            Update Catatan
                        </span>
                        <h3 id="edit-debt-modal-title" class="text-base sm:text-lg font-bold text-white mt-1.5">
                            Edit Kasbon &amp; Catat Cicilan
                        </h3>
                        <p id="edit-debt-subtitle" class="text-xs text-[#9AA68E] mt-0.5 truncate">
                            Perbarui detail tagihan atau tambahkan nominal pembayaran cicilan.
                        </p>
                    </div>
                    <button type="button"
                            onclick="closeEditDebtModal()"
                            aria-label="Tutup modal edit"
                            class="size-8 rounded-full bg-[#181F11] border border-white/10 text-[#9AA68E] hover:text-white hover:border-[#C4FF0D]/50 flex items-center justify-center text-xl leading-none transition cursor-pointer shrink-0">
                        &times;
                    </button>
                </div>

                <!-- Body Form (Scrollable di layar kecil) -->
                <div class="p-5 overflow-y-auto space-y-4">
                    @if ($editDebt && (! empty($errorsList) || ($flash && ($flash['type'] ?? '') === 'danger')))
                        <div class="rounded-2xl bg-rose-500/10 border border-rose-500/35 px-4 py-3 text-xs sm:text-sm text-rose-300">
                            @if (! empty($errorsList))
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach ($errorsList as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ $flash['msg'] ?? '' }}
                            @endif
                        </div>
                    @endif

                    <!-- Live Preview Progres Pelunasan -->
                    <div class="rounded-2xl bg-[#181F11] border border-white/[0.08] px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] uppercase tracking-wider text-[#9AA68E] font-medium">Estimasi Sisa Tagihan</p>
                            <p id="edit-live-sisa" class="text-lg font-bold text-white mt-0.5 tabular-nums">Rp 0</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span id="edit-live-status" class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-300 border border-amber-400/30">
                                Dicicil
                            </span>
                            <button type="button"
                                    onclick="fillEditDebtPaidFull()"
                                    class="px-3 py-1.5 rounded-xl bg-[#C4FF0D]/15 hover:bg-[#C4FF0D] text-[#C4FF0D] hover:text-[#152208] border border-[#C4FF0D]/30 text-xs font-semibold transition cursor-pointer">
                                Isi Lunas 100%
                            </button>
                        </div>
                    </div>

                    <form id="edit-debt-form" method="POST" action="/hutang-piutang" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 items-end">
                        @csrf
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit-debt-id" value="{{ $editDebt?->id ?? '' }}">

                        <div class="sm:col-span-2">
                            <label for="edit-debt-jenis" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jenis Catatan</label>
                            <select id="edit-debt-jenis" name="jenis" class="{{ $inputClass }}">
                                <option value="Piutang">Piutang Pelanggan (Kasbon)</option>
                                <option value="Hutang">Hutang ke Supplier</option>
                            </select>
                        </div>

                        <div>
                            <label for="edit-debt-nama" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Nama Pihak *</label>
                            <input type="text" id="edit-debt-nama" name="nama_pihak" required minlength="2" maxlength="100"
                                   placeholder="Nama pelanggan / supplier" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="edit-debt-hp" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">No. WhatsApp / HP</label>
                            <input type="tel" id="edit-debt-hp" name="no_hp" maxlength="16" inputmode="numeric"
                                   pattern="(\+62|62|0)[0-9]{8,13}"
                                   title="Nomor HP diawali 0, 62, atau +62 dan berisi 9–16 digit angka"
                                   placeholder="08123456789" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="edit-debt-jumlah" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Total Nominal (Rp) *</label>
                            <input type="number" id="edit-debt-jumlah" name="jumlah" required min="1" max="10000000000" step="1"
                                   oninput="updateEditDebtPreview()" class="no-spinner {{ $inputClass }}">
                        </div>

                        <div>
                            <label for="edit-debt-dibayar" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Sudah Dibayar (Rp)</label>
                            <input type="number" id="edit-debt-dibayar" name="dibayar" min="0" max="10000000000" step="1"
                                   oninput="updateEditDebtPreview()" class="no-spinner {{ $inputClass }}">
                        </div>

                        <div>
                            <label for="edit-debt-tanggal" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Tanggal Catat *</label>
                            <input type="date" id="edit-debt-tanggal" name="tanggal" required max="{{ date('Y-m-d') }}"
                                   class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="edit-debt-jatuh-tempo" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jatuh Tempo</label>
                            <input type="date" id="edit-debt-jatuh-tempo" name="jatuh_tempo"
                                   class="{{ $inputClass }}">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="edit-debt-keterangan" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Keterangan Barang / Nota</label>
                            <input type="text" id="edit-debt-keterangan" name="keterangan" maxlength="255"
                                   placeholder="Contoh: Cicilan ke-2 sudah masuk" class="{{ $inputClass }}">
                        </div>

                        <div class="sm:col-span-2 pt-3 border-t border-white/[0.08] flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
                            <button type="button" onclick="closeEditDebtModal()" class="w-full sm:w-auto btn-ghost-dark px-6 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto btn-lime px-7 cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($editDebt)
            <div id="edit-debt-autoload" hidden
                 data-id="{{ $editDebt->id }}"
                 data-jenis="{{ old('action') === 'update' ? old('jenis', $editDebt->jenis) : $editDebt->jenis }}"
                 data-nama="{{ old('action') === 'update' ? old('nama_pihak', $editDebt->nama_pihak) : $editDebt->nama_pihak }}"
                 data-hp="{{ old('action') === 'update' ? old('no_hp', $editDebt->no_hp ?? '') : ($editDebt->no_hp ?? '') }}"
                 data-jumlah="{{ old('action') === 'update' ? old('jumlah', $editDebt->jumlah) : $editDebt->jumlah }}"
                 data-dibayar="{{ old('action') === 'update' ? old('dibayar', $editDebt->dibayar) : $editDebt->dibayar }}"
                 data-tanggal="{{ old('action') === 'update' ? old('tanggal', $editDebt->tanggal->format('Y-m-d')) : $editDebt->tanggal->format('Y-m-d') }}"
                 data-jatuh-tempo="{{ old('action') === 'update' ? old('jatuh_tempo', $editDebt->jatuh_tempo?->format('Y-m-d') ?? '') : ($editDebt->jatuh_tempo?->format('Y-m-d') ?? '') }}"
                 data-keterangan="{{ old('action') === 'update' ? old('keterangan', $editDebt->keterangan ?? '') : ($editDebt->keterangan ?? '') }}"></div>
        @endif

        <script>
            (function () {
                function fmtRp(val) {
                    return 'Rp ' + Math.max(0, Math.round(Number(val) || 0)).toLocaleString('id-ID');
                }

                window.updateEditDebtPreview = function () {
                    const jumlahEl = document.getElementById('edit-debt-jumlah');
                    const dibayarEl = document.getElementById('edit-debt-dibayar');
                    const sisaEl = document.getElementById('edit-live-sisa');
                    const statusEl = document.getElementById('edit-live-status');
                    if (!jumlahEl || !dibayarEl || !sisaEl || !statusEl) return;

                    const jumlah = Math.max(0, parseInt(jumlahEl.value || '0', 10));
                    const dibayar = Math.max(0, parseInt(dibayarEl.value || '0', 10));
                    const sisa = Math.max(0, jumlah - dibayar);

                    sisaEl.textContent = fmtRp(sisa);

                    if (jumlah > 0 && dibayar > jumlah) {
                        statusEl.textContent = 'Melebihi Total!';
                        statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/15 text-rose-300 border border-rose-500/35';
                    } else if (jumlah > 0 && dibayar >= jumlah) {
                        statusEl.textContent = 'Lunas (100%)';
                        statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/35';
                    } else if (dibayar > 0) {
                        const pct = jumlah > 0 ? Math.round((dibayar / jumlah) * 100) : 0;
                        statusEl.textContent = 'Dicicil (' + pct + '%)';
                        statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-300 border border-amber-400/30';
                    } else {
                        statusEl.textContent = 'Belum Lunas';
                        statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/15 text-rose-300 border border-rose-500/30';
                    }
                };

                window.fillEditDebtPaidFull = function () {
                    const jumlahEl = document.getElementById('edit-debt-jumlah');
                    const dibayarEl = document.getElementById('edit-debt-dibayar');
                    if (jumlahEl && dibayarEl && jumlahEl.value) {
                        dibayarEl.value = jumlahEl.value;
                        window.updateEditDebtPreview();
                    }
                };

                window.openEditDebtModal = function (el) {
                    const modal = document.getElementById('edit-debt-modal');
                    const panel = document.getElementById('edit-debt-modal-panel');
                    if (!modal || !panel || !el) return;

                    const d = el.dataset;
                    document.getElementById('edit-debt-id').value = d.id || '';
                    document.getElementById('edit-debt-nama').value = d.nama || '';
                    document.getElementById('edit-debt-hp').value = d.hp || '';
                    document.getElementById('edit-debt-jumlah').value = d.jumlah || '';
                    document.getElementById('edit-debt-dibayar').value = d.dibayar || '0';
                    document.getElementById('edit-debt-tanggal').value = d.tanggal || '';
                    document.getElementById('edit-debt-jatuh-tempo').value = d.jatuhTempo || '';
                    document.getElementById('edit-debt-keterangan').value = d.keterangan || '';

                    const jenisEl = document.getElementById('edit-debt-jenis');
                    if (jenisEl) {
                        jenisEl.value = d.jenis || 'Piutang';
                        jenisEl.dispatchEvent(new Event('bento:sync'));
                    }

                    const sub = document.getElementById('edit-debt-subtitle');
                    if (sub) {
                        sub.textContent = 'Mengubah catatan: ' + (d.nama || '-') + ' (' + (d.jenis || 'Piutang') + ')';
                    }

                    window.updateEditDebtPreview();

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');

                    requestAnimationFrame(function () {
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');
                    });
                };

                window.closeEditDebtModal = function () {
                    const modal = document.getElementById('edit-debt-modal');
                    const panel = document.getElementById('edit-debt-modal-panel');
                    if (!modal || !panel) return;

                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    document.body.classList.remove('overflow-hidden');

                    setTimeout(function () {
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                    }, 180);
                };

                // Pindahkan modal langsung ke <body> agar position:fixed selalu 100% di tengah viewport
                const modalEl = document.getElementById('edit-debt-modal');
                if (modalEl) {
                    document.querySelectorAll('body > #edit-debt-modal').forEach(function (oldEl) {
                        if (oldEl !== modalEl) oldEl.remove();
                    });
                    if (modalEl.parentElement !== document.body) {
                        document.body.appendChild(modalEl);
                    }
                    if (!modalEl.dataset.bound) {
                        modalEl.dataset.bound = 'true';
                        modalEl.addEventListener('click', function (e) {
                            if (e.target === modalEl) {
                                window.closeEditDebtModal();
                            }
                        });
                    }
                }

                if (!window._editDebtEscBound) {
                    window._editDebtEscBound = true;
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') {
                            if (typeof Swal !== 'undefined' && Swal.isVisible()) return;
                            const m = document.getElementById('edit-debt-modal');
                            if (m && !m.classList.contains('hidden')) {
                                window.closeEditDebtModal();
                            }
                        }
                    });
                }

                const autoload = document.getElementById('edit-debt-autoload');
                if (autoload) {
                    setTimeout(function () {
                        window.openEditDebtModal(autoload);
                    }, 80);
                }
            })();
        </script>

        <!-- Konfirmasi hapus (SweetAlert) -->
        <script>
            window.konfirmasiHapusDebt = function (btn) {
                const form = btn.closest('form');
                if (!form) return;

                const nama = btn.dataset.nama || '';
                const submitForm = function () {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                };

                if (typeof Swal === 'undefined') {
                    if (confirm('Hapus catatan ' + nama + '? Data yang dihapus tidak dapat dikembalikan.')) {
                        submitForm();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Hapus Catatan?',
                    text: 'Hapus catatan ' + nama + '? Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    background: '#12170D',
                    color: '#F4F7F0',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#252f1b',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) submitForm();
                });
            };
        </script>

        <!-- Notifikasi: sukses = toast 3 detik, selain itu = dialog -->
        @if ($notif)
            <div id="notif-data" data-notif="{{ json_encode($notif) }}" hidden></div>
            <script>
                (function () {
                    const notifEl = document.getElementById('notif-data');
                    if (!notifEl || typeof Swal === 'undefined') return;

                    const notif = Object.assign(
                        { background: '#12170D', color: '#F4F7F0', confirmButtonColor: '#252f1b' },
                        JSON.parse(notifEl.dataset.notif)
                    );

                    if (notif.icon === 'success') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            iconColor: '#C4FF0D',
                            title: notif.text || notif.title,
                            background: '#12170D',
                            color: '#F4F7F0',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: { popup: 'swal-toast-bento' },
                            didOpen: function (toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                    } else {
                        Swal.fire(notif);
                    }
                })();
            </script>
        @endif
    </main>
</body>
</html>
