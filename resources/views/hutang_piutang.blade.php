<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hutang &amp; Piutang | AlpetBizz</title>
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
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Hutang Piutang Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Hutang &amp; Piutang (Kasbon)</h2>
                </div>
            </nav>

            <!-- Flash Message -->
            @if ($flash)
            <div id="flash"
                 role="status"
                 class="flex items-start justify-between gap-3 rounded-[22px] border px-5 py-4 text-sm transition-opacity duration-300
                        {{ ($flash['type'] ?? '') === 'success'
                            ? 'bg-[#C4FF0D]/10 border-[#C4FF0D]/35 text-[#C4FF0D]'
                            : 'bg-rose-500/10 border-rose-500/35 text-rose-300' }}">
                <span>{{ $flash['msg'] ?? '' }}</span>
                <button type="button" onclick="document.getElementById('flash').remove()"
                        class="font-semibold leading-none text-lg" aria-label="Tutup">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('flash');
                    if (el) { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 300); }
                }, 4000);
            </script>
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
                <section id="form-hutang" class="bento-tile xl:col-span-4 justify-between {{ $editDebt ? 'border-[#C4FF0D]/60 ring-1 ring-[#C4FF0D]/25' : '' }}">
                    <div>
                        <header class="mb-5 flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                {{ $editDebt ? 'Edit Catatan / Cicilan' : '+ Catat Kasbon / Hutang' }}
                            </h3>
                            <span class="text-xs font-semibold uppercase px-2.5 py-1 rounded-full bg-white/[0.06] text-[#9AA68E]">
                                {{ $editDebt ? 'Update' : 'Baru' }}
                            </span>
                        </header>

                        <form method="POST" action="/hutang-piutang" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @csrf
                            <input type="hidden" name="action" value="{{ $editDebt ? 'update' : 'tambah' }}">
                            @if ($editDebt)
                                <input type="hidden" name="id" value="{{ $editDebt->id }}">
                            @endif

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jenis Catatan</label>
                                <select name="jenis" class="{{ $inputClass }}">
                                    <option value="Piutang" {{ ($editDebt?->jenis ?? 'Piutang') === 'Piutang' ? 'selected' : '' }}>Piutang Pelanggan (Kasbon)</option>
                                    <option value="Hutang"  {{ ($editDebt?->jenis ?? '') === 'Hutang' ? 'selected' : '' }}>Hutang ke Supplier</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Nama Pelanggan / Supplier *</label>
                                <input type="text" name="nama_pihak" required maxlength="100"
                                    placeholder="Contoh: Warung Kopi Senja"
                                    value="{{ old('nama_pihak', $editDebt?->nama_pihak ?? '') }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">No. WhatsApp / HP</label>
                                <input type="text" name="no_hp" maxlength="30"
                                    placeholder="08123456789"
                                    value="{{ old('no_hp', $editDebt?->no_hp ?? '') }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Total Nominal (Rp) *</label>
                                <input type="number" name="jumlah" required min="1" placeholder="0"
                                    value="{{ old('jumlah', $editDebt?->jumlah ?? '') }}"
                                    class="no-spinner {{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Sudah Dibayar (Rp)</label>
                                <input type="number" name="dibayar" min="0" placeholder="0"
                                    value="{{ old('dibayar', $editDebt?->dibayar ?? 0) }}"
                                    class="no-spinner {{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Tanggal Catat *</label>
                                <input type="date" name="tanggal" required
                                    value="{{ $editDebt ? $editDebt->tanggal->format('Y-m-d') : date('Y-m-d') }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jatuh Tempo</label>
                                <input type="date" name="jatuh_tempo"
                                    value="{{ $editDebt?->jatuh_tempo?->format('Y-m-d') ?? date('Y-m-d', strtotime('+14 days')) }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Keterangan Barang / Nota</label>
                                <input type="text" name="keterangan" maxlength="255"
                                    placeholder="Contoh: Kasbon 10 pack Kopi Arabika"
                                    value="{{ old('keterangan', $editDebt?->keterangan ?? '') }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 pt-2 flex flex-col gap-2.5">
                                <button type="submit" class="w-full btn-lime">
                                    {{ $editDebt ? 'Simpan Perubahan' : '+ Simpan Catatan' }}
                                </button>
                                @if ($editDebt)
                                    <a href="/hutang-piutang" class="w-full btn-ghost-dark">Batal Edit</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 5. INTERACTIVE LEDGER TABLE TILE (8/12 on desktop) -->
                <section class="bento-tile xl:col-span-8 !p-0 overflow-hidden justify-between">
                    <div>
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">Daftar Kasbon Pelanggan &amp; Hutang Supplier</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Pantau progres cicilan, jatuh tempo, atau klik Lunasi untuk pelunasan instan.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="text"
                                       data-bento-search="debt-table-body"
                                       placeholder="Cari nama / keterangan..."
                                       class="px-4 py-2 rounded-full bg-[#181F11] border border-white/12 text-xs sm:text-sm text-white placeholder:text-[#9AA68E]/60 outline-none w-full sm:w-56">
                                <div class="bento-pills" data-bento-filter-group="debt-table-body" role="radiogroup" aria-label="Filter Jenis">
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
                                                    <div class="flex items-center justify-center gap-3">
                                                        @if (! $d->isPaid())
                                                            <form method="POST" action="/hutang-piutang" title="Tandai langsung Lunas">
                                                                @csrf
                                                                <input type="hidden" name="action" value="lunasi">
                                                                <input type="hidden" name="id" value="{{ $d->id }}">
                                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#C4FF0D]/15 text-[#C4FF0D] hover:bg-[#C4FF0D] hover:text-[#152208] text-xs font-semibold transition">
                                                                    ✓ Lunasi
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <a href="/hutang-piutang?edit={{ $d->id }}#form-hutang" class="text-xs sm:text-sm text-[#C4FF0D] hover:underline font-semibold transition">Edit</a>
                                                        <form method="POST" action="/hutang-piutang" onsubmit="return confirm('Hapus catatan ini?')">
                                                            @csrf
                                                            <input type="hidden" name="action" value="hapus">
                                                            <input type="hidden" name="id" value="{{ $d->id }}">
                                                            <button type="submit" class="text-xs sm:text-sm text-rose-400 hover:text-rose-300 font-semibold transition">Hapus</button>
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

                    <div class="px-6 py-4 border-t border-white/[0.06] bg-[#151B0F] flex items-center justify-between text-xs text-[#9AA68E]">
                        <span>Total {{ $debtList->count() }} catatan kasbon &amp; hutang</span>
                        <span class="text-[#C4FF0D] font-medium">Receivable &amp; Payable Ledger</span>
                    </div>
                </section>

            </div>
        </div>
    </main>
</body>
</html>
