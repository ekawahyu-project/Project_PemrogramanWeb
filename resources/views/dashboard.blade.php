<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AlpetBizz</title>
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
        $netFormatted = 'Rp ' . number_format(abs($laba ?? 0), 0, ',', '.');
        $inFormatted = 'Rp ' . number_format($pemasukan ?? 0, 0, ',', '.');
        $outFormatted = 'Rp ' . number_format($pengeluaran ?? 0, 0, ',', '.');
        $healthVal = $stockHealthScore ?? 88.0;
        $marginVal = $marginPct ?? 24.5;

        // Vault Cards (3 Unique Inventory Asset Metrics)
        $fmtNilaiJual = 'Rp ' . number_format($nilaiJualStok ?? 0, 0, ',', '.');
        $fmtModalStok = 'Rp ' . number_format($modalStok ?? 0, 0, ',', '.');
        $fmtPotensiLaba = 'Rp ' . number_format($potensiLabaStok ?? 0, 0, ',', '.');

        // AllocationTile: Omzet per Kategori Produk
        $catRevSlice = array_slice($categoryRevenue ?? [], 0, 4, true);
        $totalCatRev = max(array_sum($catRevSlice), 1);
        $catColors = ['#C4FF0D', '#C084FC', '#38BDF8', '#FBBF24'];

        // RangeTile: Target Omzet vs Break-Even Point (BEP)
        $bepMin = max($pengeluaran ?? 0, 500000);
        $targetMax = max((int) round($bepMin * 1.75), 2500000);
        $targetPosPct = min(92, max(12, round((($pemasukan ?? 0) / max($targetMax, 1)) * 100)));
        $isTargetMet = ($pemasukan ?? 0) >= $bepMin;

        // 24-bar PnL strip heights
        $pnlBars = [42, 58, 35, 66, 74, 48, 82, 64, 52, 78, 88, 45, 70, 92, 60, 76, 84, 54, 68, 90, 72, 86, 95, 100];
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Dashboard Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Overview Bento</h2>
                </div>
            </nav>

            <!-- ══════════════════════════════════════════════════════════
                 WEB3BENTO 4-COLUMN GRID (Responsive across Mobile, Tablet, Laptop & Desktop)
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <!-- 1. HERO TILE: Greeting, Laba Bersih & Cashflow Summary -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 xl:col-start-1 xl:row-start-1 justify-between">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-2xl sm:text-3xl font-semibold leading-[1.15] tracking-[-0.03em] text-white">
                                <span class="text-[#C4FF0D]">{{ $user }}</span>
                            </p>
                            <p class="mt-1.5 text-sm text-[#9AA68E]">
                                Ringkasan arus kas &amp; laba bersih usaha Anda.
                            </p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-3xl sm:text-4xl font-semibold leading-none tracking-[-0.04em] tabular-nums text-white mt-0.5">
                                {{ $netFormatted }}
                            </p>
                            <p class="mt-2 text-xs sm:text-sm tabular-nums">
                                <span style="color: {{ ($laba ?? 0) >= 0 ? '#C4FF0D' : '#C084FC' }}">
                                    {{ ($laba ?? 0) >= 0 ? '+' : '−' }}{{ abs($marginVal) }}% margin
                                </span>
                                <span class="text-[#9AA68E]"> · {{ ($laba ?? 0) >= 0 ? 'Surplus' : 'Defisit' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- 3 Non-Overlapping Cashflow KPI Boxes (Responsive 1 col mobile, 3 col sm+) -->
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach ($stats as $s)
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] px-4 py-3.5">
                            <p class="text-xs font-medium text-[#9AA68E]">{{ $s['label'] }}</p>
                            <p class="text-base sm:text-lg font-bold tabular-nums mt-1 {{ $s['valueClass'] }}">{{ $s['value'] }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">{{ $s['sub'] }}</p>
                        </div>
                        @endforeach
                    </div>

                    <!-- Interactive PnL Bar Strip -->
                    <div class="mt-6 flex flex-col">
                        <div id="bento-pnl-strip" class="flex items-end gap-1" style="height: 60px;">
                            @foreach ($pnlBars as $idx => $barH)
                            @php
                                $isUp = $idx % 5 !== 2;
                                $baseOp = round(0.38 + ($barH / 100) * 0.62, 2);
                            @endphp
                            <button
                                type="button"
                                data-pnl-label="Hari ke-{{ $idx + 1 }} · {{ $isUp ? '+' : '−' }}{{ $barH }}% aktivitas kas"
                                data-base-opacity="{{ $baseOp }}"
                                aria-label="Hari {{ $idx + 1 }}"
                                class="relative flex-1 rounded-[4px] focus:outline-none transition-opacity"
                                style="height: {{ $barH }}%; background: {{ $isUp ? '#C4FF0D' : '#C084FC' }}; opacity: {{ $baseOp }};"
                            ></button>
                            @endforeach
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-[#9AA68E]">
                            <span>30d Lalu</span>
                            <span id="bento-pnl-readout" data-default="Daily Cashflow Strip" class="text-[#F4F7F0] font-medium">Daily Cashflow Strip</span>
                            <span>Hari Ini</span>
                        </div>
                    </div>
                </section>

                <!-- 2. VAULT CARDS TILE: Valuasi Aset Gudang -->
                <section class="bento-tile overflow-hidden xl:col-start-3 xl:row-start-1" data-card-glare-zone>
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">
                            Aset Gudang <sup class="text-xs text-[#C4FF0D]">3</sup>
                        </h3>
                        <span class="text-xs text-[#9AA68E]">Klik kartu</span>
                    </header>

                    <div class="relative -mx-5 -mb-5 flex flex-1 flex-col justify-end overflow-hidden pt-2">
                        <div id="bento-cards-stack" class="relative">
                            <!-- Card 1 (Back): Potensi Margin Stok -->
                            <button type="button"
                                data-card-id="c-margin"
                                data-last4="8810"
                                data-holder="Potensi Margin Stok"
                                data-sub="Estimasi profit stok"
                                data-balance="{{ $fmtPotensiLaba }}"
                                class="group absolute left-1/2 block w-[calc(100%-32px)] max-w-[300px] rounded-[14px] p-0 text-left transition-all duration-500 focus:outline-none"
                                style="color: #271705; box-shadow: 0 -10px 28px -14px rgba(0,0,0,0.8);">
                                <span class="relative block w-full" style="padding-top: 58%;">
                                    <span class="absolute inset-0 overflow-hidden rounded-[14px]"
                                          style="background: linear-gradient(135deg, #FBBF24 0%, #F97316 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);">
                                        <span data-card-glare class="absolute inset-0"
                                              style="background-image: linear-gradient(115deg, transparent 20%, rgba(255,255,255,0.45) 45%, transparent 75%); background-size: 240% 100%; mix-blend-mode: soft-light;"></span>
                                    </span>
                                    <span class="absolute inset-x-0 top-0 flex items-center justify-between px-4 pt-3.5">
                                        <span class="text-sm font-bold tracking-tight">Potensi Margin</span>
                                        <span class="text-xs font-semibold">**** 8810</span>
                                    </span>
                                </span>
                            </button>

                            <!-- Card 2 (Middle): Modal Barang Tertanam -->
                            <button type="button"
                                data-card-id="c-modal"
                                data-last4="4352"
                                data-holder="Modal Pokok Stok"
                                data-sub="Nilai beli stok"
                                data-balance="{{ $fmtModalStok }}"
                                class="group absolute left-1/2 block w-[calc(100%-32px)] max-w-[300px] rounded-[14px] p-0 text-left transition-all duration-500 focus:outline-none"
                                style="color: #1F1230; box-shadow: 0 -10px 28px -14px rgba(0,0,0,0.8);">
                                <span class="relative block w-full" style="padding-top: 58%;">
                                    <span class="absolute inset-0 overflow-hidden rounded-[14px]"
                                          style="background: linear-gradient(135deg, #C084FC 0%, #818CF8 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);">
                                        <span data-card-glare class="absolute inset-0"
                                              style="background-image: linear-gradient(115deg, transparent 20%, rgba(255,255,255,0.45) 45%, transparent 75%); background-size: 240% 100%; mix-blend-mode: soft-light;"></span>
                                    </span>
                                    <span class="absolute inset-x-0 top-0 flex items-center justify-between px-4 pt-3.5">
                                        <span class="text-sm font-bold tracking-tight">Modal Stok</span>
                                        <span class="text-xs font-semibold">**** 4352</span>
                                    </span>
                                </span>
                            </button>

                            <!-- Card 3 (Front): Nilai Jual Inventaris -->
                            <button type="button"
                                data-card-id="c-aset"
                                data-last4="6902"
                                data-holder="Nilai Jual Inventaris"
                                data-sub="{{ $totalUnitStok ?? 0 }} unit siap jual"
                                data-balance="{{ $fmtNilaiJual }}"
                                class="group absolute left-1/2 block w-[calc(100%-32px)] max-w-[300px] rounded-[14px] p-0 text-left transition-all duration-500 focus:outline-none"
                                style="color: #152208; box-shadow: 0 -10px 28px -14px rgba(0,0,0,0.8);">
                                <span class="relative block w-full" style="padding-top: 58%;">
                                    <span class="absolute inset-0 overflow-hidden rounded-[14px]"
                                          style="background: linear-gradient(135deg, #C4FF0D 0%, #34D399 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);">
                                        <span data-card-glare class="absolute inset-0"
                                              style="background-image: linear-gradient(115deg, transparent 20%, rgba(255,255,255,0.5) 45%, transparent 75%); background-size: 240% 100%; mix-blend-mode: soft-light;"></span>
                                    </span>
                                    <span class="absolute inset-x-0 top-0 flex items-center justify-between px-4 pt-3.5">
                                        <span class="text-sm font-bold tracking-tight">Valuasi Jual Stok</span>
                                        <span class="text-xs font-semibold">**** 6902</span>
                                    </span>
                                </span>
                            </button>

                            <!-- Spacer -->
                            <div aria-hidden="true" class="invisible mx-auto w-[calc(100%-32px)] max-w-[300px] shrink-0" style="margin-top: 84px;">
                                <div style="padding-top: 32%;"></div>
                            </div>
                        </div>

                        <!-- Frosted Readout Panel -->
                        <div class="relative z-10 flex shrink-0 items-end justify-between gap-3 px-5 pb-4 pt-3"
                             style="height: 90px; background: rgba(18, 23, 13, 0.82); backdrop-filter: blur(16px); border-top: 1px solid rgba(255,255,255,0.08);">
                            <div class="min-w-0">
                                <p id="bento-card-front-last4" class="truncate text-sm font-semibold tabular-nums tracking-[0.04em] text-white">
                                    •••• •••• •••• 6902
                                </p>
                                <p id="bento-card-front-holder" class="mt-1 truncate text-xs text-[#9AA68E]">
                                    Nilai Jual Inventaris · {{ $totalUnitStok ?? 0 }} unit
                                </p>
                            </div>
                            <p id="bento-card-front-balance" class="shrink-0 text-base font-bold tabular-nums text-[#C4FF0D]">
                                {{ $fmtNilaiJual }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- 3. SEMICIRCLE GAUGE TILE: Kesiapan Fisik Gudang -->
                <section id="bento-gauge-tile" data-base-score="{{ $healthVal }}" class="bento-tile xl:col-start-4 xl:row-start-1 justify-between">
                    <header class="mb-2 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Indeks Gudang</h3>
                        <span class="text-xs font-semibold text-[#C4FF0D]">{{ $totalUnitStok ?? 0 }} unit</span>
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
                            <span id="bento-gauge-value">{{ number_format($healthVal, 1) }}</span>
                            <span class="text-base text-[#9AA68E]">%</span>
                        </p>
                        <p class="mt-1.5 text-xs tabular-nums text-[#9AA68E]">
                            {{ $jmlStok }} SKU · {{ $lowStock > 0 ? $lowStock.' menipis' : 'Semua aman' }}
                        </p>
                    </div>

                    <div class="mt-4 flex justify-center">
                        <div class="bento-pills" role="radiogroup" aria-label="Mode Stok">
                            <button type="button" data-gauge-tier="Slow" class="bento-pill">Min</button>
                            <button type="button" data-gauge-tier="Normal" class="bento-pill active">Normal</button>
                            <button type="button" data-gauge-tier="Fast" class="bento-pill">Optimal</button>
                        </div>
                    </div>
                </section>

                <!-- 4. CHART TILE: Tren Historis Pemasukan vs Pengeluaran -->
                @php
                    $baseIn = max($pemasukan ?? 250000, 100000);
                    $baseOut = max($pengeluaran ?? 100000, 50000);
                    $ptsA = [
                        ['xPct' => 0,   'yPct' => 72, 'val' => $baseIn * 0.45],
                        ['xPct' => 16,  'yPct' => 58, 'val' => $baseIn * 0.62],
                        ['xPct' => 33,  'yPct' => 64, 'val' => $baseIn * 0.55],
                        ['xPct' => 50,  'yPct' => 42, 'val' => $baseIn * 0.78],
                        ['xPct' => 66,  'yPct' => 35, 'val' => $baseIn * 0.86],
                        ['xPct' => 83,  'yPct' => 26, 'val' => $baseIn * 0.94],
                        ['xPct' => 100, 'yPct' => 18, 'val' => $pemasukan ?? 0],
                    ];
                    $ptsB = [
                        ['xPct' => 0,   'yPct' => 82, 'val' => $baseOut * 0.50],
                        ['xPct' => 16,  'yPct' => 76, 'val' => $baseOut * 0.60],
                        ['xPct' => 33,  'yPct' => 70, 'val' => $baseOut * 0.72],
                        ['xPct' => 50,  'yPct' => 66, 'val' => $baseOut * 0.80],
                        ['xPct' => 66,  'yPct' => 60, 'val' => $baseOut * 0.88],
                        ['xPct' => 83,  'yPct' => 56, 'val' => $baseOut * 0.95],
                        ['xPct' => 100, 'yPct' => 52, 'val' => $pengeluaran ?? 0],
                    ];
                @endphp
                <section class="bento-tile md:col-span-2 xl:col-span-2 xl:col-start-1 xl:row-span-2 xl:row-start-2 justify-between">
                    <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Tren Kurva Arus Kas</h3>
                        <a href="/laporan" class="bento-pills">
                            <span class="bento-pill active">Live</span>
                            <span class="bento-pill">Grafik Lengkap →</span>
                        </a>
                    </header>

                    <div class="flex flex-wrap items-end gap-x-8 gap-y-3">
                        <div>
                            <p class="flex items-center gap-2 text-xs text-[#9AA68E]">
                                Kurva Pemasukan
                            </p>
                            <p id="bento-chart-val-a" data-default="{{ $inFormatted }}" class="mt-1 text-xl sm:text-2xl font-bold tabular-nums text-white">
                                {{ $inFormatted }}
                            </p>
                        </div>
                        <div>
                            <p class="flex items-center gap-2 text-xs text-[#9AA68E]">
                                Kurva Pengeluaran
                            </p>
                            <p id="bento-chart-val-b" data-default="{{ $outFormatted }}" class="mt-1 text-xl sm:text-2xl font-bold tabular-nums text-[#9AA68E]">
                                {{ $outFormatted }}
                            </p>
                        </div>
                        <span class="ml-auto rounded-full bg-[#C4FF0D]/15 px-3 py-1 text-xs font-semibold tabular-nums text-[#C4FF0D]">
                            Hover titik kurva
                        </span>
                    </div>

                    <!-- Interactive Smooth SVG Curve -->
                    <div id="bento-svg-chart"
                         data-points-a='@json($ptsA)'
                         data-points-b='@json($ptsB)'
                         class="relative mt-4 flex-1 cursor-crosshair"
                         style="min-height: 220px;">
                        <svg viewBox="0 0 640 220" preserveAspectRatio="none" class="absolute inset-0 h-full w-full" aria-hidden="true">
                            <defs>
                                <linearGradient id="bentoChartFill" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0" stop-color="#C4FF0D" stop-opacity="0.28" />
                                    <stop offset="1" stop-color="#C4FF0D" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <line x1="0" x2="640" y1="55" y2="55" stroke="rgba(255,255,255,0.07)" stroke-dasharray="2 5" />
                            <line x1="0" x2="640" y1="110" y2="110" stroke="rgba(255,255,255,0.07)" stroke-dasharray="2 5" />
                            <line x1="0" x2="640" y1="165" y2="165" stroke="rgba(255,255,255,0.07)" stroke-dasharray="2 5" />

                            <path d="M 0 158 C 52 158, 52 128, 105 128 C 158 128, 158 140, 212 140 C 266 140, 266 92, 320 92 C 374 92, 374 77, 426 77 C 480 77, 480 57, 532 57 C 586 57, 586 40, 640 40 L 640 220 L 0 220 Z"
                                  fill="url(#bentoChartFill)" />

                            <path d="M 0 180 C 52 180, 52 167, 105 167 C 158 167, 158 154, 212 154 C 266 154, 266 145, 320 145 C 374 145, 374 132, 426 132 C 480 132, 480 123, 532 123 C 586 123, 586 114, 640 114"
                                  fill="none" stroke="#8E9882" stroke-width="1.75" stroke-dasharray="4 5" />

                            <path d="M 0 158 C 52 158, 52 128, 105 128 C 158 128, 158 140, 212 140 C 266 140, 266 92, 320 92 C 374 92, 374 77, 426 77 C 480 77, 480 57, 532 57 C 586 57, 586 40, 640 40"
                                  fill="none" stroke="#C4FF0D" stroke-width="2.75" stroke-linecap="round" />
                        </svg>

                        <span id="bento-chart-crosshair" class="pointer-events-none absolute inset-y-0 w-px -translate-x-1/2 bg-white/20 transition-opacity" style="left: 100%; opacity: 0;"></span>
                        <span id="bento-chart-dot-a" class="pointer-events-none absolute size-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-[#12170D] bg-[#C4FF0D]" style="left: 100%; top: 18%;"></span>
                        <span id="bento-chart-dot-b" class="pointer-events-none absolute size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-[#12170D] bg-[#8E9882] transition-opacity" style="left: 100%; top: 52%; opacity: 0;"></span>
                    </div>

                    <div class="mt-3 flex justify-between text-xs text-[#9AA68E]">
                        <span>Mgg 1</span>
                        <span>Mgg 2</span>
                        <span>Mgg 3</span>
                        <span>Mgg 4</span>
                        <span>Hari Ini</span>
                    </div>
                </section>

                <!-- 5. ALLOCATION TILE: Kontribusi Omzet per Kategori Produk -->
                <section id="bento-allocation-tile" class="bento-tile xl:col-start-3 xl:row-start-2 justify-between">
                    <header class="mb-2 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Omzet per Kategori</h3>
                        <span class="text-xs font-semibold text-[#C4FF0D]">Top Sektor</span>
                    </header>

                    <div>
                        <p class="text-xl font-bold tabular-nums text-white">
                            {{ count($catRevSlice) }} Sektor Produk
                        </p>

                        <div class="mt-3.5 flex h-8 gap-1">
                            @forelse ($catRevSlice as $catName => $catVal)
                                @php
                                    $pctCat = max(14, round(($catVal / $totalCatRev) * 100, 1));
                                    $cHex = $catColors[$loop->index % count($catColors)];
                                @endphp
                                <span data-alloc-segment
                                      class="rounded-[8px] transition-all duration-300"
                                      style="flex-basis: {{ $pctCat }}%; background: {{ $cHex }};"></span>
                            @empty
                                <span class="flex-1 rounded-[8px] bg-white/10"></span>
                            @endforelse
                        </div>

                        <ul class="mt-4 grid grid-cols-1 gap-y-2.5">
                            @forelse ($catRevSlice as $catName => $catVal)
                                @php $cHex = $catColors[$loop->index % count($catColors)]; @endphp
                                <li data-alloc-item class="flex items-center justify-between gap-2 text-xs sm:text-sm cursor-pointer">
                                    <span class="flex items-center gap-2.5 text-[#F4F7F0] truncate">
                                        <span class="size-2.5 rounded-full shrink-0" style="background: {{ $cHex }};"></span>
                                        <span class="truncate">{{ $catName }}</span>
                                    </span>
                                    <span class="tabular-nums text-[#9AA68E] font-medium shrink-0">Rp {{ number_format($catVal, 0, ',', '.') }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-[#9AA68E]">Belum ada penjualan kategori</li>
                            @endforelse
                        </ul>
                    </div>
                </section>

                <!-- 6. SOLID LIME ACCENT TILE: Ringkasan Hutang & Piutang / Kasbon -->
                <section class="bento-tile-accent xl:col-start-4 xl:row-span-2 xl:row-start-2 justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-sm font-bold opacity-85">Kasbon &amp; Hutang</h3>
                        </header>

                        <p class="mt-5 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em] tabular-nums">
                            {{ number_format($kolektibilitasPct ?? 100, 1) }}<span class="text-2xl">%</span>
                        </p>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Rasio Pelunasan Piutang</p>

                        <!-- Progress Box -->
                        <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                <span class="opacity-80">Kolektibilitas</span>
                                <span class="tabular-nums">{{ number_format($kolektibilitasPct ?? 100, 0) }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ min(100, max(10, $kolektibilitasPct ?? 100)) }}%;"></div>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <dt class="opacity-80">Sisa Piutang</dt>
                                <dd class="font-bold tabular-nums">Rp {{ number_format($sisaPiutang ?? 0, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="opacity-80">Sisa Hutang</dt>
                                <dd class="font-bold tabular-nums">Rp {{ number_format($sisaHutang ?? 0, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="opacity-80">Tagihan Berjalan</dt>
                                <dd class="font-bold tabular-nums">{{ $activeDebtCount ?? 0 }} pihak</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6 pt-4">
                        <p class="text-xs sm:text-sm font-semibold opacity-80">Dana Kasbon Belum Cair</p>
                        <p class="mt-1 text-xl sm:text-2xl font-bold tabular-nums">
                            Rp {{ number_format($sisaPiutang ?? 0, 0, ',', '.') }}
                        </p>

                        <div class="mt-4 flex h-11 items-end gap-1" aria-hidden="true">
                            @foreach ([0.55, 0.68, 0.60, 0.78, 0.72, 0.86, 0.82, 0.92, 0.88, 1.0] as $i => $ep)
                            <span class="flex-1 rounded-[4px] bg-[#152208]"
                                  style="height: {{ $ep * 100 }}%; opacity: {{ 0.16 + ($i / 10) * 0.5 }};"></span>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs opacity-70">Progres penagihan kasbon</p>

                        <a href="/hutang-piutang"
                           class="mt-4 block w-full text-center rounded-2xl py-3.5 text-sm font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            Kelola Hutang &amp; Piutang
                        </a>
                    </div>
                </section>

                <!-- 7. RANGE TILE: Target Omzet vs Break-Even Point -->
                <section class="bento-tile xl:col-start-3 xl:row-start-3 justify-between">
                    <header class="mb-2 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Target Omzet &amp; BEP</h3>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                              style="background: {{ $isTargetMet ? 'rgba(196,255,13,0.16)' : 'rgba(192,132,252,0.18)' }}; color: {{ $isTargetMet ? '#C4FF0D' : '#C084FC' }};">
                            {{ $isTargetMet ? 'Di Atas BEP' : 'Kejar BEP' }}
                        </span>
                    </header>

                    <div>
                        <p class="text-xl font-bold tabular-nums text-white">{{ $targetPosPct }}% Tercapai</p>
                        <p class="text-xs text-[#9AA68E] mt-0.5">dari target Rp {{ number_format($targetMax, 0, ',', '.') }}</p>
                    </div>

                    <div class="relative mt-4 h-10">
                        <div class="absolute inset-x-0 top-1/2 h-[2px] -translate-y-1/2 rounded-full bg-white/10"></div>
                        <div class="absolute top-1/2 h-10 -translate-y-1/2 rounded-[10px]"
                             style="left: 20%; width: 65%; background: repeating-linear-gradient(135deg, rgba(196,255,13,0.24) 0 6px, rgba(196,255,13,0.08) 6px 12px); box-shadow: inset 0 0 0 1px rgba(196,255,13,0.45);"></div>
                        <div class="absolute top-1/2 h-12 w-[3px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white"
                             style="left: {{ $targetPosPct }}%;"></div>
                    </div>

                    <p class="mt-3 text-xs text-[#9AA68E]">
                        Titik Impas (BEP): <span class="font-semibold text-white">Rp {{ number_format($bepMin, 0, ',', '.') }}</span>
                    </p>
                </section>

                <!-- 8. SHORTCUTS TILE: Akses Cepat Modul -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 xl:col-start-1 xl:row-start-4 justify-between">
                    <header class="mb-3.5 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Akses Cepat Modul</h3>
                        <span class="text-xs font-semibold text-[#C4FF0D]">6 modul aktif</span>
                    </header>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($shortcuts as $s)
                        <a href="{{ $s['href'] }}"
                           class="group rounded-2xl bg-[#181F11] border border-white/[0.07] hover:border-[#C4FF0D]/45 p-4 transition flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-semibold text-sm text-white group-hover:text-[#C4FF0D] transition">{{ $s['label'] }}</span>
                                <span class="text-sm text-[#9AA68E] group-hover:text-[#C4FF0D] group-hover:translate-x-0.5 transition-transform">→</span>
                            </div>
                            <p class="text-xs text-[#9AA68E] mt-1.5 leading-relaxed">{{ $s['desc'] }}</p>
                        </a>
                        @endforeach
                    </div>
                </section>

                <!-- 9. ACTIVITY TILE: Transaksi Terakhir -->
                <section class="bento-tile md:col-span-2 xl:col-span-2 xl:col-start-3 xl:row-start-4 justify-between">
                    <header class="mb-3.5 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-[#9AA68E]">Transaksi Terakhir</h3>
                        <a href="/transaksi" class="text-xs font-semibold text-[#C4FF0D] hover:underline">Lihat semua →</a>
                    </header>

                    @if ($transaksiTerbaru->isEmpty())
                        <p class="text-center text-[#9AA68E] text-sm py-10">Belum ada transaksi tercatat.</p>
                    @else
                        <ul class="-mx-2 -my-1 divide-y divide-white/[0.05]">
                            @foreach ($transaksiTerbaru as $t)
                            <li class="flex items-center gap-3.5 rounded-2xl px-2.5 py-3 transition-colors hover:bg-white/[0.03]">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl border border-white/10 {{ $t->isIncome() ? 'bg-[#C4FF0D]/15 text-[#C4FF0D]' : 'bg-[#C084FC]/15 text-[#C084FC]' }}">
                                    @if ($t->isIncome())
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14m0 0-6-6m6 6 6-6"/></svg>
                                    @else
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h12l-3-3M17 17H5l3 3"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-white">{{ $t->keterangan }}</p>
                                    <p class="truncate text-xs text-[#9AA68E] mt-0.5">
                                        {{ $t->tanggal->format('d M Y') }} · {{ $t->jenis }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold tabular-nums {{ $t->isIncome() ? 'text-[#C4FF0D]' : 'text-[#C084FC]' }}">
                                        {{ ($t->isIncome() ? '+' : '-') . 'Rp ' . number_format($t->jumlah, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-0.5 text-xs font-medium text-[#9AA68E]">
                                        confirmed
                                    </p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

            </div>
        </div>
    </main>
</body>
</html>
