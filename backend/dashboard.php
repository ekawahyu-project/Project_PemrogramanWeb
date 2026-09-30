<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require_once 'includes/init.php';

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: login.php'); exit;
}

$currentPage = 'dashboard';
$user        = $_SESSION['user'];

// Hitung statistik dari data session
$pemasukan = $pengeluaran = 0;
foreach ($_SESSION['transaksi'] as $t) {
    if ($t['jenis'] === 'Pemasukan') $pemasukan  += $t['jumlah'];
    else                              $pengeluaran += $t['jumlah'];
}
$laba      = $pemasukan - $pengeluaran;
$jmlStok   = count($_SESSION['produk']);
$lowStock  = count(array_filter($_SESSION['produk'], fn($p) => $p['stok'] <= $p['stok_min']));

$stats = [
    ['label' => 'Total Pemasukan',   'value' => 'Rp ' . number_format($pemasukan,   0, ',', '.'), 'sub' => count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pemasukan')) . ' transaksi', 'color' => 'text-emerald-400'],
    ['label' => 'Total Pengeluaran', 'value' => 'Rp ' . number_format($pengeluaran, 0, ',', '.'), 'sub' => count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pengeluaran')) . ' transaksi', 'color' => 'text-rose-400'],
    ['label' => 'Produk',            'value' => $jmlStok . ' item',                                'sub' => $lowStock > 0 ? $lowStock . ' stok menipis' : 'Semua aman',                                        'color' => $lowStock > 0 ? 'text-amber-400' : 'text-sky-400'],
    ['label' => 'Laba Bersih',       'value' => 'Rp ' . number_format(abs($laba),   0, ',', '.'), 'sub' => $laba >= 0 ? 'Untung' : 'Rugi',                                                                    'color' => $laba >= 0 ? 'text-sky-400' : 'text-rose-400'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">
    <?php include 'includes/sidebar.php'; ?>

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <header class="bg-white border-b border-gray-100 px-4 sm:px-6 py-4">
            <h2 class="font-bold text-lg sm:text-xl text-navy-900">Dashboard</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Selamat datang, <span class="font-medium text-navy-800"><?= htmlspecialchars($user) ?></span></p>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 max-w-7xl w-full mx-auto">
            <!-- Stok rendah alert -->
            <?php if ($lowStock > 0): ?>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3.5 sm:p-4 flex items-start sm:items-center gap-3">
                <span class="text-orange-500 text-lg flex-shrink-0">⚠</span>
                <p class="text-xs sm:text-sm text-orange-800 font-medium leading-relaxed">
                    <?= $lowStock ?> produk stok menipis. —
                    <a href="stok.php" class="underline font-semibold hover:text-orange-950">Cek halaman Stok</a>
                </p>
            </div>
            <?php endif; ?>

            <!-- Kartu Statistik -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <?php foreach ($stats as $s): ?>
                <div class="bg-navy-950 rounded-xl p-4 sm:p-5 border border-white/10 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-300 mb-1.5"><?= $s['label'] ?></p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight <?= $s['color'] ?>"><?= $s['value'] ?></p>
                    </div>
                    <p class="text-xs text-slate-400 mt-2.5 pt-2 border-t border-white/10"><?= $s['sub'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Shortcut navigasi -->
            <div>
                <h3 class="font-semibold text-navy-900 text-sm mb-3">Akses Cepat Modul</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <?php
                    $shortcuts = [
                        ['href' => 'transaksi.php',   'label' => 'Transaksi',   'desc' => 'Catat pemasukan & pengeluaran'],
                        ['href' => 'produk.php',      'label' => 'Produk',      'desc' => 'Kelola katalog data produk'],
                        ['href' => 'stok.php',        'label' => 'Stok',        'desc' => 'Pantau & catat pergerakan barang'],
                        ['href' => 'laporan.php',     'label' => 'Laporan',     'desc' => 'Grafik analitik & laporan bulanan'],
                        ['href' => 'rekomendasi.php', 'label' => 'Rekomendasi', 'desc' => 'Analisis pintar & catatan bisnis'],
                        ['href' => 'profil.php',      'label' => 'Profil',      'desc' => 'Informasi akun & profil usaha'],
                    ];
                    foreach ($shortcuts as $s): ?>
                    <a href="<?= $s['href'] ?>"
                        class="bg-navy-950 rounded-xl p-4 border border-white/10 shadow-sm hover:border-white/25 hover:bg-navy-900 transition group flex flex-col justify-between">
                        <div>
                            <p class="font-semibold text-white text-sm flex items-center justify-between">
                                <?= $s['label'] ?>
                                <span class="text-slate-400 group-hover:text-white transition-transform group-hover:translate-x-0.5">→</span>
                            </p>
                            <p class="text-xs text-slate-300 mt-1"><?= $s['desc'] ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 5 Transaksi Terakhir -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-navy-900 text-sm">Transaksi Terakhir</h3>
                    <a href="transaksi.php" class="text-xs font-semibold text-navy-700 hover:text-navy-950 transition">Lihat semua</a>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[500px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 sm:px-5 py-3 text-left">Tanggal</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Keterangan</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Jenis</th>
                                <th class="px-4 sm:px-5 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach (array_slice(array_reverse($_SESSION['transaksi']), 0, 5) as $t): ?>
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500 whitespace-nowrap"><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
                                <td class="px-4 sm:px-5 py-3.5 font-medium text-navy-900"><?= htmlspecialchars($t['keterangan']) ?></td>
                                <td class="px-4 sm:px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium
                                        <?= $t['jenis'] === 'Pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                        <?= $t['jenis'] ?>
                                    </span>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-right font-semibold whitespace-nowrap <?= $t['jenis'] === 'Pemasukan' ? 'text-green-600' : 'text-red-500' ?>">
                                    <?= ($t['jenis'] === 'Pemasukan' ? '+' : '-') . 'Rp ' . number_format($t['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
