<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi | AlpetBizz</title>
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
        $topProducts = array_values($produkStats);
        $topProduct = $topProducts[0] ?? null;
        $totalRevTracked = max(array_sum(array_column($topProducts, 'revenue')), 1);
        $cntHighPrio = $catatanList->where('prioritas', 'Tinggi')->count();
        $smartScore = max(45, min(98, 100 - ($lowStock->count() * 8) - ($unsoldProd->count() * 4)));
        $allocColors = ['#C4FF0D', '#C084FC', '#38BDF8', '#FBBF24', '#64748B'];
    @endphp

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1200px] w-full mx-auto space-y-4">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Rekomendasi Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs uppercase tracking-wider text-[#8E9882]">Workspace /</span>
                    <h2 class="text-sm font-semibold text-white">Sistem Rekomendasi</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="flex items-center gap-2 rounded-full border border-white/10 bg-[#12170D] px-3 py-1.5 text-[12px] font-medium text-[#F4F7F0]">
                        <span class="size-2 rounded-full bg-[#C4FF0D]"></span>
                        {{ $catatanList->count() }} rencana tindak lanjut
                    </span>
                    <a href="#form-rekomendasi" class="rounded-full bg-[#C4FF0D] px-3.5 py-1.5 text-[12px] font-semibold text-[#152208] hover:brightness-105 transition">
                        + Tambah Rencana
                    </a>
                </div>
            </nav>

            <!-- ══════════════════════════════════════════════════════════
                 WEB3BENTO 4-COLUMN GRID — REKOMENDASI
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4">

                <!-- 1. BEST SELLER & REVENUE LEADERBOARD TILE (col-span-2) -->
                <section id="bento-allocation-tile" class="bento-tile sm:col-span-2 lg:col-span-2 justify-between">
                    <header class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Produk Terlaris &amp; Kontribusi</h3>
                            <p class="text-xs text-[#8E9882] mt-0.5">Peringkat otomatis berdasarkan frekuensi transaksi penjualan.</p>
                        </div>
                        <span class="rounded-full bg-[#C4FF0D]/15 px-2.5 py-1 text-[11px] font-semibold text-[#C4FF0D]">
                            Top Sales
                        </span>
                    </header>

                    @if (empty($produkStats))
                        <div class="py-10 text-center">
                            <p class="text-[#8E9882] text-xs">Belum ada data penjualan terhubung ke produk.</p>
                        </div>
                    @else
                        <!-- Segmented Revenue Share Bar -->
                        <div class="mt-4 flex h-7 gap-1">
                            @foreach (array_slice($topProducts, 0, 5) as $idx => $s)
                                @php
                                    $sharePct = max(12, round(($s['revenue'] / $totalRevTracked) * 100));
                                    $cHex = $allocColors[$idx % count($allocColors)];
                                @endphp
                                <span data-alloc-segment class="rounded-[7px] transition-all duration-300"
                                      style="flex-basis: {{ $sharePct }}%; background: {{ $cHex }};"></span>
                            @endforeach
                        </div>

                        <!-- Leaderboard Rows -->
                        <div class="mt-4 space-y-2">
                            @foreach ($produkStats as $s)
                                @php $cHex = $allocColors[($loop->index) % count($allocColors)]; @endphp
                                <div data-alloc-item class="flex items-center justify-between rounded-2xl bg-[#181F11] border border-white/[0.06] px-3.5 py-2.5 hover:border-[#C4FF0D]/30 transition cursor-pointer">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold shrink-0
                                            {{ $loop->first ? 'bg-[#C4FF0D] text-[#152208]' : 'bg-white/10 text-white' }}">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="size-2 rounded-full shrink-0" style="background: {{ $cHex }};"></span>
                                        <span class="text-sm font-medium text-white truncate">{{ $s['nama'] }}</span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs font-semibold text-[#C4FF0D]">{{ $s['count'] }}x jual</p>
                                        <p class="text-[11px] text-[#8E9882]">Rp {{ number_format($s['revenue'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- 2. RESTOCK & UNSOLD RADAR TILE (col-span-1) -->
                <section class="bento-tile justify-between">
                    <div>
                        <header class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="text-[13px] font-medium text-[#8E9882]">Perlu Restock &amp; Promosi</h3>
                            <span class="text-[11px] text-amber-300 font-semibold">Radar</span>
                        </header>

                        <!-- Perlu Restock List -->
                        <div class="space-y-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C4FF0D]">Perlu Restock ({{ $lowStock->count() }})</p>
                            @if ($lowStock->isEmpty())
                                <p class="text-[#8E9882] text-xs py-2">Semua stok aman.</p>
                            @else
                                @foreach ($lowStock->take(3) as $p)
                                <div class="rounded-2xl bg-[#181F11] border border-white/[0.06] p-2.5">
                                    <div class="flex justify-between items-center">
                                        <p class="text-xs font-semibold text-white truncate">{{ $p->nama }}</p>
                                        <span class="text-[10px] text-amber-300 font-semibold">{{ $p->isOutOfStock() ? 'Habis' : 'Menipis' }}</span>
                                    </div>
                                    <p class="text-[11px] text-[#8E9882] mt-0.5">
                                        Stok: {{ $p->stok }} / Min: {{ $p->stok_min }} · <span class="text-[#C4FF0D]">+{{ $p->recommendedRestockAmount() }} {{ $p->satuan }}</span>
                                    </p>
                                </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Belum Terjual List -->
                        @if ($unsoldProd->isNotEmpty())
                        <div class="mt-3.5 pt-3 border-t border-white/[0.08] space-y-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C084FC]">Belum Terjual ({{ $unsoldProd->count() }})</p>
                            @foreach ($unsoldProd->take(3) as $p)
                            <div class="rounded-2xl bg-[#181F11] border border-white/[0.06] p-2.5 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-white truncate">{{ $p->nama }}</p>
                                    <p class="text-[10px] text-[#8E9882]">Stok: {{ $p->stok }} · Pertimbangkan promosi</p>
                                </div>
                                <span class="shrink-0 size-2 rounded-full bg-[#C084FC]"></span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-white/[0.08] flex items-center justify-between text-[11px]">
                        <span class="text-[#8E9882]">Saran otomatis</span>
                        <a href="/stok" class="text-[#C4FF0D] font-semibold hover:underline">Buka Gudang →</a>
                    </div>
                </section>

                <!-- 3. SOLID LIME ACCENT TILE: Skor Optimasi Bisnis (col-span-1) -->
                <section class="bento-tile-accent justify-between">
                    <div>
                        <header class="flex items-center justify-between">
                            <h3 class="text-[13px] font-semibold opacity-80">Indeks Optimasi</h3>
                            <span class="flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                                  style="background: rgba(21, 34, 8, 0.14);">
                                <span class="size-1.5 rounded-full bg-[#152208]"></span>
                                AI Insight
                            </span>
                        </header>

                        <p class="mt-4 text-[44px] font-semibold leading-none tracking-[-0.04em]">
                            {{ $smartScore }}<span class="text-[20px]">%</span>
                        </p>
                        <p class="mt-1 text-[12px] font-medium opacity-75">Skor Kesiapan Operasional</p>

                        <div class="mt-4 rounded-2xl p-3" style="background: rgba(21, 34, 8, 0.1);">
                            <div class="flex items-baseline justify-between text-[12px] font-semibold">
                                <span class="opacity-75">Prioritas Tinggi</span>
                                <span>{{ $cntHighPrio }} catatan</span>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                <div class="h-full rounded-full bg-[#152208]" style="width: {{ $smartScore }}%;"></div>
                            </div>
                        </div>

                        <dl class="mt-3.5 space-y-1.5 text-[12px]">
                            <div class="flex justify-between">
                                <dt class="opacity-75">Produk Terlaris</dt>
                                <dd class="font-bold truncate max-w-[110px]">{{ $topProduct['nama'] ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="opacity-75">Kandidat Promo</dt>
                                <dd class="font-bold">{{ $unsoldProd->count() }} SKU</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#152208]/15">
                        <a href="#form-rekomendasi"
                           class="block w-full text-center rounded-2xl py-2.5 text-xs font-semibold transition-transform active:scale-[0.98]"
                           style="background: #152208; color: #C4FF0D;">
                            + Buat Rencana Baru
                        </a>
                    </div>
                </section>

                <!-- 4. ACTION PLAN COMPOSER TILE (lg:col-span-1) -->
                <section id="form-rekomendasi" class="bento-tile lg:col-span-1 justify-between {{ $editCat ? 'border-[#C4FF0D]/60 ring-1 ring-[#C4FF0D]/25' : '' }}">
                    <div>
                        <header class="mb-4 flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                                <span class="size-2 rounded-full {{ $editCat ? 'bg-[#C084FC]' : 'bg-[#C4FF0D]' }}"></span>
                                {{ $editCat ? 'Edit Catatan' : '+ Tambah Catatan' }}
                            </h3>
                            <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-white/[0.06] text-[#8E9882]">
                                Action
                            </span>
                        </header>

                        <form method="POST" action="/rekomendasi" class="space-y-3.5">
                            @csrf
                            <input type="hidden" name="action" value="{{ $editCat ? 'update' : 'tambah' }}">
                            @if ($editCat)
                            <input type="hidden" name="id" value="{{ $editCat->id }}">
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-[#8E9882] mb-1.5">Judul Tindak Lanjut</label>
                                <input type="text" name="judul" required placeholder="Contoh: Promo Paket Bundling"
                                    value="{{ $editCat?->judul ?? '' }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#8E9882] mb-1.5">Keterangan Strategi</label>
                                <textarea name="isi" required rows="4" placeholder="Jelaskan tindak lanjut yang akan dilakukan..."
                                    class="{{ $inputClass }} resize-none">{{ $editCat?->isi ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#8E9882] mb-1.5">Tingkat Prioritas</label>
                                <select name="prioritas" class="{{ $inputClass }}">
                                    @foreach ($prioritasOpt as $pr)
                                    <option value="{{ $pr }}" {{ ($editCat?->prioritas ?? 'Sedang') === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pt-2 flex flex-col gap-2">
                                <button type="submit" class="w-full btn-lime">
                                    {{ $editCat ? 'Simpan Perubahan' : 'Tambah Catatan' }}
                                </button>
                                @if ($editCat)
                                <a href="/rekomendasi" class="w-full btn-ghost-dark">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <!-- 5. ACTION PLAN BENTO BOARD TILE (sm:col-span-2 lg:col-span-3) -->
                <section class="bento-tile sm:col-span-2 lg:col-span-3 justify-between">
                    <div>
                        <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm sm:text-base font-semibold text-white">Daftar Tindak Lanjut Bisnis</h3>
                                <p class="text-xs text-[#8E9882] mt-0.5">Filter rencana aksi berdasarkan tingkat prioritas eksekusi.</p>
                            </div>
                            <div class="bento-pills" data-bento-filter-group="rekomendasi-board" role="radiogroup" aria-label="Filter Prioritas">
                                <button type="button" data-filter-val="all" class="bento-pill active">Semua</button>
                                <button type="button" data-filter-val="Tinggi" class="bento-pill">Tinggi</button>
                                <button type="button" data-filter-val="Sedang" class="bento-pill">Sedang</button>
                                <button type="button" data-filter-val="Rendah" class="bento-pill">Rendah</button>
                            </div>
                        </header>

                        @if ($catatanList->isEmpty())
                            <div class="text-center py-14">
                                <p class="text-[#8E9882] text-sm">Belum ada catatan tindak lanjut. Tambahkan melalui panel di samping.</p>
                            </div>
                        @else
                            <div id="rekomendasi-board" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($catatanList as $c)
                                <div data-filter-item data-filter-tag="{{ $c->prioritas }}"
                                     class="rounded-2xl bg-[#181F11] border {{ (string) $c->id === (string) $editId ? 'border-[#C4FF0D]' : 'border-white/[0.06] hover:border-white/20' }} p-4 flex flex-col justify-between transition">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $c->priorityBadgeClass() }}">
                                                Prioritas {{ $c->prioritas }}
                                            </span>
                                            <span class="text-[11px] text-[#8E9882]">{{ $c->dibuat->format('d M Y') }}</span>
                                        </div>
                                        <h4 class="font-semibold text-white text-sm">{{ $c->judul }}</h4>
                                        <p class="text-xs text-[#8E9882] leading-relaxed mt-1.5">{{ $c->isi }}</p>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-end gap-4">
                                        <a href="/rekomendasi?edit={{ $c->id }}#form-rekomendasi" class="text-xs text-[#C4FF0D] hover:underline font-semibold">Edit</a>
                                        <form method="POST" action="/rekomendasi" onsubmit="return confirm('Hapus catatan ini?')">
                                            @csrf
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="{{ $c->id }}">
                                            <button class="text-xs text-rose-400 hover:underline font-semibold">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-white/[0.06] flex items-center justify-between text-[11px] text-[#8E9882]">
                        <span>{{ $catatanList->count() }} rencana aksi tersimpan</span>
                        <span class="text-[#C4FF0D] font-medium">Strategy Board Active</span>
                    </div>
                </section>

            </div>
        </div>
    </main>
</body>
</html>
