<!-- Fonts: Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Mobile Topbar (hanya tampil < lg) -->
<header class="lg:hidden sticky top-0 z-30 flex items-center justify-between px-4 py-3 bg-[#0A0D07]/90 backdrop-blur-xl border-b border-white/[0.08]">
    <div class="flex items-center gap-2.5">
        <button type="button" id="sidebar-toggle" aria-label="Buka Menu"
            class="p-2 -ml-1 text-[#8E9882] hover:text-white hover:bg-white/[0.06] rounded-xl focus:outline-none transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}"
                 alt="Logo AlpetBizz"
                 class="w-7 h-7 rounded-full object-contain shrink-0 drop-shadow-[0_0_10px_rgba(196,255,13,0.45)]">
            <span class="font-bold text-white text-base tracking-tight">AlpetBizz</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-[#12170D] px-2.5 py-1 font-mono text-[11px] font-medium text-[#F4F7F0] truncate max-w-[140px]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#C4FF0D] shrink-0"></span>
            {{ auth()->user()?->username ?? 'User' }}
        </span>
    </div>
</header>

<!-- Backdrop overlay mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

<!-- Sidebar Floating Bento Card -->
<aside id="app-sidebar"
    class="fixed left-0 top-0 h-screen w-64 z-50 p-3 flex flex-col
           transition-transform duration-300 ease-in-out
           -translate-x-full lg:translate-x-0">

    <div class="h-full bg-[#12170D] border border-white/[0.08] rounded-[26px] shadow-2xl flex flex-col overflow-hidden relative">
        <!-- Subtle top lime glow inside sidebar -->
        <div class="pointer-events-none absolute -top-16 -left-16 w-44 h-44 rounded-full bg-[#C4FF0D]/10 blur-3xl"></div>

        <!-- Brand / Logo -->
        <div class="relative px-5 py-5 border-b border-white/[0.08] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}"
                     alt="Logo AlpetBizz"
                     class="w-10 h-10 rounded-full object-contain shrink-0 drop-shadow-[0_0_14px_rgba(196,255,13,0.45)]">
                <div>
                    <h1 class="font-bold text-white text-base tracking-[-0.02em] leading-tight">AlpetBizz</h1>
                </div>
            </div>
            <button type="button" id="sidebar-close" aria-label="Tutup Menu"
                class="lg:hidden p-1.5 text-[#8E9882] hover:text-white rounded-lg hover:bg-white/[0.06] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navigasi -->
        <nav class="relative flex-1 px-3 py-4 space-y-1.5 overflow-y-auto" aria-label="Menu Utama">
            <p class="px-3.5 pb-1.5 font-mono text-xs uppercase tracking-wider text-[#8E9882]/70">Workspace</p>
            @foreach ($sidebarNav as $item)
            @php $isActive = ($currentPage ?? '') === $item['page']; @endphp
            <a href="{{ $item['href'] }}"
               data-nav-page="{{ $item['page'] }}"
               class="flex items-center justify-between gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition
                      {{ $isActive
                            ? 'bg-[#C4FF0D] text-[#152208] font-semibold shadow-[0_6px_20px_-4px_rgba(196,255,13,0.35)]'
                            : 'text-[#9AA68E] hover:bg-white/[0.05] hover:text-white' }}">
                <span class="flex items-center gap-3">
                    {!! $sidebarIcons[$item['page']] !!}
                    <span>{{ $item['label'] }}</span>
                </span>
            </a>
            @endforeach
        </nav>

        <!-- Account Mini Card + Keluar -->
        <div class="relative p-3 border-t border-white/[0.08] space-y-2">
            <div class="px-4 py-3 rounded-2xl bg-[#181F11] border border-white/[0.06] flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()?->nama ?? 'Administrator' }}</p>
                    <p class="font-mono text-xs text-[#9AA68E] truncate">{{ '@' . (auth()->user()?->username ?? 'admin') }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-[#C4FF0D]/15 px-2.5 py-0.5 font-mono text-xs font-semibold text-[#C4FF0D]">PRO</span>
            </div>

            <form method="POST" action="/logout" data-spa-ignore="true">
                @csrf
                <input type="hidden" name="action" value="logout">
                <button type="button"
                    onclick="konfirmasiLogout(this)"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium cursor-pointer
                           text-[#9AA68E] hover:bg-rose-500/15 hover:text-rose-300 border border-transparent hover:border-rose-500/25 transition">
                    {!! $sidebarIcons['logout'] !!}
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Global Assets -->
<link rel="stylesheet" href="{{ asset('assets/css/animations.css') }}?v={{ filemtime(public_path('assets/css/animations.css')) }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/animations.js') }}?v={{ filemtime(public_path('assets/js/animations.js')) }}"></script>
<script src="{{ asset('assets/js/profil.js') }}"></script>
<script src="{{ asset('assets/js/spa.js') }}?v={{ filemtime(public_path('assets/js/spa.js')) }}"></script>
<script>
    window.konfirmasiLogout = function (btn) {
        const form = btn.closest('form');
        if (!form) return;

        if (typeof Swal === 'undefined') {
            if (confirm('Yakin ingin keluar dari akun AlpetBizz?')) {
                form.submit();
            }
            return;
        }

        Swal.fire({
            title: 'Keluar dari Akun?',
            text: 'Sesi Anda saat ini akan diakhiri dan Anda akan kembali ke halaman login.',
            icon: 'warning',
            background: '#12170D',
            color: '#F4F7F0',
            showCancelButton: true,
            confirmButtonText: 'Ya, keluar',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#252f1b',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    };

    // Jika halaman dipulihkan dari cache browser (tombol Back), muat ulang
    // agar server mengecek sesi login kembali.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
