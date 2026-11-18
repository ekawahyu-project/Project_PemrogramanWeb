<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk | AlpetBizz</title>
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
        $totalSku = $produkList->count();
        $nilaiJualTotal = (int) $produkList->sum(fn ($p) => $p->harga_jual * $p->stok);
        $nilaiBeliTotal = (int) $produkList->sum(fn ($p) => $p->harga_beli * $p->stok);
        $potensiLaba = $nilaiJualTotal - $nilaiBeliTotal;

        $cntNormal = $produkList->filter(fn ($p) => ! $p->isLowStock())->count();
        $cntLow = $produkList->filter(fn ($p) => $p->isLowStock() && ! $p->isOutOfStock())->count();
        $cntOut = $produkList->filter(fn ($p) => $p->isOutOfStock())->count();

        $avgMarginPct = $produkList->count() > 0
            ? round($produkList->avg(fn ($p) => $p->harga_jual > 0 ? (($p->harga_jual - $p->harga_beli) / $p->harga_jual) * 100 : 0), 1)
            : 0.0;

        $readyRatio = $totalSku > 0 ? round(($cntNormal / $totalSku) * 100, 1) : 100.0;

        $groupedKat = $produkList->groupBy('kategori')->map->count()->sortDesc()->take(4);
        $katColors = ['#C4FF0D', '#C084FC', '#38BDF8', '#FBBF24'];
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Produk Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Manajemen Produk</h2>
                </div>
            </nav>

            @if (! empty($errorsList))
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

                <!-- 1. HERO VALUATION TILE (col-span-2) -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 justify-between">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#9AA68E]">Valuasi Katalog Produk</p>
                            <p class="mt-1.5 text-3xl sm:text-4xl font-bold leading-none tracking-[-0.03em] text-white">
                                Rp {{ number_format($nilaiJualTotal, 0, ',', '.') }}
                            </p>
                            <p class="mt-2 text-sm text-[#9AA68E]">
                                Estimasi nilai jual seluruh stok · Potensi laba <span class="text-[#C4FF0D] font-semibold">+Rp {{ number_format($potensiLaba, 0, ',', '.') }}</span>
                            </p>
                        </div>
                        <span class="rounded-full bg-[#C4FF0D]/15 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-[#C4FF0D]">
                            {{ $totalSku }} item
                        </span>
                    </div>

                    <!-- 4 Mini Status Pills (Responsive 2 col mobile, 4 col sm+) -->
                    <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] px-3.5 py-3">
                            <p class="text-xs font-medium text-[#9AA68E]">Modal Stok</p>
                            <p class="text-sm sm:text-base font-bold text-white mt-1">Rp {{ number_format($nilaiBeliTotal, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] px-3.5 py-3">
                            <p class="text-xs font-medium text-[#9AA68E]">Stok Normal</p>
                            <p class="text-sm sm:text-base font-bold text-[#C4FF0D] mt-1">{{ $cntNormal }} produk</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] px-3.5 py-3">
                            <p class="text-xs font-medium text-[#9AA68E]">Stok Menipis</p>
                            <p class="text-sm sm:text-base font-bold text-amber-300 mt-1">{{ $cntLow }} produk</p>
                        </div>
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] px-3.5 py-3">
                            <p class="text-xs font-medium text-[#9AA68E]">Stok Habis</p>
                            <p class="text-sm sm:text-base font-bold text-rose-400 mt-1">{{ $cntOut }} produk</p>
                        </div>
                    </div>

                    <!-- Interactive SKU Margin Strip -->
                    <div class="mt-6">
                        <div id="bento-pnl-strip" class="flex items-end gap-1.5" style="height: 56px;">
                            @if ($produkList->isNotEmpty())
                                @foreach ($produkList->take(18) as $p)
                                    @php
                                        $mPct = $p->harga_jual > 0 ? round((($p->harga_jual - $p->harga_beli) / $p->harga_jual) * 100, 1) : 0;
                                        $barH = max(22, min(100, round($mPct * 1.4)));
                                    @endphp
                                    <button type="button"
                                            data-pnl-label="{{ $p->nama }} · Margin +{{ $mPct }}% (Stok: {{ $p->stok }} {{ $p->satuan }})"
                                            data-base-opacity="0.85"
                                            class="flex-1 rounded-[4px] transition-opacity focus:outline-none"
                                            style="height: {{ $barH }}%; background: {{ $p->isLowStock() ? '#C084FC' : '#C4FF0D' }}; opacity: 0.85;"></button>
                                @endforeach
                            @else
                                @foreach ([45, 65, 55, 80, 70, 90, 60, 85] as $dH)
                                    <span class="flex-1 rounded-[4px] bg-[#C4FF0D]/40" style="height: {{ $dH }}%;"></span>
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-[#9AA68E]">
                            <span>SKU #1</span>
                            <span id="bento-pnl-readout" data-default="Hover bar untuk cek margin produk" class="text-[#F4F7F0] font-medium truncate max-w-[280px]">Hover bar untuk cek margin produk</span>
                            <span>SKU #{{ $totalSku }}</span>
                        </div>
                    </div>
                </section>

                <!-- 2. CATEGORY ALLOCATION TILE (col-span-1) -->
                <section id="bento-allocation-tile" class="bento-tile justify-between">
                    <header class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Sebaran Kategori</h3>
                        <span class="text-xs font-semibold text-[#9AA68E]">SKU</span>
                    </header>

                    <div class="my-4">
                        <p class="text-2xl font-bold text-white">{{ $groupedKat->count() }} Kategori</p>
                        <p class="text-xs text-[#9AA68E] mt-0.5">aktif dalam katalog</p>

                        <div class="mt-4 flex h-8 gap-1">
                            @forelse ($groupedKat as $katName => $katCount)
                                @php $cHex = $katColors[$loop->index % count($katColors)]; @endphp
                                <span data-alloc-segment class="rounded-[8px] transition-all duration-300"
                                      style="flex-basis: {{ max(15, round(($katCount / max($totalSku, 1)) * 100)) }}%; background: {{ $cHex }};"></span>
                            @empty
                                <span class="flex-1 rounded-[8px] bg-white/10"></span>
                            @endforelse
                        </div>

                        <ul class="mt-4 space-y-2.5 text-sm">
                            @forelse ($groupedKat as $katName => $katCount)
                                @php $cHex = $katColors[$loop->index % count($katColors)]; @endphp
                                <li data-alloc-item class="flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2.5 text-white truncate">
                                        <span class="size-2.5 rounded-full shrink-0" style="background: {{ $cHex }};"></span>
                                        <span class="truncate">{{ $katName }}</span>
                                    </span>
                                    <span class="text-[#9AA68E] font-semibold shrink-0">{{ $katCount }} item</span>
                                </li>
                            @empty
                                <li class="text-sm text-[#9AA68E]">Belum ada kategori</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="pt-3.5 border-t border-white/[0.08] flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-[#9AA68E]">Kesiapan Katalog</span>
                        <span class="font-semibold text-[#C4FF0D]">{{ $readyRatio }}% Ready</span>
                    </div>
                </section>

                <!-- 3. SOLID LIME ACCENT TILE (col-span-1) -->
                <section class="bento-tile-accent justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-sm font-bold opacity-85">Rata-rata Margin</h3>
                        </header>

                        <p class="mt-4 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em]">
                            {{ number_format($avgMarginPct, 1) }}<span class="text-2xl">%</span>
                        </p>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Gross Margin Katalog</p>

                        <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                <span class="opacity-80">Ketersediaan Stok</span>
                                <span>{{ $readyRatio }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ min(100, max(10, $readyRatio)) }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-[#152208]/15">
                        <div class="flex justify-between text-xs sm:text-sm mb-3">
                            <span class="opacity-80">Potensi Profit</span>
                            <span class="font-bold">Rp {{ number_format($potensiLaba, 0, ',', '.') }}</span>
                        </div>
                        <a href="/stok"
                           class="block w-full text-center rounded-2xl py-3 text-xs sm:text-sm font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            Kelola Mutasi Stok →
                        </a>
                    </div>
                </section>

            </div>

            <!-- ══════════════════════════════════════════════════════════
                 ROW 2: PRODUCT COMPOSER + INTERACTIVE CATALOG TABLE (12-Column Responsive Split)
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">

                <!-- 4. PRODUCT COMPOSER TILE (4/12 on desktop, full width on mobile/tablet) -->
                <section id="form-produk" class="bento-tile xl:col-span-4 justify-between {{ $editPrd ? 'border-[#C4FF0D]/60 ring-1 ring-[#C4FF0D]/25' : '' }}">
                    <div>
                        <header class="mb-5 flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                {{ $editPrd ? 'Edit Data Produk' : '+ Tambah Produk Baru' }}
                            </h3>
                            <span class="text-xs font-semibold uppercase px-2.5 py-1 rounded-full bg-white/[0.06] text-[#9AA68E]">
                                {{ $editPrd ? 'Update' : 'Create' }}
                            </span>
                        </header>

                        <form method="POST" action="/produk" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @csrf
                            <input type="hidden" name="action" value="{{ $editPrd ? 'update' : 'tambah' }}">
                            @if ($editPrd)
                                <input type="hidden" name="id" value="{{ $editPrd->id }}">
                            @endif

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Nama Produk</label>
                                <input type="text" name="nama" required maxlength="100" placeholder="Masukkan nama produk"
                                    value="{{ old('nama', $editPrd?->nama ?? '') }}" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Kategori</label>
                                <select name="kategori" class="{{ $inputClass }}">
                                    @foreach ($kategoriOpt as $k)
                                        <option value="{{ $k }}" {{ old('kategori', $editPrd?->kategori ?? 'Lainnya') === $k ? 'selected' : '' }}>{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Satuan</label>
                                <select name="satuan" class="{{ $inputClass }}">
                                    @foreach ($satuanOpt as $s)
                                        <option value="{{ $s }}" {{ old('satuan', $editPrd?->satuan ?? 'pcs') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Harga Beli (Rp)</label>
                                <input type="number" name="harga_beli" required min="0" step="1" placeholder="0"
                                    value="{{ old('harga_beli', $editPrd?->harga_beli ?? '') }}" class="no-spinner {{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Harga Jual (Rp)</label>
                                <input type="number" name="harga_jual" required min="1" step="1" placeholder="0"
                                    value="{{ old('harga_jual', $editPrd?->harga_jual ?? '') }}" class="no-spinner {{ $inputClass }}">
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">{{ $editPrd ? 'Stok Saat Ini' : 'Stok Awal' }}</label>
                                <input type="number" name="stok" required min="0" step="1" placeholder="0"
                                    value="{{ old('stok', $editPrd?->stok ?? 0) }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Stok Minimum</label>
                                <input type="number" name="stok_min" required min="0" step="1" placeholder="5"
                                    value="{{ old('stok_min', $editPrd?->stok_min ?? 5) }}" class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 pt-2 flex flex-col gap-2.5">
                                <button type="submit" class="w-full btn-lime">
                                    {{ $editPrd ? 'Simpan Perubahan' : 'Tambah Produk' }}
                                </button>
                                @if ($editPrd)
                                    <a href="/produk" class="w-full btn-ghost-dark">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 5. INTERACTIVE CATALOG TABLE TILE (8/12 on desktop) -->
                <section class="bento-tile xl:col-span-8 !p-0 overflow-hidden justify-between">
                    <div>
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">Daftar Katalog Produk ({{ $produkList->count() }})</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Filter produk berdasarkan status ketersediaan atau cari nama produk.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="text"
                                       data-bento-search="produk-table-body"
                                       placeholder="Cari produk / kategori..."
                                       class="px-4 py-2 rounded-full bg-[#181F11] border border-white/12 text-xs sm:text-sm text-white placeholder:text-[#9AA68E]/60 outline-none w-full sm:w-56">
                                <div class="bento-pills" data-bento-filter-group="produk-table-body" role="radiogroup" aria-label="Filter Status Produk">
                                    <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                    <button type="button" data-filter-val="Normal" class="bento-pill">Normal</button>
                                    <button type="button" data-filter-val="Menipis" class="bento-pill">Menipis</button>
                                    <button type="button" data-filter-val="Habis" class="bento-pill">Habis</button>
                                </div>
                            </div>
                        </header>

                        @if ($produkList->isEmpty())
                            <p class="text-center text-[#9AA68E] text-sm py-16">Belum ada produk.</p>
                        @else
                            <div class="overflow-x-auto w-full">
                                <table class="bento-table text-sm min-w-[680px]">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Nama Produk</th>
                                            <th class="text-left">Kategori</th>
                                            <th class="text-right">Harga Beli</th>
                                            <th class="text-right">Harga Jual &amp; Margin</th>
                                            <th class="text-center">Stok</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="produk-table-body">
                                        @foreach ($produkList as $p)
                                            @php
                                                $statusLabel = $p->stockStatus();
                                                $unitProfit = $p->harga_jual - $p->harga_beli;
                                                $mPctRow = $p->harga_jual > 0 ? round(($unitProfit / $p->harga_jual) * 100) : 0;
                                            @endphp
                                            <tr data-filter-item data-filter-tag="{{ $statusLabel }}"
                                                class="{{ (string) $p->id === (string) $editId ? 'bg-[#C4FF0D]/[0.07]' : '' }}">
                                                <td>
                                                    <div class="flex items-center gap-3.5">
                                                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#181F11] border border-white/10 text-xs font-bold text-[#C4FF0D]">
                                                            {{ strtoupper(mb_substr($p->nama, 0, 1)) }}
                                                        </span>
                                                        <div>
                                                            <p class="font-semibold text-white">{{ $p->nama }}</p>
                                                            <p class="text-xs text-[#9AA68E] mt-0.5">Per {{ $p->satuan }} · Min: {{ $p->stok_min }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="rounded-full border border-white/10 bg-[#181F11] px-3 py-1 text-xs text-[#9AA68E]">
                                                        {{ $p->kategori }}
                                                    </span>
                                                </td>
                                                <td class="text-right tabular-nums text-[#9AA68E] whitespace-nowrap">
                                                    Rp {{ number_format($p->harga_beli, 0, ',', '.') }}
                                                </td>
                                                <td class="text-right whitespace-nowrap">
                                                    <p class="font-bold tabular-nums text-[#C4FF0D]">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</p>
                                                    <p class="text-xs text-[#9AA68E]">+{{ $mPctRow }}% margin</p>
                                                </td>
                                                <td class="text-center font-bold tabular-nums text-white">{{ $p->stok }}</td>
                                                <td class="text-center whitespace-nowrap">
                                                    @if ($p->isOutOfStock())
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-[#9AA68E]">Habis</span>
                                                    @elseif ($p->isLowStock())
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-300 border border-amber-400/30">Menipis</span>
                                                    @else
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30">Normal</span>
                                                    @endif
                                                </td>
                                                <td class="text-center whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-3.5">
                                                        <a href="/produk?edit={{ $p->id }}#form-produk" class="text-xs sm:text-sm text-[#C4FF0D] hover:underline font-semibold transition">Edit</a>
                                                        <form method="POST" action="/produk">
                                                            @csrf
                                                            <input type="hidden" name="action" value="hapus">
                                                            <input type="hidden" name="id" value="{{ $p->id }}">
                                                            <button type="button"
                                                                data-nama="{{ $p->nama }}"
                                                                onclick="konfirmasiHapus(this)"
                                                                class="text-xs sm:text-sm text-rose-400 hover:text-rose-300 font-semibold transition">Hapus</button>
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
                        <span>Total {{ $totalSku }} produk dalam database</span>
                        <span class="text-[#C4FF0D] font-medium">Inventory Live</span>
                    </div>
                </section>

            </div>
        </div>

        <script>
            window.konfirmasiHapus = function(btn) {
                const form = btn.closest('form');
                Swal.fire({
                    title: 'Hapus Produk?',
                    text: 'Hapus produk ' + btn.dataset.nama + '?',
                    icon: 'warning',
                    background: '#12170D',
                    color: '#F4F7F0',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#252f1b',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            };
        </script>

        @if ($notif)
            <script>
                var _notif = Object.assign({ background: '#12170D', color: '#F4F7F0', confirmButtonColor: '#252f1b' }, @json($notif));
                Swal.fire(_notif);
            </script>
        @endif
    </main>
</body>
</html>
