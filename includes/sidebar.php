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
<aside class="fixed left-0 top-0 w-56 h-screen bg-navy-950 flex flex-col z-30">
    <div class="px-5 py-4 border-b border-white/10">
        <span class="text-white font-bold text-base tracking-tight">AlpetBizz</span>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
        <?php foreach ($nav as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
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
            <button class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-white/65 hover:bg-red-500/80 hover:text-white transition">
                Keluar
            </button>
        </form>
    </div>
</aside>

<!-- Global Script untuk Chart.js dan Navigasi SPA Tanpa Reload -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/profil.js"></script>
<script src="assets/js/spa.js"></script>
