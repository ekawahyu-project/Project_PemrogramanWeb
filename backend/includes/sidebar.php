<?php
$nav = [
    ['href' => 'dashboard.php',   'label' => 'Dashboard',    'page' => 'dashboard'],
    ['href' => 'transaksi.php',   'label' => 'Transaksi',    'page' => 'transaksi'],
    ['href' => 'produk.php',      'label' => 'Produk',       'page' => 'produk'],
    ['href' => 'stok.php',        'label' => 'Stok',         'page' => 'stok'],
    ['href' => 'laporan.php',     'label' => 'Laporan',      'page' => 'laporan'],
    ['href' => 'rekomendasi.php', 'label' => 'Rekomendasi',  'page' => 'rekomendasi'],
    ['href' => 'profil.php',      'label' => 'Profil',       'page' => 'profil'],
];

$icons = [
    'dashboard' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="2"/></svg>',
    'transaksi' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
    'produk'    => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
    'stok'      => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
    'laporan'   => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
    'rekomendasi' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
    'profil'    => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    'logout'    => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
];
?>

<!-- Mobile Topbar (hanya tampil < lg) -->
<header class="lg:hidden sticky top-0 z-30 flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100 shadow-sm">
    <div class="flex items-center gap-2.5">
        <button type="button" id="sidebar-toggle" aria-label="Buka Menu"
            class="p-2 -ml-1 text-gray-600 hover:bg-gray-100 rounded-xl focus:outline-none transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <span class="font-bold text-navy-900 text-base tracking-tight">AlpetBizz</span>
    </div>
    <span class="text-xs font-semibold px-2.5 py-1 bg-navy-100 text-navy-800 rounded-lg truncate max-w-[120px]">
        <?= htmlspecialchars($_SESSION['user'] ?? 'User') ?>
    </span>
</header>

<!-- Backdrop overlay mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

<!-- Sidebar Floating Card -->
<aside id="app-sidebar"
    class="fixed left-0 top-0 h-screen w-64 z-50 p-3 flex flex-col
           transition-transform duration-300 ease-in-out
           -translate-x-full lg:translate-x-0">

    <div class="h-full bg-navy-950 rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        <!-- Brand / Logo -->
        <div class="px-5 py-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center
                            text-white font-bold text-base">
                    A
                </div>
                <div>
                    <h1 class="font-bold text-white text-base tracking-tight leading-tight">AlpetBizz</h1>
                    <p class="text-[11px] text-white/40 font-medium">UMKM Manager</p>
                </div>
            </div>
        </div>

        <!-- Navigasi -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <?php foreach ($nav as $item): ?>
            <a href="<?= $item['href'] ?>"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition
                      <?= ($currentPage ?? '') === $item['page']
                            ? 'bg-white/15 text-white'
                            : 'text-white/60 hover:bg-white/10 hover:text-white' ?>">
                <?= $icons[$item['page']] ?>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Keluar -->
        <div class="px-3 py-4 border-t border-white/10">
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="action" value="logout">
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium
                           text-white/60 hover:bg-red-500/80 hover:text-white transition">
                    <?= $icons['logout'] ?>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Global Assets -->
<link rel="stylesheet" href="assets/css/animations.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/animations.js"></script>
<script src="assets/js/profil.js"></script>
<script src="assets/js/spa.js"></script>
