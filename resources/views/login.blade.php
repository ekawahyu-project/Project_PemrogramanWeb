<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpetBizz — Sistem Arus Kas, Stok &amp; Konsultan AI UMKM</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}?v={{ filemtime(public_path('assets/css/login.css')) }}">
</head>
<body class="font-sans antialiased">

<div id="landing-scroll-progress" aria-hidden="true"></div>

<!-- ══════════════════════════════════════════════════════════════
     MINIMALIST HAIRLINE STICKY NAVIGATION
     ══════════════════════════════════════════════════════════════ -->
<nav id="landing-nav" class="landing-nav" aria-label="Navigasi Utama">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <a href="#hero" class="flex items-center gap-3 text-white no-underline group">
            <img src="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}"
                 alt="Logo AlpetBizz"
                 class="w-9 h-9 sm:w-10 sm:h-10 object-contain shrink-0 drop-shadow-[0_0_14px_rgba(196,255,13,0.45)] transition-transform duration-200 group-hover:scale-105">
            <span class="text-lg sm:text-xl font-bold tracking-tight text-white">AlpetBizz</span>
        </a>

        <div class="hidden md:flex items-center gap-8">
            <a href="#produk" class="landing-nav-link">Produk</a>
            <a href="#alpetbizz-ai" class="landing-nav-link">AlpetBizz AI</a>
            <a href="#cara-kerja" class="landing-nav-link">Cara Kerja</a>
            <a href="#faq" class="landing-nav-link">FAQ</a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" id="nav-login-btn" onclick="openLogin('login')"
                class="text-white/80 hover:text-white transition-colors text-xs sm:text-sm font-medium bg-transparent border-0 cursor-pointer px-3 py-2 rounded-lg hover:bg-white/[0.06]">
                Masuk
            </button>
            <button type="button" onclick="openLogin('register')"
                class="btn-editorial px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm">
                Daftar Akun
            </button>
        </div>
    </div>
</nav>

<!-- ══════════════════════════════════════════════════════════════
     ACT I — STICKY PARALLAX CURTAIN HERO
     ══════════════════════════════════════════════════════════════ -->
<div id="hero" class="hero-curtain-wrap">
    <section id="hero-curtain" class="hero-curtain">
        <div class="hero-hairlines" aria-hidden="true"></div>
        <div class="hero-watermark" data-parallax="0.16" data-parallax-axis="x" aria-hidden="true">ALPETBIZZ</div>

        <div class="relative z-10 flex-1 flex items-center px-5 sm:px-8 py-6 sm:py-8">
            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-8 xl:gap-10 items-center">

                <!-- Left: Balanced 3-Line Editorial Typography & Instant Entry -->
                <div class="lg:col-span-7 space-y-6">
                    <h1 class="anim-hero-1 font-extrabold tracking-[-0.035em] leading-[1.06] text-white"
                        style="font-size: clamp(2.25rem, 4.5vw, 4.35rem);">
                        <span class="block sm:whitespace-nowrap">Kendalikan Kas,</span>
                        <span class="block sm:whitespace-nowrap">Stok, dan <span class="text-[#C4FF0D]">Laba</span></span>
                        <span class="block sm:whitespace-nowrap">Tanpa Ribet.</span>
                    </h1>

                    <p class="anim-hero-2 text-sm sm:text-base max-w-[520px] leading-[1.72] text-[#9AA68E]">
                        Ruang kerja privat untuk toko kelontong dan UMKM: pencatatan transaksi harian, mutasi stok atomik, buku kasbon, hingga konsultan bisnis berbasis data nyata.
                    </p>

                    <!-- Direct Login / Quick Start Input -->
                    <div class="anim-hero-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 max-w-[490px] pt-1">
                        <input
                            id="hero-quick-email"
                            type="text"
                            placeholder="Username atau email usaha..."
                            aria-label="Username atau email"
                            class="flex-1 px-4 py-3.5 rounded-xl bg-white/[0.06] border border-white/15 text-white placeholder:text-white/35 outline-none text-sm transition focus:border-[#C4FF0D] focus:bg-white/[0.09]"
                        >
                        <button
                            type="button"
                            onclick="startFromHeroCta()"
                            class="btn-editorial px-6 py-3.5 rounded-xl text-sm shrink-0"
                        >
                            Mulai Sekarang &rarr;
                        </button>
                    </div>
                </div>

                <!-- Right: Custom 3D Dark Matte & Acid-Lime Editorial Sculpture (Parallax + 3D Mouse Tilt) -->
                <div class="lg:col-span-5 hero-stage flex items-center justify-center">
                    <div class="hero-3d-wrap relative w-full max-w-[400px] sm:max-w-[440px] lg:max-w-[490px] mx-auto" data-parallax="-0.1">
                        <div class="hero-3d-aura" aria-hidden="true"></div>
                        <div class="hero-3d-float">
                            <div id="hero-3d-figure" class="hero-3d-figure">
                                <img src="{{ asset('img/hero-3d.webp') }}?v={{ filemtime(public_path('img/hero-3d.webp')) }}"
                                     alt="Visual 3D analitik keuangan, stok, dan pertumbuhan laba AlpetBizz"
                                     width="880"
                                     height="880"
                                     loading="eager"
                                     decoding="async"
                                     class="w-full h-auto block select-none pointer-events-none">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Minimal Scroll Hint -->
        <div class="relative z-10 px-5 sm:px-8 pb-7 max-w-7xl w-full mx-auto flex items-center justify-between text-[11px] uppercase tracking-[0.18em] text-white/40">
            <span>Scroll untuk jelajahi</span>
            <a href="#produk" class="text-white/55 hover:text-[#C4FF0D] transition no-underline">Produk &darr;</a>
        </div>
    </section>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ACT II — OVERLAPPING RISING SHEET + VELOCITY KINETIC MARQUEE
     ══════════════════════════════════════════════════════════════ -->
<div class="overlapping-sheet">

    <!-- Velocity-Driven Kinetic Typography Ribbon (Accelerates & Reverses with Scroll Velocity!) -->
    <div class="velocity-marquee-wrap" aria-hidden="true">
        <div id="velocity-marquee-track" class="velocity-marquee-track">
            @php
                $tickerWords = [
                    ['text' => 'Arus Kas Real-Time', 'outline' => false],
                    ['text' => 'Mutasi Stok Atomik', 'outline' => true],
                    ['text' => 'Buku Kasbon & Piutang', 'outline' => false],
                    ['text' => 'Konsultan AI', 'outline' => true],
                    ['text' => 'Grafik Laba Bulanan', 'outline' => false],
                    ['text' => 'Isolasi Multi-Tenant', 'outline' => true],
                ];
            @endphp
            @foreach ([0, 1] as $loopCopy)
                @foreach ($tickerWords as $tw)
                    <span class="velocity-word {{ $tw['outline'] ? 'velocity-word-outline' : '' }}">{{ $tw['text'] }}</span>
                    <span class="velocity-sep">/</span>
                @endforeach
            @endforeach
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         ACT III — STICKY OVERLAPPING SCROLL STORY DECK (#produk)
         ══════════════════════════════════════════════════════════════ -->
    <section id="produk" class="px-5 sm:px-8 py-20 sm:py-28">
        <div class="max-w-7xl mx-auto">

            <!-- Editorial Intro Header -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-14 sm:mb-16" data-reveal="up">
                <div class="lg:col-span-7">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-[-0.03em] leading-[1.08] text-white">
                        Dirancang untuk ritme nyata<br class="hidden sm:inline"> toko harian Anda.
                    </h2>
                </div>
                <p class="lg:col-span-5 text-sm sm:text-base text-[#9AA68E] leading-relaxed">
                    Setiap modul terhubung ke satu sumber data yang sama. Saat Anda mencatat transaksi, mutasi stok, atau cicilan kasbon, seluruh laporan dan rekomendasi AI ikut bergerak seketika.
                </p>
            </div>

            <!-- 4 STICKY OVERLAPPING ARCHITECTURAL SLABS -->
            <div class="overlap-deck">

                <!-- SLAB 01: ARUS KAS & MARGIN -->
                <article class="overlap-slab overlap-slab-dark p-6 sm:p-10 lg:p-12" style="--stack-idx: 0;">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-5 space-y-5">
                            <h3 class="text-2xl sm:text-3xl lg:text-[34px] font-bold tracking-[-0.03em] leading-[1.12] text-white">
                                Setiap rupiah masuk dan keluar langsung terukur labanya.
                            </h3>
                            <p class="text-sm text-[#9AA68E] leading-relaxed">
                                Pisahkan uang modal, biaya operasional, dan penjualan produk dalam satu buku kas digital. Margin keuntungan serta grafik tren bulanan terbentuk otomatis tanpa rumus manual.
                            </p>
                            <dl class="pt-4 border-t border-white/10 grid grid-cols-2 gap-4 text-xs">
                                <div>
                                    <dt class="text-[#8E9882]">Pencatatan</dt>
                                    <dd class="mt-1 font-semibold text-white">Tautan langsung ke SKU</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8E9882]">Analitik</dt>
                                    <dd class="mt-1 font-semibold text-[#C4FF0D]">Grafik Bar &amp; Line Bulanan</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="lg:col-span-7">
                            <div class="rounded-2xl bg-[#0A0E07] border border-white/10 overflow-hidden">
                                <div class="px-5 py-3.5 border-b border-white/[0.07] flex items-center justify-between text-xs">
                                    <span class="font-semibold text-white">Ringkasan Buku Kas</span>
                                    <span class="font-semibold text-[#C4FF0D] tabular-nums">Surplus +Rp 7.620.000</span>
                                </div>
                                <div class="divide-y divide-white/[0.06] text-xs sm:text-sm">
                                    <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-medium text-white truncate">Penjualan Kopi Arabika 250g</p>
                                            <p class="text-[11px] text-[#8E9882]">Pemasukan &middot; Produk Terkait</p>
                                        </div>
                                        <span class="font-bold text-[#C4FF0D] tabular-nums shrink-0">+Rp 440.000</span>
                                    </div>
                                    <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-medium text-white truncate">Penjualan Tas Kanvas Polos</p>
                                            <p class="text-[11px] text-[#8E9882]">Pemasukan &middot; Produk Terkait</p>
                                        </div>
                                        <span class="font-bold text-[#C4FF0D] tabular-nums shrink-0">+Rp 315.000</span>
                                    </div>
                                    <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-medium text-white truncate">Belanja Bahan Baku &amp; Kemasan</p>
                                            <p class="text-[11px] text-[#8E9882]">Pengeluaran &middot; Operasional</p>
                                        </div>
                                        <span class="font-bold text-[#C084FC] tabular-nums shrink-0">-Rp 200.000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- SLAB 02: INVENTARIS & MUTASI STOK ATOMIK -->
                <article class="overlap-slab overlap-slab-deep p-6 sm:p-10 lg:p-12" style="--stack-idx: 1;">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-5 space-y-5">
                            <h3 class="text-2xl sm:text-3xl lg:text-[34px] font-bold tracking-[-0.03em] leading-[1.12] text-white">
                                Stok gudang dikunci atomik. Anti minus, anti selisih.
                            </h3>
                            <p class="text-sm text-[#9AA68E] leading-relaxed">
                                Setiap barang masuk dan keluar diproses di dalam transaksi database terkunci. Jika catatan mutasi diedit atau dihapus, saldo stok otomatis dihitung ulang secara presisi.
                            </p>
                            <dl class="pt-4 border-t border-white/10 grid grid-cols-2 gap-4 text-xs">
                                <div>
                                    <dt class="text-[#8E9882]">Proteksi Harga</dt>
                                    <dd class="mt-1 font-semibold text-white">Validasi harga jual &ge; modal</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8E9882]">Radar Gudang</dt>
                                    <dd class="mt-1 font-semibold text-amber-300">Peringatan stok menipis</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="lg:col-span-7 space-y-3">
                            @php
                                $stockDemo = [
                                    ['nama' => 'Kopi Arabika 250g', 'stok' => '25 pack', 'min' => 'Min. 5', 'pct' => 92, 'status' => 'Normal', 'bar' => '#C4FF0D'],
                                    ['nama' => 'Teh Hijau 100g', 'stok' => '8 pack', 'min' => 'Min. 10', 'pct' => 42, 'status' => 'Menipis', 'bar' => '#FBBF24'],
                                    ['nama' => 'Gula Pasir 1kg', 'stok' => '3 kg', 'min' => 'Min. 5', 'pct' => 28, 'status' => 'Kritis', 'bar' => '#C084FC'],
                                ];
                            @endphp
                            @foreach ($stockDemo as $sd)
                            <div class="rounded-2xl bg-[#0A0E07] border border-white/[0.08] p-4 sm:p-5">
                                <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                                    <span class="font-semibold text-white">{{ $sd['nama'] }}</span>
                                    <span class="tabular-nums text-[#9AA68E]">
                                        <strong class="text-white">{{ $sd['stok'] }}</strong> &middot; {{ $sd['min'] }}
                                    </span>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-white/10 overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ $sd['pct'] }}%; background: {{ $sd['bar'] }};"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <!-- SLAB 03: HIGH-CONTRAST SIGNATURE LIME — KASBON & PIUTANG -->
                <article class="overlap-slab overlap-slab-lime p-6 sm:p-10 lg:p-12" style="--stack-idx: 2;">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-5 space-y-5">
                            <h3 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold tracking-[-0.03em] leading-[1.1] text-[#0E1605]">
                                Catat kasbon pelanggan tanpa lagi lupa jatuh tempo.
                            </h3>
                            <p class="text-sm font-medium leading-relaxed text-[#0E1605]/80">
                                Lacak piutang pelanggan maupun tagihan supplier dalam satu tempat. Catat pembayaran cicilan bertahap dan pantau rasio kolektibilitas kas usaha secara langsung.
                            </p>
                            <div class="pt-3 flex items-baseline gap-6 border-t border-[#0E1605]/15">
                                <div>
                                    <p class="text-3xl sm:text-4xl font-extrabold tracking-tight tabular-nums">82,5%</p>
                                    <p class="text-xs font-semibold opacity-75">Rasio Piutang Tertagih</p>
                                </div>
                                <div>
                                    <p class="text-3xl sm:text-4xl font-extrabold tracking-tight tabular-nums">3 Status</p>
                                    <p class="text-xs font-semibold opacity-75">Belum &rarr; Cicilan &rarr; Lunas</p>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-7">
                            <div class="rounded-2xl bg-[#0E1605] text-[#F4F7F0] p-5 sm:p-6 space-y-3.5 shadow-2xl">
                                <div class="flex items-center justify-between text-xs border-b border-white/10 pb-3">
                                    <span class="font-semibold text-white">Radar Jatuh Tempo Aktif</span>
                                    <span class="text-[#C4FF0D] font-semibold">Auto-Status</span>
                                </div>
                                <div class="rounded-xl bg-white/[0.04] border border-white/[0.07] p-4 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">Warung Kopi Senja</p>
                                        <p class="text-xs text-[#9AA68E]">Piutang &middot; Terbayar Rp 250.000 / Rp 550.000</p>
                                    </div>
                                    <span class="shrink-0 text-xs font-bold text-[#C4FF0D] tabular-nums">Cicilan</span>
                                </div>
                                <div class="rounded-xl bg-white/[0.04] border border-white/[0.07] p-4 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">PT Nusantara Gula Murni</p>
                                        <p class="text-xs text-[#9AA68E]">Hutang Supplier &middot; Sisa Rp 350.000</p>
                                    </div>
                                    <span class="shrink-0 text-xs font-bold text-amber-300 tabular-nums">Jatuh Tempo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- SLAB 04: ALPETBIZZ GEMINI AI INTERACTIVE CONSOLE (#alpetbizz-ai) -->
                <article id="alpetbizz-ai" class="overlap-slab overlap-slab-ai p-6 sm:p-10 lg:p-12" style="--stack-idx: 3;">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                        <div class="lg:col-span-5 space-y-5">
                            <h3 class="text-2xl sm:text-3xl lg:text-[34px] font-bold tracking-[-0.03em] leading-[1.12] text-white">
                                Konsultan bisnis AI yang membaca data tokomu langsung.
                            </h3>
                            <p class="text-sm text-[#9AA68E] leading-relaxed">
                                Pilih skenario di bawah untuk melihat bagaimana AlpetBizz AI menerjemahkan data omzet, stok kritis, dan kasbon menjadi langkah aksi nyata.
                            </p>

                            <div class="space-y-2.5 pt-1">
                                <button type="button" data-ai-demo="laba" aria-pressed="true" class="ai-switch-btn active">
                                    <div class="flex items-center justify-between gap-2 text-xs sm:text-sm font-semibold text-white">
                                        <span>Evaluasi Laba &amp; Margin</span>
                                        <span class="text-[#C4FF0D]">&rarr;</span>
                                    </div>
                                </button>
                                <button type="button" data-ai-demo="restock" aria-pressed="false" class="ai-switch-btn">
                                    <div class="flex items-center justify-between gap-2 text-xs sm:text-sm font-semibold text-white">
                                        <span>Prioritas Restock Gudang</span>
                                        <span class="text-[#C4FF0D]">&rarr;</span>
                                    </div>
                                </button>
                                <button type="button" data-ai-demo="kasbon" aria-pressed="false" class="ai-switch-btn">
                                    <div class="flex items-center justify-between gap-2 text-xs sm:text-sm font-semibold text-white">
                                        <span>Strategi Penagihan Kasbon</span>
                                        <span class="text-[#C4FF0D]">&rarr;</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="lg:col-span-7">
                            <div class="rounded-2xl bg-[#090D06] border border-white/10 p-5 sm:p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-white/[0.08]">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}"
                                             alt="AlpetBizz AI"
                                             class="w-8 h-8 object-contain">
                                        <div>
                                            <p class="text-xs font-bold text-white">AlpetBizz AI Consultant</p>
                                            <p id="ai-demo-context" class="text-[11px] text-[#C4FF0D]">Omzet Rp 19,8 jt &middot; Margin 38,4%</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="openLogin('register')"
                                            class="text-xs font-semibold text-[#C4FF0D] hover:underline bg-transparent border-0 cursor-pointer">
                                        Coba dengan Data Sendiri &rarr;
                                    </button>
                                </div>

                                <div class="py-5 space-y-4 min-h-[225px]">
                                    <div class="flex justify-end">
                                        <div class="max-w-[88%] rounded-2xl rounded-br-sm bg-[#C4FF0D] text-[#0E1605] px-4 py-2.5 text-xs sm:text-sm font-semibold">
                                            <span id="ai-demo-question">Analisis laba bersih &amp; margin toko bulan ini. Apa 3 langkah prioritas?</span>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl rounded-bl-sm bg-[#12180D] border border-white/[0.08] p-4 text-xs sm:text-sm text-[#dce5d3] leading-relaxed">
                                        <div id="ai-demo-typing" class="hidden items-center gap-1.5 py-1.5">
                                            <span class="ai-typing-dot"></span>
                                            <span class="ai-typing-dot"></span>
                                            <span class="ai-typing-dot"></span>
                                            <span class="text-xs text-[#8E9882] ml-1.5">Menganalisis snapshot toko...</span>
                                        </div>
                                        <div id="ai-demo-response" class="space-y-2.5"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════
         ACT IV — VELOCITY-SKEW ALUR CARA KERJA (#cara-kerja)
         ══════════════════════════════════════════════════════════════ -->
    <section id="cara-kerja" class="px-5 sm:px-8 py-20 sm:py-28 border-t border-white/[0.08]">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">

            <div class="lg:col-span-5 lg:sticky lg:top-28 space-y-5" data-reveal="left">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-[-0.03em] leading-[1.1] text-white">
                    Dari buka akun sampai pembukuan rapi dalam hitungan menit.
                </h2>
                <p class="text-sm sm:text-base text-[#9AA68E] leading-relaxed">
                    Tidak perlu latar belakang akuntansi atau instalasi rumit. Ikuti empat langkah sederhana berikut untuk merapikan operasional toko Anda setiap hari.
                </p>
                <div class="pt-1">
                    <button type="button" onclick="openLogin('register')"
                            class="btn-editorial px-6 py-3.5 rounded-xl text-sm">
                        Mulai Langkah Pertama &rarr;
                    </button>
                </div>
            </div>

            <div class="lg:col-span-7">
                @php
                    $workflowSteps = [
                        [
                            'step' => 'Langkah 1',
                            'title' => 'Buat Akun & Profil Usaha',
                            'desc' => 'Daftar gratis dengan nama toko dan kategori usaha Anda. Dalam kurang dari satu menit, ruang kerja privat AlpetBizz langsung siap dipakai dari HP maupun laptop.',
                        ],
                        [
                            'step' => 'Langkah 2',
                            'title' => 'Masukkan Produk & Batas Stok',
                            'desc' => 'Input katalog barang beserta harga modal, harga jual, dan batas stok minimum. Sistem otomatis menghitung margin per produk serta menyiapkan peringatan saat stok menipis.',
                        ],
                        [
                            'step' => 'Langkah 3',
                            'title' => 'Catat Transaksi & Kasbon Harian',
                            'desc' => 'Setiap penjualan, belanja bahan baku, mutasi stok masuk/keluar, hingga cicilan kasbon pelanggan tercatat rapi sambil berjualan—tanpa buku tulis atau rekap malam hari.',
                        ],
                        [
                            'step' => 'Langkah 4',
                            'title' => 'Pantau Laba & Tanya Konsultan AI',
                            'desc' => 'Grafik arus kas bulanan, produk terlaris, dan daftar piutang tersusun otomatis. Kapan pun butuh saran promo atau prioritas restock, diskusikan langsung bersama AlpetBizz AI.',
                        ],
                    ];
                @endphp

                @foreach ($workflowSteps as $ws)
                <div class="spec-row grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-6 items-start" data-velocity-skew>
                    <div class="sm:col-span-3 pt-0.5">
                        <span class="text-lg sm:text-xl font-extrabold text-[#C4FF0D] tabular-nums">{{ $ws['step'] }}</span>
                    </div>
                    <div class="sm:col-span-9">
                        <h3 class="text-base sm:text-lg font-bold text-white">{{ $ws['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-[#9AA68E] leading-relaxed">{{ $ws['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════
         ACT V — HAIRLINE EDITORIAL FAQ (#faq) & CLOSING CALLOUT
         ══════════════════════════════════════════════════════════════ -->
    <section id="faq" class="px-5 sm:px-8 py-20 sm:py-24 border-t border-white/[0.08]">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
            <div class="lg:col-span-4" data-reveal="up">
                <h2 class="text-3xl font-bold tracking-[-0.03em] text-white">Hal yang sering ditanyakan.</h2>
            </div>

            <div class="lg:col-span-8" data-reveal="up" data-reveal-delay="80">
                @php
                    $faqs = [
                        [
                            'q' => 'Apakah AlpetBizz gratis untuk UMKM dan toko kelontong?',
                            'a' => 'Ya, seluruh fitur mulai dari pencatatan arus kas, katalog produk, mutasi stok atomik, buku kasbon, laporan grafik, hingga konsultasi AI bisnis dapat digunakan secara gratis.',
                        ],
                        [
                            'q' => 'Apakah data keuangan toko saya aman dari akun pengguna lain?',
                            'a' => 'Aman. Seluruh data diisolasi ketat per akun (multi-tenant) pada level query dan validasi, disertai proteksi CSRF, sesi HttpOnly Secure, dan rate limiting.',
                        ],
                        [
                            'q' => 'Bagaimana AlpetBizz AI memberi saran yang relevan dengan toko saya?',
                            'a' => 'Setiap pertanyaan yang dikirimkan otomatis disertai ringkasan data toko aktif Anda (omzet, margin, produk terlaris, stok kritis, dan kasbon jatuh tempo) sehingga saran AI selalu konkret.',
                        ],
                        [
                            'q' => 'Bagaimana jika saya salah mencatat stok masuk atau keluar?',
                            'a' => 'Anda cukup mengedit atau menghapus log mutasi di menu Stok. Sistem akan otomatis membalikkan (reversal) jumlah stok produk secara atomik agar tetap akurat.',
                        ],
                    ];
                @endphp

                @foreach ($faqs as $idx => $faq)
                <div class="faq-row {{ $idx === 0 ? 'open' : '' }}">
                    <button type="button" class="faq-trigger" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                        <span>{{ $faq['q'] }}</span>
                        <span class="faq-plus" aria-hidden="true">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 5v14M5 12h14"/>
                            </svg>
                        </span>
                    </button>
                    <div class="faq-panel">
                        <div class="faq-panel-inner">
                            <p class="pb-5 pr-8 text-sm text-[#9AA68E] leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FULL-BLEED ASYMMETRIC CLOSING BAND -->
    <section class="px-5 sm:px-8 py-20 sm:py-24 border-t border-white/[0.08] bg-gradient-to-b from-[#0c1108] to-[#070a05]">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-end justify-between gap-8" data-reveal="up">
            <div class="max-w-2xl">
                <h2 class="font-extrabold tracking-[-0.04em] leading-[1.02] text-white"
                    style="font-size: clamp(2.1rem, 4.8vw, 3.9rem);">
                    Rapikan kas &amp; stok tokomu dalam satu menit.
                </h2>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                <button type="button" onclick="openLogin('register')"
                        class="btn-editorial px-7 py-4 rounded-xl text-sm">
                    Daftar Akun Gratis &rarr;
                </button>
                <button type="button" onclick="openLogin('login')"
                        class="px-7 py-4 rounded-xl text-sm font-semibold text-white bg-white/[0.05] hover:bg-white/[0.1] border border-white/15 transition cursor-pointer">
                    Masuk ke Dashboard
                </button>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/[0.08] bg-[#060804] px-5 sm:px-8 py-7">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#8E9882]">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}"
                     alt="Logo AlpetBizz"
                     class="w-6 h-6 object-contain">
                <span class="font-bold text-white">AlpetBizz</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-5">
                <a href="#produk" class="hover:text-[#C4FF0D] transition no-underline text-[#8E9882]">Produk</a>
                <a href="#alpetbizz-ai" class="hover:text-[#C4FF0D] transition no-underline text-[#8E9882]">AlpetBizz AI</a>
                <a href="#cara-kerja" class="hover:text-[#C4FF0D] transition no-underline text-[#8E9882]">Cara Kerja</a>
                <a href="#faq" class="hover:text-[#C4FF0D] transition no-underline text-[#8E9882]">FAQ</a>
            </div>
            <p>&copy; {{ date('Y') }} AlpetBizz</p>
        </div>
    </footer>
</div>

<!-- ══════════════════════════════════════════════════════════════
     UNIFIED AUTH MODAL PANEL (Login & Register)
     ══════════════════════════════════════════════════════════════ -->
<div id="login-panel" aria-modal="true" role="dialog" aria-label="Form Autentikasi">
    <div id="login-panel-card" class="{{ $activeTab === 'register' ? 'card-wide' : '' }}">

        @if ($error)
        <div class="login-error">
            <svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $error }}</span>
        </div>
        @endif

        @if ($success)
        <div class="login-success">
            <svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $success }}</span>
        </div>
        @endif

        <!-- TAB 1: LOGIN -->
        <div id="tab-login-content" style="{{ $activeTab === 'register' ? 'display:none;' : '' }}">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Masuk ke Akun</h2>
                    <p class="panel-sub">Kelola keuangan dan stok usaha Anda di satu tempat.</p>
                </div>
                <button type="button" class="panel-close" onclick="closeLogin()" aria-label="Tutup form">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="/login" class="auth-form">
                @csrf
                <input type="hidden" name="action" value="login">

                <div>
                    <label class="field-label" for="inp-username">Email atau Username</label>
                    <input id="inp-username" class="field-input" type="text"
                        name="username" required autocomplete="username"
                        value="{{ old('username', '') }}"
                        placeholder="Masukkan email atau username">
                </div>

                <div>
                    <label class="field-label" for="inp-password">Kata Sandi</label>
                    <div class="field-wrap">
                        <input id="inp-password" class="field-input has-icon" type="password"
                            name="password" required autocomplete="current-password"
                            placeholder="Masukkan kata sandi">
                        <button type="button" class="pw-toggle"
                            onclick="togglePw('inp-password', this)"
                            aria-label="Tampilkan kata sandi">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk Sekarang</button>
            </form>

            <div class="panel-footer">
                <span class="panel-footer-text">Belum memiliki akun? </span>
                <button type="button" class="tab-link" onclick="switchTab('register')">Daftar sekarang</button>
            </div>
        </div>

        <!-- TAB 2: REGISTER -->
        <div id="tab-register-content" style="{{ $activeTab === 'register' ? '' : 'display:none;' }}">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Buat Akun Baru</h2>
                    <p class="panel-sub">Daftarkan akun dan profil usaha UMKM Anda di AlpetBizz.</p>
                </div>
                <button type="button" class="panel-close" onclick="closeLogin()" aria-label="Tutup form">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="/login" class="auth-form">
                @csrf
                <input type="hidden" name="action" value="register">

                <div class="reg-grid">
                    <p class="section-label">Informasi Akun</p>

                    <div>
                        <label class="field-label" for="r-nama">Nama Lengkap *</label>
                        <input id="r-nama" class="field-input" type="text" name="nama" required
                            value="{{ old('nama', '') }}"
                            placeholder="Nama pemilik usaha">
                    </div>

                    <div>
                        <label class="field-label" for="r-username">Username *</label>
                        <input id="r-username" class="field-input" type="text" name="username" required
                            value="{{ old('username', '') }}"
                            placeholder="Username unik">
                    </div>

                    <div>
                        <label class="field-label" for="r-email">Alamat Email *</label>
                        <input id="r-email" class="field-input" type="email" name="email" required
                            value="{{ old('email', '') }}"
                            placeholder="nama@domain.com">
                    </div>

                    <div>
                        <label class="field-label" for="r-hp">No. WhatsApp / HP</label>
                        <input id="r-hp" class="field-input" type="tel" name="no_hp"
                            value="{{ old('no_hp', '') }}"
                            placeholder="08123456789">
                    </div>

                    <div>
                        <label class="field-label" for="r-pw">Kata Sandi *</label>
                        <div class="field-wrap">
                            <input id="r-pw" class="field-input has-icon" type="password" name="password" required minlength="8"
                                placeholder="Min. 8 karakter (huruf & angka)">
                            <button type="button" class="pw-toggle" onclick="togglePw('r-pw', this)" aria-label="Lihat sandi">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="r-pw2">Ulangi Sandi *</label>
                        <div class="field-wrap">
                            <input id="r-pw2" class="field-input has-icon" type="password" name="password2" required
                                placeholder="Ulangi kata sandi">
                            <button type="button" class="pw-toggle" onclick="togglePw('r-pw2', this)" aria-label="Lihat sandi">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <p class="section-label">Informasi Usaha UMKM</p>

                    <div>
                        <label class="field-label" for="r-usaha">Nama Usaha / Toko</label>
                        <input id="r-usaha" class="field-input" type="text" name="nama_usaha"
                            value="{{ old('nama_usaha', '') }}"
                            placeholder="Contoh: Toko Berkah Jaya">
                    </div>

                    <div>
                        <label class="field-label" for="r-kat">Kategori Usaha</label>
                        <select id="r-kat" class="field-input" name="kategori">
                            <option value="">-- Pilih kategori usaha --</option>
                            @foreach ($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ old('kategori', '') === $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reg-full">
                        <label class="field-label" for="r-alamat">Alamat Lengkap Usaha</label>
                        <input id="r-alamat" class="field-input" type="text" name="alamat"
                            value="{{ old('alamat', '') }}"
                            placeholder="Alamat fisik toko / usaha">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Daftar Sekarang</button>
            </form>

            <div class="panel-footer">
                <span class="panel-footer-text">Sudah memiliki akun? </span>
                <button type="button" class="tab-link" onclick="switchTab('login')">Masuk di sini</button>
            </div>
        </div>

    </div>
</div>

<script>
    window.ACTIVE_TAB = @json($activeTab);
    window.AUTO_OPEN_PANEL = @json((bool) ($error || $success || request()->has('tab')));
</script>
<script src="{{ asset('assets/js/login.js') }}?v={{ filemtime(public_path('assets/js/login.js')) }}"></script>

</body>
</html>
