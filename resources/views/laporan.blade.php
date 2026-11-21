<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | AlpetBizz</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bento-body font-sans antialiased text-[#F4F7F0]">
    @include('includes.sidebar')

    @php
        $totalFlowLap = max($totalMasuk + $totalKeluar, 1);
        $pctMasukLap = $totalMasuk > 0 ? round(($totalMasuk / $totalFlowLap) * 100, 1) : 50.0;
        $marginLap = $totalMasuk > 0 ? round(($totalLaba / $totalMasuk) * 100, 1) : 0.0;
        $cntSelesai = $laporanTersimpan->where('status', 'Selesai')->count();
        $cntTinjau = $laporanTersimpan->where('status', 'Dalam Tinjauan')->count();
        $cntDraft = $laporanTersimpan->where('status', 'Draft')->count();
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Laporan Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Laporan &amp; Grafik</h2>
                </div>
            </nav>

            <!-- ══════════════════════════════════════════════════════════
                 ROW 1: WEB3BENTO ANALYTICS & LIVE CHART GRID
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">

                <!-- 1. MAIN ANALYTICS & LIVE CHART TILE (xl:col-span-3) -->
                <section class="bento-tile xl:col-span-3 justify-between">
                    <header class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-white">Grafik Tren Bulanan Pemasukan vs Pengeluaran</h3>
                            <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Perbandingan visual arus kas masuk &amp; keluar per bulan.</p>
                        </div>
                        <div class="bento-pills" role="radiogroup" aria-label="Tipe Grafik">
                            <button type="button" onclick="switchChartType('bar', this)" class="bento-pill active">Bar</button>
                            <button type="button" onclick="switchChartType('line', this)" class="bento-pill">Line</button>
                        </div>
                    </header>

                    <!-- 3 KPI Summary Sub-Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E] flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-[#C4FF0D]"></span>
                                Total Pemasukan
                            </p>
                            <p class="text-xl sm:text-2xl font-bold text-[#C4FF0D] mt-1.5">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">{{ $pemasukanCount }} transaksi tercatat</p>
                        </div>

                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E] flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-[#C084FC]"></span>
                                Total Pengeluaran
                            </p>
                            <p class="text-xl sm:text-2xl font-bold text-[#C084FC] mt-1.5">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
                            <p class="text-xs text-[#9AA68E]/85 mt-1">{{ $pengeluaranCount }} transaksi tercatat</p>
                        </div>

                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-4">
                            <p class="text-xs font-medium text-[#9AA68E] flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-sky-400"></span>
                                Laba Bersih
                            </p>
                            <p class="text-xl sm:text-2xl font-bold text-white mt-1.5">Rp {{ number_format(abs($totalLaba), 0, ',', '.') }}</p>
                            <p class="text-xs text-[#C4FF0D] mt-1">{{ $totalLaba >= 0 ? 'Status: Surplus (Untung)' : 'Status: Defisit (Rugi)' }}</p>
                        </div>
                    </div>

                    <!-- Chart.js Canvas -->
                    <div class="relative w-full h-64 sm:h-72">
                        <canvas id="chartBulanan"></canvas>
                    </div>
                </section>

                <!-- 2. SOLID LIME ACCENT TILE: Ringkasan Neraca & Arsip (xl:col-span-1) -->
                <section class="bento-tile-accent xl:col-span-1 justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-sm font-bold opacity-85">Neraca Periode</h3>
                        </header>

                        <p class="mt-5 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em]">
                            {{ number_format(abs($marginLap), 1) }}<span class="text-2xl">%</span>
                        </p>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Net Profit Margin</p>

                        <!-- Progress Dominasi Pemasukan -->
                        <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                <span class="opacity-80">Porsi Pemasukan</span>
                                <span>{{ $pctMasukLap }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ min(100, max(10, $pctMasukLap)) }}%;"></div>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2.5 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <dt class="opacity-80">Laporan Selesai</dt>
                                <dd class="font-bold">{{ $cntSelesai }} dokumen</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="opacity-80">Dalam Tinjauan</dt>
                                <dd class="font-bold">{{ $cntTinjau }} dokumen</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="opacity-80">Status Draft</dt>
                                <dd class="font-bold">{{ $cntDraft }} dokumen</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6 pt-4 border-t border-[#152208]/15">
                        <p class="text-xs font-semibold opacity-80">Laba Bersih Kumulatif</p>
                        <p class="text-xl font-bold mt-0.5">Rp {{ number_format(abs($totalLaba), 0, ',', '.') }}</p>

                        <a href="#form-laporan"
                           class="mt-3.5 block w-full text-center rounded-2xl py-3 text-xs sm:text-sm font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            + Arsipkan Laporan Baru
                        </a>
                    </div>
                </section>

            </div>

            <!-- ══════════════════════════════════════════════════════════
                 ROW 2: REPORT COMPOSER + ARCHIVES TABLE (12-Column Responsive Split)
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">

                <!-- 3. REPORT COMPOSER TILE (4/12 on desktop, full width on mobile/tablet) -->
                <section id="form-laporan" class="bento-tile xl:col-span-4 justify-between {{ $editLap ? 'border-[#C4FF0D]/60 ring-1 ring-[#C4FF0D]/25' : '' }}">
                    <div>
                        <header class="mb-5 flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                {{ $editLap ? 'Edit Data Laporan' : '+ Simpan Laporan' }}
                            </h3>
                            <span class="text-xs font-semibold uppercase px-2.5 py-1 rounded-full bg-white/[0.06] text-[#9AA68E]">
                                {{ $editLap ? 'Update' : 'Archive' }}
                            </span>
                        </header>

                        <form method="POST" action="/laporan" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @csrf
                            <input type="hidden" name="action" value="{{ $editLap ? 'update' : 'simpan' }}">
                            @if ($editLap)
                            <input type="hidden" name="id" value="{{ $editLap->id }}">
                            @endif

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Judul Laporan</label>
                                <input type="text" name="judul" required placeholder="Contoh: Laporan Q3 2026"
                                    value="{{ $editLap?->judul ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Kategori Laporan</label>
                                <select name="kategori" class="{{ $inputClass }}">
                                    @foreach ($kategoriOptions as $kat)
                                    <option value="{{ $kat }}" {{ ($editLap?->kategori ?? 'Evaluasi Bulanan') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Dari Tanggal</label>
                                <input type="date" name="tanggal_dari" required
                                    value="{{ $editLap?->tanggal_dari?->format('Y-m-d') ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Sampai Tanggal</label>
                                <input type="date" name="tanggal_sampai" required
                                    value="{{ $editLap?->tanggal_sampai?->format('Y-m-d') ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Status Laporan</label>
                                <select name="status" class="{{ $inputClass }}">
                                    @foreach ($statusOptions as $stOpt)
                                    <option value="{{ $stOpt }}" {{ ($editLap?->status ?? 'Selesai') === $stOpt ? 'selected' : '' }}>{{ $stOpt }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">Catatan Evaluasi</label>
                                <input type="text" name="catatan" placeholder="Catatan singkat evaluasi"
                                    value="{{ $editLap?->catatan ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 pt-2 flex flex-col gap-2.5">
                                <button type="submit" class="w-full btn-lime">
                                    {{ $editLap ? 'Simpan Perubahan' : 'Simpan Laporan Ini' }}
                                </button>
                                @if ($editLap)
                                <a href="/laporan" class="w-full btn-ghost-dark">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 4. SAVED REPORTS ARCHIVE TABLE TILE (8/12 on desktop) -->
                <section class="bento-tile xl:col-span-8 !p-0 overflow-hidden justify-between">
                    <div>
                        <header class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">Daftar Laporan Tersimpan</h3>
                                <p class="text-xs sm:text-sm text-[#9AA68E] mt-0.5">Arsip evaluasi periode beserta status peninjauan.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="text"
                                       data-bento-search="laporan-table-body"
                                       placeholder="Cari judul / catatan..."
                                       class="px-4 py-2 rounded-full bg-[#181F11] border border-white/12 text-xs sm:text-sm text-white placeholder:text-[#9AA68E]/60 outline-none w-full sm:w-56">
                                <div class="bento-pills" data-bento-filter-group="laporan-table-body" role="radiogroup" aria-label="Filter Status Laporan">
                                    <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                    <button type="button" data-filter-val="Selesai" class="bento-pill">Selesai</button>
                                    <button type="button" data-filter-val="Dalam Tinjauan" class="bento-pill">Tinjauan</button>
                                    <button type="button" data-filter-val="Draft" class="bento-pill">Draft</button>
                                </div>
                            </div>
                        </header>

                        @if ($laporanTersimpan->isEmpty())
                        <p class="text-center text-[#9AA68E] text-sm py-16">Belum ada laporan tersimpan.</p>
                        @else
                        <div class="overflow-x-auto w-full">
                            <table class="bento-table text-sm min-w-[660px]">
                                <thead>
                                    <tr>
                                        <th class="text-left">Judul &amp; Kategori</th>
                                        <th class="text-left">Periode Laporan</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-left">Catatan Evaluasi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="laporan-table-body">
                                    @foreach ($laporanTersimpan as $l)
                                    <tr data-filter-item data-filter-tag="{{ $l->status }}"
                                        class="{{ (string) $l->id === (string) $editId ? 'bg-[#C4FF0D]/[0.07]' : '' }}">
                                        <td>
                                            <p class="font-semibold text-white">{{ $l->judul }}</p>
                                            <span class="inline-block mt-1 text-xs font-medium px-2.5 py-0.5 rounded-full bg-[#181F11] text-[#9AA68E] border border-white/10">
                                                {{ $l->kategori }}
                                            </span>
                                        </td>
                                        <td class="text-[#9AA68E] text-xs sm:text-sm whitespace-nowrap">
                                            <p class="text-white font-medium">{{ $l->tanggal_dari->format('d M Y') }} – {{ $l->tanggal_sampai->format('d M Y') }}</p>
                                            <p class="text-xs text-[#9AA68E] mt-0.5">Dibuat: {{ $l->created_at?->format('d M Y') ?? '-' }}</p>
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            @if ($l->status === 'Selesai')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30">
                                                    Selesai
                                                </span>
                                            @elseif ($l->status === 'Dalam Tinjauan')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-300 border border-amber-400/30">
                                                    Dalam Tinjauan
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-[#9AA68E]">
                                                    Draft
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-xs sm:text-sm text-[#9AA68E] max-w-xs">
                                            <p class="truncate" title="{{ $l->catatan ?: '-' }}">
                                                {{ $l->catatan ?: '-' }}
                                            </p>
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-3.5">
                                                <a href="/laporan?edit={{ $l->id }}#form-laporan" class="text-xs sm:text-sm text-[#C4FF0D] hover:underline font-semibold transition">Edit</a>
                                                <form method="POST" action="/laporan" onsubmit="return confirm('Hapus arsip laporan ini?')">
                                                    @csrf
                                                    <input type="hidden" name="action" value="hapus">
                                                    <input type="hidden" name="id" value="{{ $l->id }}">
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
                        <span>Total {{ $laporanTersimpan->count() }} arsip laporan periode</span>
                        <span class="text-[#C4FF0D] font-medium">Audit Ready</span>
                    </div>
                </section>

            </div>
        </div>

        <script>
        (function() {
            var chartLabels = @json($labels);
            var dataIn = @json($pemasukanData);
            var dataOut = @json($pengeluaranData);

            function renderBentoChart(type) {
                var ctx = document.getElementById('chartBulanan');
                if (!ctx) return;
                if (window._myChartBulanan) {
                    window._myChartBulanan.destroy();
                }
                window._myChartBulanan = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Pemasukan',
                                data: dataIn,
                                backgroundColor: type === 'line' ? 'rgba(196, 255, 13, 0.2)' : '#C4FF0D',
                                borderColor: '#C4FF0D',
                                borderWidth: 2.5,
                                tension: 0.35,
                                fill: type === 'line',
                                borderRadius: 6
                            },
                            {
                                label: 'Pengeluaran',
                                data: dataOut,
                                backgroundColor: type === 'line' ? 'rgba(192, 132, 252, 0.15)' : '#C084FC',
                                borderColor: '#C084FC',
                                borderWidth: 2.5,
                                tension: 0.35,
                                fill: type === 'line',
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: '#F4F7F0', font: { family: 'Poppins', size: 12 } }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#9AA68E', font: { family: 'Poppins', size: 11 } },
                                grid: { color: 'rgba(255,255,255,0.05)' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#9AA68E',
                                    font: { family: 'Poppins', size: 11 },
                                    callback: function(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v); }
                                },
                                grid: { color: 'rgba(255,255,255,0.06)' }
                            }
                        }
                    }
                });
            }

            window.switchChartType = function(type, btn) {
                if (btn && btn.parentElement) {
                    btn.parentElement.querySelectorAll('.bento-pill').forEach(function(p) { p.classList.remove('active'); });
                    btn.classList.add('active');
                }
                renderBentoChart(type);
            };

            renderBentoChart('bar');
        })();
        </script>
    </main>
</body>
</html>
