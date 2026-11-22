<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi | AlpetBizz</title>
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
        $totalMasuk = (int) $transaksiList->where('jenis', 'Pemasukan')->sum('jumlah');
        $totalKeluar = (int) $transaksiList->where('jenis', 'Pengeluaran')->sum('jumlah');
        $cntMasuk = $transaksiList->where('jenis', 'Pemasukan')->count();
        $cntKeluar = $transaksiList->where('jenis', 'Pengeluaran')->count();
        $cntLinked = $transaksiList->whereNotNull('product_id')->count();
        $selisih = $totalMasuk - $totalKeluar;
        $totalVolume = max($totalMasuk + $totalKeluar, 1);
        $pctMasuk = $totalMasuk > 0 ? round(($totalMasuk / $totalVolume) * 100, 1) : 50.0;
        $pctKeluar = $totalKeluar > 0 ? round(($totalKeluar / $totalVolume) * 100, 1) : 50.0;
        $marginTrx = $totalMasuk > 0 ? round(($selisih / $totalMasuk) * 100, 1) : 0.0;
        $avgTrx = $transaksiList->count() > 0 ? round(($totalMasuk + $totalKeluar) / $transaksiList->count()) : 0;

        $recentSlice = $transaksiList->take(20)->reverse()->values();
        $maxTrxVal = max((int) $recentSlice->max('jumlah'), 1);
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Transaksi Header">
                <div class="flex items-center gap-2.5">
                    <span class="font-mono text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Manajemen Transaksi</h2>
                </div>
            </nav>

            <!-- Flash Message -->
            @if ($flash)
            <div id="flash"
                 role="{{ in_array($flash['type'] ?? '', ['success', 'danger']) ? 'status' : 'alert' }}"
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

                <!-- 1. HERO CASHFLOW TILE (col-span-2) -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 justify-between">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#9AA68E]">Arus Kas Bersih</p>
                            <p class="mt-1.5 text-3xl sm:text-4xl font-bold leading-none tracking-[-0.03em] text-white">
                                {{ $selisih >= 0 ? '+' : '−' }}Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                            </p>
                            <p class="mt-2 text-sm text-[#9AA68E]">
                                Selisih pemasukan &amp; pengeluaran seluruh periode.
                            </p>
                        </div>
                        <span class="rounded-full px-3.5 py-1.5 text-xs sm:text-sm font-semibold"
                              style="background: {{ $selisih >= 0 ? 'rgba(196,255,13,0.15)' : 'rgba(192,132,252,0.18)' }}; color: {{ $selisih >= 0 ? '#C4FF0D' : '#C084FC' }};">
                            {{ $selisih >= 0 ? 'Surplus +' : 'Defisit ' }}{{ $marginTrx }}%
                        </span>
                    </div>

                    <!-- 3 Mini KPI Sub-Tiles (Responsive 1 col mobile, 3 col sm+) -->
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Pemasukan</p>
                            <p class="text-base sm:text-lg font-bold text-[#C4FF0D] mt-1">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">{{ $cntMasuk }} transaksi</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Pengeluaran</p>
                            <p class="text-base sm:text-lg font-bold text-[#C084FC] mt-1">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">{{ $cntKeluar }} transaksi</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Rata-rata / Trx</p>
                            <p class="text-base sm:text-lg font-bold text-white mt-1">Rp {{ number_format($avgTrx, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">{{ $cntLinked }} terhubung produk</p>
                        </div>
                    </div>

                    <!-- Interactive Cashflow Pulse Strip -->
                    <div class="mt-6">
                        <div id="bento-pnl-strip" class="flex items-end gap-1" style="height: 58px;">
                            @if ($recentSlice->isNotEmpty())
                                @foreach ($recentSlice as $item)
                                    @php
                                        $hPct = max(20, min(100, round(($item->jumlah / $maxTrxVal) * 100)));
                                        $isInc = $item->isIncome();
                                    @endphp
                                    <button type="button"
                                            data-pnl-label="{{ $item->tanggal->format('d M') }} · {{ $item->keterangan }} ({{ ($isInc ? '+' : '-') . 'Rp ' . number_format($item->jumlah, 0, ',', '.') }})"
                                            data-base-opacity="0.82"
                                            class="flex-1 rounded-[4px] transition-opacity focus:outline-none"
                                            style="height: {{ $hPct }}%; background: {{ $isInc ? '#C4FF0D' : '#C084FC' }}; opacity: 0.82;"></button>
                                @endforeach
                            @else
                                @foreach ([35, 55, 45, 70, 60, 80, 50, 90, 65, 85, 75, 95] as $idx => $dH)
                                    <span class="flex-1 rounded-[4px]" style="height: {{ $dH }}%; background: {{ $idx % 3 === 1 ? '#C084FC' : '#C4FF0D' }}; opacity: 0.45;"></span>
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-[#9AA68E]">
                            <span>Terlama</span>
                            <span id="bento-pnl-readout" data-default="Hover bar untuk detail transaksi" class="text-[#F4F7F0] font-medium truncate max-w-[280px]">Hover bar untuk detail transaksi</span>
                            <span>Terbaru</span>
                        </div>
                    </div>
                </section>

                <!-- 2. ALLOCATION & RATIO TILE (col-span-1) -->
                <section id="bento-allocation-tile" class="bento-tile justify-between">
                    <header class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Proporsi Kas</h3>
                    </header>

                    <div class="my-4">
                        <p class="text-2xl font-bold text-white">Rp {{ number_format($totalMasuk + $totalKeluar, 0, ',', '.') }}</p>
                        <p class="text-xs text-[#9AA68E] mt-1">total perputaran arus kas</p>

                        <div class="mt-4 flex h-9 gap-1.5">
                            <span data-alloc-segment class="rounded-[8px] transition-all duration-300"
                                  style="flex-basis: {{ max(15, $pctMasuk) }}%; background: #C4FF0D;"></span>
                            <span data-alloc-segment class="rounded-[8px] transition-all duration-300"
                                  style="flex-basis: {{ max(15, $pctKeluar) }}%; background: #C084FC;"></span>
                        </div>

                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li data-alloc-item class="flex items-center justify-between cursor-pointer">
                                <span class="flex items-center gap-2.5 text-white">
                                    <span class="size-2.5 rounded-full bg-[#C4FF0D]"></span>
                                    Pemasukan
                                </span>
                                <span class="text-[#C4FF0D] font-semibold">{{ $pctMasuk }}%</span>
                            </li>
                            <li data-alloc-item class="flex items-center justify-between cursor-pointer">
                                <span class="flex items-center gap-2.5 text-white">
                                    <span class="size-2.5 rounded-full bg-[#C084FC]"></span>
                                    Pengeluaran
                                </span>
                                <span class="text-[#C084FC] font-semibold">{{ $pctKeluar }}%</span>
                            </li>
                        </ul>
                    </div>

                    <div class="pt-3.5 border-t border-white/[0.08] flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-[#9AA68E]">Transaksi produk</span>
                        <span class="font-semibold text-white">{{ $cntLinked }} dari {{ $transaksiList->count() }}</span>
                    </div>
                </section>

                <!-- 3. SOLID LIME ACCENT TILE (col-span-1) -->
                <section class="bento-tile-accent justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-sm font-bold opacity-85">Rasio Surplus</h3>
                        </header>

                        <p class="mt-4 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em]">
                            {{ number_format(abs($marginTrx), 1) }}<span class="text-2xl">%</span>
                        </p>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Net Retention dari Pemasukan</p>

                        <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                <span class="opacity-80">Dominasi Masuk</span>
                                <span>{{ $pctMasuk }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ min(100, max(8, $pctMasuk)) }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-[#152208]/15">
                        <div class="flex justify-between text-xs sm:text-sm mb-3">
                            <span class="opacity-80">Status Kas</span>
                            <span class="font-bold">{{ $selisih >= 0 ? 'Sehat (Surplus)' : 'Waspada (Defisit)' }}</span>
                        </div>
                        <a href="/laporan"
                           class="block w-full text-center rounded-2xl py-3 text-xs sm:text-sm font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            Analisis Grafik Bulanan →
                        </a>
                    </div>
                </section>

            </div>

            <!-- ══════════════════════════════════════════════════════════
                 ROW 2: FORM COMPOSER + INTERACTIVE TABLE (12-Column Responsive Split)
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">

                <!-- 4. FORM COMPOSER TILE (Full width on mobile/tablet, 4/12 sticky on desktop) -->
                <section id="form-transaksi" class="bento-tile xl:col-span-4 xl:sticky xl:top-6 justify-between {{ $editTrx ? 'border-[#C4FF0D]/60 ring-1 ring-[#C4FF0D]/25' : '' }}">
                    <div>
                        <header class="mb-5 flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                {{ $editTrx ? 'Edit Transaksi' : 'Catat Transaksi' }}
                            </h3>
                            <span class="text-xs font-semibold uppercase px-2.5 py-1 rounded-full bg-white/[0.06] text-[#9AA68E]">
                                {{ $editTrx ? 'Update' : 'Baru' }}
                            </span>
                        </header>

                        <form method="POST" action="/transaksi" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4">
                            @csrf
                            <input type="hidden" name="action" value="{{ $editTrx ? 'update' : 'tambah' }}">
                            @if ($editTrx)
                            <input type="hidden" name="id" value="{{ $editTrx->id }}">
                            @endif

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Tanggal Transaksi</label>
                                <input type="date" name="tanggal" required
                                    value="{{ $editTrx ? $editTrx->tanggal->format('Y-m-d') : date('Y-m-d') }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jenis Arus Kas</label>
                                <select name="jenis" class="{{ $inputClass }}">
                                    <option value="Pemasukan"   {{ ($editTrx?->jenis ?? 'Pemasukan') === 'Pemasukan'   ? 'selected' : '' }}>Pemasukan (+)</option>
                                    <option value="Pengeluaran" {{ ($editTrx?->jenis ?? '')          === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran (-)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Keterangan</label>
                                <input type="text" name="keterangan" required placeholder="Contoh: Penjualan Kopi"
                                    value="{{ $editTrx?->keterangan ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jumlah Nominal (Rp)</label>
                                <input type="number" name="jumlah" required min="1" placeholder="0"
                                    value="{{ (string) ($editTrx?->jumlah ?? '') }}"
                                    class="no-spinner {{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 xl:col-span-1">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Produk Terkait <span class="text-[#9AA68E]/65 font-normal">(opsional)</span></label>
                                <select name="produk_id" class="{{ $inputClass }}">
                                    <option value="">— Tanpa produk —</option>
                                    @foreach ($produkList as $p)
                                    <option value="{{ $p->id }}"
                                        {{ (string) ($editTrx?->product_id ?? '') === (string) $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2 xl:col-span-1 pt-2 flex flex-col sm:flex-row xl:flex-col gap-2.5">
                                <button type="submit" class="w-full btn-lime">
                                    {{ $editTrx ? 'Simpan Perubahan' : '+ Tambah Transaksi' }}
                                </button>
                                @if ($editTrx)
                                <a href="/transaksi" class="w-full btn-ghost-dark">
                                    Batal Edit
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 5. ON-LEDGER STREAM & INTERACTIVE TABLE TILE (8/12 on desktop) -->
                <section class="bento-tile xl:col-span-8 !p-0 !overflow-hidden justify-between">
                    <div>
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-base sm:text-lg font-bold text-white">Daftar Riwayat Transaksi</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Filter instan berdasarkan jenis arus kas atau kata kunci.</p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
                                <input type="text"
                                       data-bento-search="trx-table-body"
                                       placeholder="Cari keterangan / produk..."
                                       class="px-4 py-2 rounded-full bg-[#181F11] border border-white/12 text-xs sm:text-sm text-white placeholder:text-[#9AA68E]/60 outline-none w-full sm:flex-1 lg:w-52">
                                <div class="bento-pills self-start sm:self-auto" data-bento-filter-group="trx-table-body" role="radiogroup" aria-label="Filter Transaksi">
                                    <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                    <button type="button" data-filter-val="Pemasukan" class="bento-pill">Masuk</button>
                                    <button type="button" data-filter-val="Pengeluaran" class="bento-pill">Keluar</button>
                                </div>
                            </div>
                        </header>

                        @if ($transaksiList->isEmpty())
                        <div class="text-center py-16 px-4">
                            <p class="text-[#9AA68E] text-sm">Belum ada riwayat transaksi.</p>
                        </div>
                        @else
                        <div class="overflow-x-auto w-full">
                            <table class="bento-table text-sm min-w-[640px]">
                                <thead>
                                    <tr>
                                        <th class="text-left">Transaksi &amp; Tanggal</th>
                                        <th class="text-left">Produk</th>
                                        <th class="text-left">Jenis</th>
                                        <th class="text-right">Nominal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="trx-table-body">
                                    @foreach ($transaksiList as $t)
                                    <tr data-filter-item data-filter-tag="{{ $t->jenis }}"
                                        class="{{ (string) $t->id === (string) $editId ? 'bg-[#C4FF0D]/[0.07]' : '' }}">
                                        <td>
                                            <div class="flex items-center gap-3.5">
                                                <span class="grid size-10 shrink-0 place-items-center rounded-xl border border-white/10 {{ $t->isIncome() ? 'bg-[#C4FF0D]/15 text-[#C4FF0D]' : 'bg-[#C084FC]/15 text-[#C084FC]' }}">
                                                    @if ($t->isIncome())
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14m0 0-6-6m6 6 6-6"/></svg>
                                                    @else
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h12l-3-3M17 17H5l3 3"/></svg>
                                                    @endif
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-white truncate">{{ $t->keterangan }}</p>
                                                    <p class="text-xs text-[#9AA68E] mt-0.5">{{ $t->tanggal->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-[#9AA68E] text-xs sm:text-sm">
                                            @if ($t->product)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#181F11] border border-white/10 text-white text-xs font-medium">
                                                    {{ $t->product->nama }}
                                                </span>
                                            @else
                                                <span>—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                                {{ $t->isIncome() ? 'bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30' : 'bg-[#C084FC]/15 text-[#C084FC] border border-[#C084FC]/30' }}">
                                                {{ $t->jenis }}
                                            </span>
                                        </td>
                                        <td class="text-right font-bold tabular-nums whitespace-nowrap {{ $t->isIncome() ? 'text-[#C4FF0D]' : 'text-[#C084FC]' }}">
                                            {{ ($t->isIncome() ? '+' : '-') . 'Rp ' . number_format($t->jumlah, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-3.5">
                                                <a href="/transaksi?edit={{ $t->id }}#form-transaksi" class="text-xs sm:text-sm text-[#C4FF0D] hover:underline font-semibold transition">Edit</a>
                                                <form method="POST" action="/transaksi" onsubmit="return confirm('Hapus transaksi ini?')">
                                                    @csrf
                                                    <input type="hidden" name="action" value="hapus">
                                                    <input type="hidden" name="id" value="{{ $t->id }}">
                                                    <button class="text-xs sm:text-sm text-rose-400 hover:text-rose-300 font-semibold transition">Hapus</button>
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
                        <span>Menampilkan {{ $transaksiList->count() }} transaksi tercatat</span>
                        <span class="text-[#C4FF0D] font-medium">Ledger Tersinkronisasi</span>
                    </div>
                </section>

            </div>
        </div>
    </main>
</body>
</html>
