<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpetBizz | Masuk & Daftar</title>
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
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<body class="font-sans antialiased">

<!-- ══════════════════════════════════════════════════════════════
     VAULTORY HERO SECTION (Adapted for AlpetBizz UMKM Manager)
     ══════════════════════════════════════════════════════════════ -->
<section class="vaultory-hero">
    <!-- Radial Glow Effect -->
    <div class="vaultory-glow" aria-hidden="true"></div>

    <!-- Top Navigation -->
    <nav class="relative z-40 px-5 sm:px-8 py-5 sm:py-6" aria-label="Navigasi Utama">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <!-- Logo -->
            <a href="/login" class="flex items-center gap-2.5 text-white no-underline group">
                <span class="w-9 h-9 rounded-xl bg-[#C4FF0D] text-[#0b0e08] font-extrabold text-base flex items-center justify-center shadow-[0_0_24px_rgba(196,255,13,0.35)] transition-transform group-hover:scale-105">
                    A
                </span>
                <span class="text-xl sm:text-2xl font-bold tracking-tight text-white">AlpetBizz</span>
            </a>

            <!-- Nav Items -->
            <div class="hidden md:flex items-center gap-8">
                <button type="button" onclick="openLogin('login')" class="text-white/75 hover:text-white transition-colors text-sm font-medium bg-transparent border-0 cursor-pointer">
                    Arus Kas
                </button>
                <button type="button" onclick="openLogin('login')" class="text-white/75 hover:text-white transition-colors text-sm font-medium bg-transparent border-0 cursor-pointer">
                    Inventaris &amp; Stok
                </button>
                <button type="button" onclick="openLogin('login')" class="text-white/75 hover:text-white transition-colors text-sm font-medium bg-transparent border-0 cursor-pointer">
                    Analitik Bento
                </button>
                <button type="button" onclick="openLogin('register')" class="text-white/75 hover:text-white transition-colors text-sm font-medium bg-transparent border-0 cursor-pointer">
                    Daftar UMKM
                </button>
            </div>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3 sm:gap-4">
                <button type="button" id="nav-login-btn" onclick="openLogin('login')"
                    class="text-white/85 hover:text-white transition-colors text-sm font-medium bg-transparent border-0 cursor-pointer px-2 py-1.5">
                    Masuk
                </button>
                <button type="button" onclick="openLogin('register')"
                    class="px-4 sm:px-6 py-2.5 rounded-lg font-semibold text-xs sm:text-sm transition-all hover:scale-105 cursor-pointer border-0"
                    style="background: #C4FF0D; color: #000000;">
                    Daftar Akun
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Hero 2-Column Content -->
    <div class="relative z-10 flex-1 flex items-center px-5 sm:px-8 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-center">

            <!-- Left Content -->
            <div class="space-y-6 sm:space-y-8 anim-fade-left">

                <!-- Title -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.08] tracking-[-0.03em] text-white">
                    Masa depan<br>
                    <span style="color: #C4FF0D;">finansial UMKM.</span>
                </h1>

                <!-- Description -->
                <p class="text-base sm:text-lg md:text-xl max-w-xl leading-[1.7]" style="color: rgba(255, 255, 255, 0.72);">
                    Catat transaksi, pantau pergerakan stok real-time, dan kembangkan profit bisnis Anda dalam satu brankas pintar yang responsif di semua perangkat.
                </p>

                <!-- Email / Username Input & CTA -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-md">
                    <input
                        id="hero-quick-email"
                        type="text"
                        placeholder="Masukkan username atau email..."
                        aria-label="Username atau email"
                        class="flex-1 px-4 sm:px-5 py-3.5 rounded-lg text-white placeholder:text-white/40 outline-none text-sm"
                        style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);"
                    >
                    <button
                        type="button"
                        onclick="startFromHeroCta()"
                        class="px-7 py-3.5 rounded-lg font-semibold text-sm transition-transform hover:scale-105 active:scale-95 cursor-pointer border-0 shrink-0"
                        style="background: #C4FF0D; color: #000000;"
                    >
                        Mulai Sekarang
                    </button>
                </div>

                <!-- Quick Metrics Strip -->
                <div class="grid grid-cols-3 gap-4 max-w-md pt-2 border-t border-white/10">
                    <div>
                        <p class="font-mono text-lg sm:text-xl font-bold text-white">100%</p>
                        <p class="text-xs text-white/55">Stok Terlacak</p>
                    </div>
                    <div>
                        <p class="font-mono text-lg sm:text-xl font-bold text-[#C4FF0D]">Real-time</p>
                        <p class="text-xs text-white/55">Laporan Laba</p>
                    </div>
                    <div>
                        <p class="font-mono text-lg sm:text-xl font-bold text-white">24/7</p>
                        <p class="text-xs text-white/55">Rekomendasi</p>
                    </div>
                </div>
            </div>

            <!-- Right Content: Wallet Bento Card & Floating Coins -->
            <div class="relative h-[400px] sm:h-[460px] flex items-center justify-center anim-fade-right">
                <!-- Interactive Vault Preview Card -->
                <div class="vault-wallet-card" onclick="openLogin('login')" style="cursor: pointer;" title="Klik untuk masuk ke Dashboard">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-[#C4FF0D]/20 border border-[#C4FF0D]/40 text-[#C4FF0D] font-mono text-xs font-bold flex items-center justify-center">Rp</span>
                            <div>
                                <p class="text-xs font-semibold text-white">AlpetBizz Vault</p>
                                <p class="font-mono text-[10px] text-white/50">Kas &amp; Inventaris Aktif</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-[#C4FF0D]/15 border border-[#C4FF0D]/30 px-2.5 py-0.5 font-mono text-[10px] font-semibold text-[#C4FF0D]">
                            +28.4%
                        </span>
                    </div>

                    <p class="text-[11px] text-white/50 font-medium">Estimasi Laba Bersih</p>
                    <p class="font-mono text-2xl sm:text-3xl font-bold text-white tracking-tight mt-0.5">Rp 24.850.000</p>
                    <p class="font-mono text-[11px] text-[#C4FF0D] mt-1">+Rp 5.420.000 bulan ini</p>

                    <!-- Mini PnL Bars inside Hero Card -->
                    <div class="mt-5 flex items-end gap-1.5 h-16 pt-2">
                        @foreach ([38, 52, 44, 68, 60, 82, 74, 90, 65, 86, 94, 100] as $idx => $h)
                        <span class="flex-1 rounded-[3px]"
                              style="height: {{ $h }}%; background: {{ $idx % 4 === 2 ? '#C084FC' : '#C4FF0D' }}; opacity: {{ 0.4 + ($idx / 20) }};"></span>
                        @endforeach
                    </div>
                    <div class="mt-2 flex justify-between font-mono text-[10px] text-white/45">
                        <span>30 Hari Lalu</span>
                        <span class="text-[#C4FF0D]">Arus Kas Positif</span>
                        <span>Hari Ini</span>
                    </div>

                    <!-- Mini Activity Pill -->
                    <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between text-xs">
                        <span class="text-white/70">Status Gudang &amp; Produk</span>
                        <span class="font-mono text-[#C4FF0D] font-semibold"> Terkendali →</span>
                    </div>
                </div>

                <!-- Floating Coins (Exact VaultoryHero positions & gradient) -->
                <div class="vault-coin vault-coin-1" style="width: 74px; height: 74px; left: 4%; top: 8%;">
                    <span class="font-mono text-xl font-extrabold">Rp</span>
                </div>
                <div class="vault-coin vault-coin-2" style="width: 66px; height: 66px; right: 5%; top: 14%;">
                    <span class="font-mono text-lg font-extrabold">₿</span>
                </div>
                <div class="vault-coin vault-coin-3" style="width: 58px; height: 58px; left: 7%; bottom: 10%;">
                    <span class="font-mono text-lg font-extrabold">◎</span>
                </div>
                <div class="vault-coin vault-coin-4" style="width: 62px; height: 62px; right: 7%; bottom: 12%;">
                    <span class="font-mono text-lg font-extrabold">Ξ</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Bottom Brand / Module Strip -->
    <div class="relative z-20 pb-6 sm:pb-8 px-6 anim-fade-up">
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════
     UNIFIED AUTH MODAL PANEL (Login & Register)
     ══════════════════════════════════════════════════════════════ -->
<div id="login-panel" aria-modal="true" role="dialog" aria-label="Form Autentikasi">
    <div id="login-panel-card" class="{{ $activeTab === 'register' ? 'card-wide' : '' }}">

        <!-- Global Alert Messages -->
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

        <!-- ════════ TAB 1: FORM LOGIN ════════ -->
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

        <!-- ════════ TAB 2: FORM REGISTER ════════ -->
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
                            <input id="r-pw" class="field-input has-icon" type="password" name="password" required
                                placeholder="Min. 6 karakter">
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

                    <!-- Bagian 2: Usaha -->
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
<script src="{{ asset('assets/js/login.js') }}"></script>

</body>
</html>
