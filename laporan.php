<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require_once 'includes/init.php';

$currentPage = 'laporan';
$bulanNama   = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
                 '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];

// ── CRUD: Laporan Tersimpan ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE — hitung snapshot dari date range
    if ($action === 'simpan') {
        $judul   = trim($_POST['judul']          ?? '');
        $dari    = $_POST['tanggal_dari']          ?? '';
        $sampai  = $_POST['tanggal_sampai']        ?? '';
        $catatan = trim($_POST['catatan']          ?? '');

        if ($judul && $dari && $sampai && $dari <= $sampai) {
            $p = $k = 0;
            foreach ($_SESSION['transaksi'] as $t) {
                if ($t['tanggal'] >= $dari && $t['tanggal'] <= $sampai) {
                    if ($t['jenis'] === 'Pemasukan') $p += $t['jumlah'];
                    else $k += $t['jumlah'];
                }
            }
            $_SESSION['laporan_tersimpan'][] = [
                'id'             => uniqid('l'),
                'judul'          => $judul,
                'tanggal_dari'   => $dari,
                'tanggal_sampai' => $sampai,
                'pemasukan'      => $p,
                'pengeluaran'    => $k,
                'laba'           => $p - $k,
                'catatan'        => $catatan,
                'dibuat'         => date('Y-m-d'),
            ];
            header('Location: laporan.php'); exit;
        }
    }

    // UPDATE — hanya judul & catatan (data finansial adalah snapshot, tidak berubah)
    if ($action === 'update') {
        $id = $_POST['id'] ?? '';
        foreach ($_SESSION['laporan_tersimpan'] as &$l) {
            if ($l['id'] === $id) {
                $l['judul']   = trim($_POST['judul']   ?? $l['judul']);
                $l['catatan'] = trim($_POST['catatan'] ?? $l['catatan']);
                break;
            }
        }
        unset($l);
        header('Location: laporan.php'); exit;
    }

    // DELETE
    if ($action === 'hapus') {
        $id = $_POST['id'] ?? '';
        $_SESSION['laporan_tersimpan'] = array_values(
            array_filter($_SESSION['laporan_tersimpan'], fn($l) => $l['id'] !== $id)
        );
        header('Location: laporan.php'); exit;
    }
}

// Edit mode — pre-fill form
$editId  = $_GET['edit'] ?? null;
$editLap = null;
if ($editId) {
    foreach ($_SESSION['laporan_tersimpan'] as $l) {
        if ($l['id'] === $editId) { $editLap = $l; break; }
    }
}

// ── Data grafik dari transaksi live ────────────────────────────
$bulanData = [];
foreach ($_SESSION['transaksi'] as $t) {
    $key = date('Y-m', strtotime($t['tanggal']));
    if (!isset($bulanData[$key])) $bulanData[$key] = ['pemasukan' => 0, 'pengeluaran' => 0];
    if ($t['jenis'] === 'Pemasukan') $bulanData[$key]['pemasukan'] += $t['jumlah'];
    else $bulanData[$key]['pengeluaran'] += $t['jumlah'];
}
ksort($bulanData);

$labels = array_map(function($k) use ($bulanNama) {
    [$y, $m] = explode('-', $k);
    return ($bulanNama[$m] ?? $m) . ' ' . $y;
}, array_keys($bulanData));

$totalMasuk = $totalKeluar = 0;
foreach ($_SESSION['transaksi'] as $t) {
    if ($t['jenis'] === 'Pemasukan') $totalMasuk  += $t['jumlah'];
    else                              $totalKeluar += $t['jumlah'];
}
$totalLaba = $totalMasuk - $totalKeluar;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'includes/sidebar.php'; ?>
    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <header class="bg-white border-b border-gray-100 px-4 sm:px-6 py-4">
            <h2 class="font-bold text-lg sm:text-xl text-navy-900">Laporan & Grafik</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Grafik pemasukan/pengeluaran live + arsip laporan per periode.</p>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 w-full">
            <!-- READ: Ringkasan Total (live) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1.5">Total Pemasukan</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight text-green-600">Rp <?= number_format($totalMasuk,  0, ',', '.') ?></p>
                    </div>
                    <p class="text-xs text-gray-400 mt-2.5 pt-2 border-t border-gray-50"><?= count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pemasukan')) ?> transaksi tercatat</p>
                </div>
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1.5">Total Pengeluaran</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight text-red-500">Rp <?= number_format($totalKeluar, 0, ',', '.') ?></p>
                    </div>
                    <p class="text-xs text-gray-400 mt-2.5 pt-2 border-t border-gray-50"><?= count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pengeluaran')) ?> transaksi tercatat</p>
                </div>
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1.5">Laba Bersih</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight <?= $totalLaba >= 0 ? 'text-navy-800' : 'text-red-500' ?>">
                            Rp <?= number_format(abs($totalLaba), 0, ',', '.') ?>
                        </p>
                    </div>
                    <p class="text-xs text-gray-400 mt-2.5 pt-2 border-t border-gray-50"><?= $totalLaba >= 0 ? 'Status: Surplus (Untung)' : 'Status: Defisit (Rugi)' ?></p>
                </div>
            </div>

            <!-- READ: Grafik live -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-6">
                <h3 class="font-semibold text-navy-900 text-sm sm:text-base mb-3 sm:mb-4">Grafik Tren Bulanan Pemasukan vs Pengeluaran</h3>
                <div class="relative w-full h-64 sm:h-72 md:h-80">
                    <canvas id="chartBulanan"></canvas>
                </div>
            </div>

            <!-- CREATE & UPDATE: Form Simpan Laporan -->
            <div class="bg-white rounded-xl border <?= $editLap ? 'border-navy-200 ring-1 ring-navy-100' : 'border-gray-100' ?> shadow-sm p-4 sm:p-6">
                <h3 class="font-semibold text-navy-900 text-sm sm:text-base mb-4 flex items-center gap-2">
                    <?= $editLap ? 'Edit Data Laporan' : '+ Simpan Laporan Periode' ?>
                </h3>
                <form method="POST" action="laporan.php" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <input type="hidden" name="action" value="<?= $editLap ? 'update' : 'simpan' ?>">
                    <?php if ($editLap): ?>
                    <input type="hidden" name="id" value="<?= $editLap['id'] ?>">
                    <?php endif; ?>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Laporan</label>
                        <input type="text" name="judul" required placeholder="Contoh: Laporan Penjualan Q3 2026"
                            value="<?= htmlspecialchars($editLap['judul'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <?php if (!$editLap): ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" required class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" required class="<?= $inputClass ?>">
                    </div>
                    <?php endif; ?>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan Evaluasi</label>
                        <input type="text" name="catatan" placeholder="Catatan singkat evaluasi laporan ini"
                            value="<?= htmlspecialchars($editLap['catatan'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2.5 pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            <?= $editLap ? 'Simpan Perubahan' : 'Simpan Laporan Ini' ?>
                        </button>
                        <?php if ($editLap): ?>
                        <a href="laporan.php" class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- READ, UPDATE, DELETE: Tabel laporan tersimpan -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm sm:text-base">Daftar Laporan Tersimpan</h3>
                </div>
                <?php if (empty($_SESSION['laporan_tersimpan'])): ?>
                <p class="text-center text-gray-400 text-sm py-12">Belum ada laporan tersimpan.</p>
                <?php else: ?>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[620px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Judul & Catatan</th>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-right">Pemasukan</th>
                                <th class="px-4 py-3 text-right">Pengeluaran</th>
                                <th class="px-4 py-3 text-right">Laba/Rugi</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach (array_reverse($_SESSION['laporan_tersimpan']) as $l): ?>
                            <tr class="hover:bg-gray-50/70 transition <?= $l['id'] === $editId ? 'bg-navy-100/40' : '' ?>">
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-navy-900"><?= htmlspecialchars($l['judul']) ?></p>
                                    <?php if ($l['catatan']): ?>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($l['catatan']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                                    <?= date('d M Y', strtotime($l['tanggal_dari'])) ?> —
                                    <?= date('d M Y', strtotime($l['tanggal_sampai'])) ?>
                                </td>
                                <td class="px-4 py-3.5 text-right text-green-600 font-medium whitespace-nowrap">Rp <?= number_format($l['pemasukan'],   0, ',', '.') ?></td>
                                <td class="px-4 py-3.5 text-right text-red-500 font-medium whitespace-nowrap">Rp <?= number_format($l['pengeluaran'], 0, ',', '.') ?></td>
                                <td class="px-4 py-3.5 text-right font-semibold whitespace-nowrap <?= $l['laba'] >= 0 ? 'text-navy-800' : 'text-red-500' ?>">
                                    <?= $l['laba'] >= 0 ? '+' : '' ?>Rp <?= number_format($l['laba'], 0, ',', '.') ?>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="?edit=<?= $l['id'] ?>" class="text-xs text-navy-700 hover:text-navy-950 font-semibold transition">Edit</a>
                                        <form method="POST" action="laporan.php" onsubmit="return confirm('Hapus arsip laporan ini?')">
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                            <button class="text-xs text-red-500 hover:text-red-700 font-semibold transition">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        (function() {
            var ctx = document.getElementById('chartBulanan');
            if (!ctx) return;
            if (window._myChartBulanan) {
                window._myChartBulanan.destroy();
            }
            window._myChartBulanan = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_values($labels)) ?>,
                    datasets: [
                        { label: 'Pemasukan',   data: <?= json_encode(array_column(array_values($bulanData), 'pemasukan')) ?>,   backgroundColor: 'rgba(22,163,74,0.75)',  borderRadius: 4 },
                        { label: 'Pengeluaran', data: <?= json_encode(array_column(array_values($bulanData), 'pengeluaran')) ?>, backgroundColor: 'rgba(239,68,68,0.75)',  borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } } }
                }
            });
        })();
        </script>
    </main>
</body>
</html>
