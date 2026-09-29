<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login/index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: ../login/index.php');
    exit;
}

$currentPage = 'dashboard';
$user = $_SESSION['user'];

$stats = [
    ['label' => 'Pemasukan',   'value' => 'Rp 4.500.000', 'sub' => 'Bulan ini',      'color' => 'text-green-600'],
    ['label' => 'Pengeluaran', 'value' => 'Rp 1.800.000', 'sub' => 'Bulan ini',      'color' => 'text-red-500'],
    ['label' => 'Stok Barang', 'value' => '34 item',      'sub' => '3 hampir habis', 'color' => 'text-blue-600'],
    ['label' => 'Laba Bersih', 'value' => 'Rp 2.700.000', 'sub' => 'Bulan ini',      'color' => 'text-navy-800'],
];

$transactions = [
    ['tanggal' => '28 Sep 2026', 'keterangan' => 'Penjualan Produk A', 'jenis' => 'Pemasukan',   'jumlah' => '+Rp 500.000'],
    ['tanggal' => '27 Sep 2026', 'keterangan' => 'Beli Bahan Baku',    'jenis' => 'Pengeluaran', 'jumlah' => '-Rp 200.000'],
    ['tanggal' => '26 Sep 2026', 'keterangan' => 'Penjualan Produk B', 'jenis' => 'Pemasukan',   'jumlah' => '+Rp 750.000'],
    ['tanggal' => '25 Sep 2026', 'keterangan' => 'Biaya Listrik',      'jenis' => 'Pengeluaran', 'jumlah' => '-Rp 150.000'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — UMKM Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/sidebar.php'; ?>

    <main class="ml-56 min-h-screen flex flex-col">
        <header class="bg-white border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-navy-900">Dashboard</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Selamat datang, <span class="font-medium"><?= htmlspecialchars($user) ?></span>
            </p>
        </header>

        <div class="p-6 flex-1">
            <!-- Kartu Statistik -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php foreach ($stats as $s): ?>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 mb-2"><?= $s['label'] ?></p>
                    <p class="text-lg font-bold <?= $s['color'] ?>"><?= $s['value'] ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= $s['sub'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Transaksi Terakhir -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm">Transaksi Terakhir</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Keterangan</th>
                            <th class="px-5 py-3 text-left">Jenis</th>
                            <th class="px-5 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($transactions as $t): ?>
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-5 py-3.5 text-gray-500"><?= $t['tanggal'] ?></td>
                            <td class="px-5 py-3.5 font-medium text-navy-900"><?= $t['keterangan'] ?></td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium
                                    <?= $t['jenis'] === 'Pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                    <?= $t['jenis'] ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold
                                <?= $t['jumlah'][0] === '+' ? 'text-green-600' : 'text-red-500' ?>">
                                <?= $t['jumlah'] ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
