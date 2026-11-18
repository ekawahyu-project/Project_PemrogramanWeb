<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok | AlpetBizz</title>
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
        $totalUnits = (int) $produkList->sum('stok');
        $totalIn = (int) $stokLogList->where('jenis', 'Masuk')->sum('jumlah');
        $totalOut = (int) $stokLogList->where('jenis', 'Keluar')->sum('jumlah');
        $skuCount = $produkList->count();
        $lowCount = $lowStock->count();
        $healthPct = $skuCount > 0 ? round((max(0, $skuCount - $lowCount) / $skuCount) * 100, 1) : 100.0;
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Stok Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Manajemen Stok</h2>
                </div>
            </nav>

            @if ($error)
            <div class="text-sm text-rose-300 bg-rose-500/10 border border-rose-500/30 px-5 py-4 rounded-[22px] font-medium">{{ $error }}</div>
            @endif

            <!-- ══════════════════════════════════════════════════════════
                 ROW 1: WEB3BENTO 4-COLUMN ANALYTICS GRID
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <!-- 1. HERO WAREHOUSE CAPACITY TILE (col-span-2) -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 justify-between">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#9AA68E]">Total Unit Gudang</p>
                            <p class="mt-1.5 text-3xl sm:text-4xl font-bold leading-none tracking-[-0.03em] text-white">
                                {{ number_format($totalUnits, 0, ',', '.') }} <span class="text-lg font-normal text-[#9AA68E]">unit</span>
                            </p>
                            <p class="mt-2 text-sm text-[#9AA68E]">
                                Akumulasi stok fisik lintas {{ $skuCount }} SKU produk aktif.
                            </p>
                        </div>
                        <span class="rounded-full bg-[#C4FF0D]/15 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-[#C4FF0D]">
                            {{ $stokLogList->count() }} log mutasi
                        </span>
                    </div>

                    <!-- 3 Mini KPI Boxes (Responsive 1 col mobile, 3 col sm+) -->
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Total Masuk</p>
                            <p class="text-base sm:text-lg font-bold text-[#C4FF0D] mt-1">+{{ number_format($totalIn, 0, ',', '.') }} unit</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Total Keluar</p>
                            <p class="text-base sm:text-lg font-bold text-[#C084FC] mt-1">-{{ number_format($totalOut, 0, ',', '.') }} unit</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E]">Status Kritis</p>
                            <p class="text-base sm:text-lg font-bold {{ $lowCount > 0 ? 'text-amber-300' : 'text-white' }} mt-1">{{ $lowCount }} SKU</p>
                        </div>
                    </div>

                    <!-- Interactive SKU Stock Level Strip -->
                    <div class="mt-6">
                        <div id="bento-pnl-strip" class="flex items-end gap-1.5" style="height: 56px;">
                            @if ($produkList->isNotEmpty())
                                @foreach ($produkList as $p)
                                    @php
                                        $pctCap = max(18, min(100, round($p->stockProgressPercentage())));
                                    @endphp
                                    <button type="button"
                                            data-pnl-label="{{ $p->nama }} · Sisa {{ $p->stok }} {{ $p->satuan }} (Min: {{ $p->stok_min }})"
                                            data-base-opacity="0.85"
                                            class="flex-1 rounded-[4px] transition-opacity focus:outline-none"
                                            style="height: {{ $pctCap }}%; background: {{ $p->isLowStock() ? '#C084FC' : '#C4FF0D' }}; opacity: 0.85;"></button>
                                @endforeach
                            @else
                                @foreach ([50, 75, 60, 85, 95] as $dH)
                                    <span class="flex-1 rounded-[4px] bg-[#C4FF0D]/40" style="height: {{ $dH }}%;"></span>
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-[#9AA68E]">
                            <span>Kapasitas SKU</span>
                            <span id="bento-pnl-readout" data-default="Hover bar untuk detail kapasitas stok" class="text-[#F4F7F0] font-medium truncate max-w-[280px]">Hover bar untuk detail kapasitas stok</span>
                            <span>100%</span>
                        </div>
                    </div>
                </section>

                <!-- 2. SEMICIRCLE GAUGE TILE: Kesiapan Gudang (col-span-1) -->
                <section id="bento-gauge-tile" data-base-score="{{ $healthPct }}" class="bento-tile justify-between">
                    <header class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Kesiapan Gudang</h3>
                        <span class="text-xs font-semibold text-[#C4FF0D]">live</span>
                    </header>

                    <div class="flex flex-1 flex-col items-center justify-center py-2">
                        <div class="relative" style="width: 160px; height: 92px;">
                            <svg width="160" height="92" viewBox="0 0 150 86" aria-hidden="true">
                                <path d="M 13 78 A 62 62 0 0 1 137 78" fill="none" stroke="#1E2615" stroke-width="10" stroke-linecap="round" />
                                <path id="bento-gauge-arc" d="M 13 78 A 62 62 0 0 1 137 78" fill="none" stroke="#C4FF0D" stroke-width="10" stroke-linecap="round"
                                      style="transition: stroke-dashoffset 800ms cubic-bezier(0.22, 1, 0.36, 1);" />
                                <g id="bento-gauge-needle" style="transform: rotate(45deg); transform-origin: 75px 78px; transition: transform 800ms cubic-bezier(0.22, 1, 0.36, 1);">
                                    <line x1="75" y1="78" x2="75" y2="30" stroke="#F4F7F0" stroke-width="2.5" stroke-linecap="round" />
                                </g>
                                <circle cx="75" cy="78" r="5" fill="#F4F7F0" />
                            </svg>
                        </div>
                        <p class="mt-2 text-3xl font-bold leading-none tabular-nums text-white">
                            <span id="bento-gauge-value">{{ number_format($healthPct, 1) }}</span><span class="text-base text-[#9AA68E]">%</span>
                        </p>
                        <p class="mt-1.5 text-xs text-[#9AA68E]">
                            {{ $skuCount - $lowCount }} dari {{ $skuCount }} SKU aman
                        </p>
                    </div>

                    <div class="flex justify-center">
                        <div class="bento-pills" role="radiogroup" aria-label="Target Gudang">
                            <button type="button" data-gauge-tier="Slow" class="bento-pill">Min</button>
                            <button type="button" data-gauge-tier="Normal" class="bento-pill active">Normal</button>
                            <button type="button" data-gauge-tier="Fast" class="bento-pill">Max</button>
                        </div>
                    </div>
                </section>

                <!-- 3. SOLID LIME ACCENT TILE: Radar Restock (col-span-1) -->
                <section class="bento-tile-accent justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-sm font-bold opacity-85">Radar Restock</h3>
                        </header>

                        <p class="mt-4 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em]">
                            {{ $lowCount }} <span class="text-xl font-semibold">SKU</span>
                        </p>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Berada di bawah batas minimum</p>

                        <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                <span class="opacity-80">Stok Terpenuhi</span>
                                <span>{{ number_format($healthPct, 0) }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ min(100, max(10, $healthPct)) }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-[#152208]/15">
                        <a href="#form-stok"
                           class="block w-full text-center rounded-2xl py-3 text-xs sm:text-sm font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            + Catat Restock Sekarang
                        </a>
                    </div>
                </section>

            </div>

            <!-- ══════════════════════════════════════════════════════════
                 ROW 2: STOCK MOVEMENT FORM + TABLES (12-Column Responsive Split)
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">

                <!-- 4. STOCK MOVEMENT FORM TILE (4/12 on desktop, full width on mobile/tablet) -->
                <section id="form-stok" class="bento-tile xl:col-span-4 justify-between {{ $editLog ? 'border-[#C4FF0D]/60 ring-1 ring-[#C4FF0D]/25' : '' }}">
                    <div>
                        <header class="mb-5 flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                {{ $editLog ? 'Edit Catatan Mutasi' : '+ Catat Mutasi Stok' }}
                            </h3>
                            <span class="text-xs font-semibold uppercase px-2.5 py-1 rounded-full bg-white/[0.06] text-[#9AA68E]">
                                {{ $editLog ? 'Update' : 'Log' }}
                            </span>
                        </header>

                        <form method="POST" action="/stok" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4">
                            @csrf
                            <input type="hidden" name="action" value="{{ $editLog ? 'update_log' : 'catat' }}">
                            @if ($editLog)
                            <input type="hidden" name="id" value="{{ $editLog->id }}">
                            @endif

                            <div class="sm:col-span-2 xl:col-span-1">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Pilih Produk</label>
                                <select name="produk_id" required class="{{ $inputClass }}">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($produkList as $p)
                                    <option value="{{ $p->id }}" {{ (string) ($editLog?->product_id ?? '') === (string) $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} (Sisa: {{ $p->stok }} {{ $p->satuan }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jenis Mutasi</label>
                                <select name="jenis" class="{{ $inputClass }}">
                                    <option value="Masuk" {{ ($editLog?->jenis ?? 'Masuk') === 'Masuk' ? 'selected' : '' }}>Barang Masuk (Restock / Tambah)</option>
                                    <option value="Keluar" {{ ($editLog?->jenis ?? '') === 'Keluar' ? 'selected' : '' }}>Barang Keluar (Terjual / Rusak)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Tanggal Mutasi</label>
                                <input type="date" name="tanggal" required
                                    value="{{ $editLog ? $editLog->tanggal->format('Y-m-d') : date('Y-m-d') }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Jumlah Unit</label>
                                <input type="number" name="jumlah" required min="1" placeholder="0"
                                    value="{{ (string) ($editLog?->jumlah ?? '') }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Keterangan Catatan</label>
                                <input type="text" name="keterangan" placeholder="Contoh: Restock supplier batch #4"
                                    value="{{ $editLog?->keterangan ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 xl:col-span-1 pt-2 flex flex-col gap-2.5">
                                <button type="submit" class="w-full btn-lime">
                                    {{ $editLog ? 'Simpan Perubahan' : 'Simpan Catatan Mutasi' }}
                                </button>
                                @if ($editLog)
                                <a href="/stok" class="w-full btn-ghost-dark">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 5. KONDISI STOK & RIWAYAT MUTASI (8/12 on desktop) -->
                <div class="xl:col-span-8 space-y-4">

                    <!-- Sub-Tile A: Kondisi Stok Saat Ini -->
                    <section class="bento-tile !p-0 overflow-hidden">
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">Kondisi Stok Saat Ini</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Indikator kapasitas real-time terhadap batas minimum.</p>
                            </div>
                            <div class="bento-pills" data-bento-filter-group="stok-current-body" role="radiogroup" aria-label="Filter Kondisi Stok">
                                <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                <button type="button" data-filter-val="Normal" class="bento-pill">Normal</button>
                                <button type="button" data-filter-val="Menipis" class="bento-pill">Menipis</button>
                                <button type="button" data-filter-val="Habis" class="bento-pill">Habis</button>
                            </div>
                        </header>
                        <div class="overflow-x-auto w-full">
                            <table class="bento-table text-sm min-w-[540px]">
                                <thead>
                                    <tr>
                                        <th class="text-left">Produk</th>
                                        <th class="text-left">Kategori</th>
                                        <th class="text-center">Stok Saat Ini</th>
                                        <th class="text-center">Batas Min.</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="stok-current-body">
                                    @foreach ($produkList as $p)
                                        @php
                                            $pctStyle = 'width:' . $p->stockProgressPercentage() . '%';
                                            $barColor = $p->isOutOfStock() ? 'bg-white/25' : ($p->isLowStock() ? 'bg-amber-400' : 'bg-[#C4FF0D]');
                                        @endphp
                                    <tr data-filter-item data-filter-tag="{{ $p->stockStatus() }}">
                                        <td class="font-semibold text-white">
                                            {{ $p->nama }}
                                            <span class="text-[#9AA68E] text-xs font-normal">/{{ $p->satuan }}</span>
                                        </td>
                                        <td class="text-[#9AA68E] text-xs sm:text-sm">{{ $p->kategori }}</td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <span class="font-bold text-white w-8 text-right tabular-nums">{{ $p->stok }}</span>
                                                <div class="w-24 sm:w-32 h-2 bg-[#181F11] rounded-full overflow-hidden border border-white/[0.06]">
                                                    <div class="h-full {{ $barColor }} rounded-full" style="{{ $pctStyle }}"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center text-[#9AA68E] tabular-nums">{{ $p->stok_min }}</td>
                                        <td class="text-center whitespace-nowrap">
                                            @if ($p->isOutOfStock())
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-[#9AA68E]">Habis</span>
                                            @elseif ($p->isLowStock())
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-300 border border-amber-400/30">Menipis</span>
                                            @else
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Sub-Tile B: Riwayat Mutasi Stok -->
                    <section class="bento-tile !p-0 overflow-hidden">
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">Riwayat Mutasi Stok</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Log masuk &amp; keluar barang beserta opsi reversal otomatis.</p>
                            </div>
                            <div class="bento-pills" data-bento-filter-group="stok-log-body" role="radiogroup" aria-label="Filter Log Mutasi">
                                <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                <button type="button" data-filter-val="Masuk" class="bento-pill">Masuk</button>
                                <button type="button" data-filter-val="Keluar" class="bento-pill">Keluar</button>
                            </div>
                        </header>
                        @if ($stokLogList->isEmpty())
                        <p class="text-center text-[#9AA68E] text-sm py-12">Belum ada riwayat pergerakan stok.</p>
                        @else
                        <div class="overflow-x-auto w-full">
                            <table class="bento-table text-sm min-w-[580px]">
                                <thead>
                                    <tr>
                                        <th class="text-left">Tanggal</th>
                                        <th class="text-left">Produk</th>
                                        <th class="text-center">Jenis</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-left">Keterangan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="stok-log-body">
                                    @foreach ($stokLogList as $log)
                                    <tr data-filter-item data-filter-tag="{{ $log->jenis }}"
                                        class="{{ (string) $log->id === (string) $editId ? 'bg-[#C4FF0D]/[0.07]' : '' }}">
                                        <td class="text-xs sm:text-sm text-[#9AA68E] whitespace-nowrap">{{ $log->tanggal->format('d M Y') }}</td>
                                        <td class="font-semibold text-white">{{ $log->product?->nama ?? 'Dihapus' }}</td>
                                        <td class="text-center whitespace-nowrap">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                {{ $log->isIncoming() ? 'bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30' : 'bg-[#C084FC]/15 text-[#C084FC] border border-[#C084FC]/30' }}">
                                                {{ $log->jenis }}
                                            </span>
                                        </td>
                                        <td class="text-center font-bold tabular-nums whitespace-nowrap {{ $log->isIncoming() ? 'text-[#C4FF0D]' : 'text-[#C084FC]' }}">
                                            {{ ($log->isIncoming() ? '+' : '-') . $log->jumlah }}
                                        </td>
                                        <td class="text-[#9AA68E]">{{ $log->keterangan ?: '—' }}</td>
                                        <td class="text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-3.5">
                                                <a href="/stok?edit={{ $log->id }}#form-stok" class="text-xs sm:text-sm text-[#C4FF0D] hover:underline font-semibold transition">Edit</a>
                                                <form method="POST" action="/stok"
                                                    onsubmit="return confirm('Hapus riwayat log ini? Stok produk akan dikembalikan (reversal).')">
                                                    @csrf
                                                    <input type="hidden" name="action" value="hapus_log">
                                                    <input type="hidden" name="id" value="{{ $log->id }}">
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
                    </section>

                </div>

            </div>
        </div>
    </main>

    @if ($flash)
    <script>
        var _flash = Object.assign({ background: '#12170D', color: '#F4F7F0', confirmButtonColor: '#252f1b' }, @json($flash));
        Swal.fire(_flash);
    </script>
    @endif
</body>
</html>
