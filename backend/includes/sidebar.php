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
?>

<!-- Mobile Topbar Navbar (Muncul hanya pada layar < lg / Mobile & Tablet) -->
<header class="lg:hidden sticky top-0 z-30 flex items-center justify-between px-4 py-3 bg-navy-950 text-white shadow-md">
    <div class="flex items-center gap-3">
        <button type="button" id="sidebar-toggle" aria-label="Buka Menu" class="p-2 -ml-1 text-white hover:bg-white/10 rounded-lg focus:outline-none transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <span class="font-bold text-base tracking-tight text-white">AlpetBizz</span>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs font-semibold px-2.5 py-1 bg-white/10 text-white rounded-md border border-white/10 truncate max-w-[120px]">
            <?= htmlspecialchars($_SESSION['user'] ?? 'User') ?>
        </span>
    </div>
</header>

<!-- Backdrop Overlay Gelap untuk Mobile saat sidebar terbuka -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-navy-950/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

<!-- Sidebar Drawer Utama (Off-canvas di mobile/tablet, Fixed permanen di desktop/laptop) -->
<aside id="app-sidebar" class="fixed left-0 top-0 w-64 h-screen bg-navy-950 flex flex-col z-50 transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 shadow-2xl lg:shadow-none">
    <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
        <span class="text-white font-bold text-lg tracking-tight">AlpetBizz</span>
        <!-- Tombol Tutup Sidebar khusus Mobile -->
        <button type="button" id="sidebar-close" aria-label="Tutup Menu" class="lg:hidden p-1.5 text-white/70 hover:text-white hover:bg-white/10 rounded-lg focus:outline-none transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto">
        <?php foreach ($nav as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition
                  <?= ($currentPage ?? '') === $item['page']
                        ? 'bg-white/15 text-white'
                        : 'text-white/65 hover:bg-white/10 hover:text-white' ?>">
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="px-3 py-3 border-t border-white/10">
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="logout">
            <button class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium text-white/65 hover:bg-red-500/80 hover:text-white transition">
                Keluar
            </button>
        </form>
    </div>
</aside>

<!-- Global Script & Style untuk Animasi, Chart.js, dan Navigasi SPA Tanpa Reload -->
<link rel="stylesheet" href="assets/css/animations.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/animations.js"></script>
<script src="assets/js/profil.js"></script>
<script src="assets/js/spa.js"></script>
