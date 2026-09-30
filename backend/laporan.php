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

    // CREATE
    if ($action === 'simpan') {
        $judul    = trim($_POST['judul']          ?? '');
        $kategori = trim($_POST['kategori']       ?? 'Evaluasi Bulanan');
        $dari     = $_POST['tanggal_dari']          ?? '';
        $sampai   = $_POST['tanggal_sampai']        ?? '';
        $status   = trim($_POST['status']         ?? 'Selesai');
        $catatan  = trim($_POST['catatan']          ?? '');

        if ($judul && $dari && $sampai && $dari <= $sampai) {
            $_SESSION['laporan_tersimpan'][] = [
                'id'             => uniqid('l'),
                'judul'          => $judul,
                'kategori'       => $kategori,
                'tanggal_dari'   => $dari,
                'tanggal_sampai' => $sampai,
                'status'         => $status,
                'catatan'        => $catatan,
                'dibuat'         => date('Y-m-d'),
                'pembuat'        => $_SESSION['user'] ?? 'Admin',
            ];
            header('Location: laporan.php'); exit;
        }
    }

    // UPDATE
    if ($action === 'update') {
        $id = $_POST['id'] ?? '';
        foreach ($_SESSION['laporan_tersimpan'] as &$l) {
            if ($l['id'] === $id) {
                $l['judul']          = trim($_POST['judul']          ?? $l['judul']);
                $l['kategori']       = trim($_POST['kategori']       ?? ($l['kategori'] ?? 'Evaluasi Bulanan'));
                $l['tanggal_dari']   = $_POST['tanggal_dari']          ?? ($l['tanggal_dari'] ?? '');
                $l['tanggal_sampai'] = $_POST['tanggal_sampai']        ?? ($l['tanggal_sampai'] ?? '');
                $l['status']         = trim($_POST['status']         ?? ($l['status'] ?? 'Selesai'));
                $l['catatan']        = trim($_POST['catatan']        ?? $l['catatan']);
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
            <!-- READ: Ringkasan Total (live) - Soft Pastel Tint (Clean) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-emerald-50/70 border border-emerald-100/90 rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-semibold text-emerald-900/70 mb-1.5">Total Pemasukan</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight text-emerald-700">Rp <?= number_format($totalMasuk,  0, ',', '.') ?></p>
                    </div>
                    <p class="text-xs text-emerald-700/70 mt-2.5 pt-2 border-t border-emerald-100"><?= count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pemasukan')) ?> transaksi tercatat</p>
                </div>

                <div class="bg-rose-50/70 border border-rose-100/90 rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-semibold text-rose-900/70 mb-1.5">Total Pengeluaran</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight text-rose-700">Rp <?= number_format($totalKeluar, 0, ',', '.') ?></p>
                    </div>
                    <p class="text-xs text-rose-700/70 mt-2.5 pt-2 border-t border-rose-100"><?= count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pengeluaran')) ?> transaksi tercatat</p>
                </div>

                <div class="<?= $totalLaba >= 0 ? 'bg-indigo-50/70 border-indigo-100/90' : 'bg-rose-50/70 border-rose-100/90' ?> border rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-semibold <?= $totalLaba >= 0 ? 'text-indigo-900/70' : 'text-rose-900/70' ?> mb-1.5">Laba Bersih</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight <?= $totalLaba >= 0 ? 'text-indigo-700' : 'text-rose-700' ?>">
                            Rp <?= number_format(abs($totalLaba), 0, ',', '.') ?>
                        </p>
                    </div>
                    <p class="text-xs <?= $totalLaba >= 0 ? 'text-indigo-700/70 border-indigo-100' : 'text-rose-700/70 border-rose-100' ?> mt-2.5 pt-2 border-t"><?= $totalLaba >= 0 ? 'Status: Surplus (Untung)' : 'Status: Defisit (Rugi)' ?></p>
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

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Laporan</label>
                        <input type="text" name="judul" required placeholder="Contoh: Laporan Penjualan Q3 2026"
                            value="<?= htmlspecialchars($editLap['judul'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori Laporan</label>
                        <?php $currKat = $editLap['kategori'] ?? 'Evaluasi Bulanan'; ?>
                        <select name="kategori" class="<?= $inputClass ?>">
                            <option value="Evaluasi Bulanan" <?= $currKat === 'Evaluasi Bulanan' ? 'selected' : '' ?>>Evaluasi Bulanan</option>
                            <option value="Penjualan & Produk" <?= $currKat === 'Penjualan & Produk' ? 'selected' : '' ?>>Penjualan & Produk</option>
                            <option value="Keuangan & Kas" <?= $currKat === 'Keuangan & Kas' ? 'selected' : '' ?>>Keuangan & Kas</option>
                            <option value="Operasional & Stok" <?= $currKat === 'Operasional & Stok' ? 'selected' : '' ?>>Operasional & Stok</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" required
                            value="<?= htmlspecialchars($editLap['tanggal_dari'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" required
                            value="<?= htmlspecialchars($editLap['tanggal_sampai'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status Laporan</label>
                        <?php $currStatus = $editLap['status'] ?? 'Selesai'; ?>
                        <select name="status" class="<?= $inputClass ?>">
                            <option value="Selesai" <?= $currStatus === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="Dalam Tinjauan" <?= $currStatus === 'Dalam Tinjauan' ? 'selected' : '' ?>>Dalam Tinjauan</option>
                            <option value="Draft" <?= $currStatus === 'Draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>

                    <div>
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
                    <table class="w-full text-xs sm:text-sm min-w-[700px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Judul & Kategori</th>
                                <th class="px-4 py-3 text-left">Periode Laporan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-left">Catatan Evaluasi</th>
                                <th class="px-4 py-3 text-center">Dibuat</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach (array_reverse($_SESSION['laporan_tersimpan']) as $l): ?>
                            <tr class="hover:bg-gray-50/70 transition <?= ($l['id'] ?? '') === $editId ? 'bg-navy-100/40' : '' ?>">
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-navy-900"><?= htmlspecialchars($l['judul'] ?? '') ?></p>
                                    <span class="inline-block mt-1 text-[11px] font-medium px-2 py-0.5 rounded bg-navy-100/70 text-navy-800">
                                        <?= htmlspecialchars($l['kategori'] ?? 'Evaluasi Bulanan') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span><?= !empty($l['tanggal_dari']) ? date('d M Y', strtotime($l['tanggal_dari'])) : '-' ?> - <?= !empty($l['tanggal_sampai']) ? date('d M Y', strtotime($l['tanggal_sampai'])) : '-' ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <?php
                                    $st = $l['status'] ?? 'Selesai';
                                    if ($st === 'Selesai'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="bg-emerald-50"></span>Selesai
                                        </span>
                                    <?php elseif ($st === 'Dalam Tinjauan'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Dalam Tinjauan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Draft
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-600 max-w-xs">
                                    <p class="truncate" title="<?= htmlspecialchars($l['catatan'] ?? '-') ?>">
                                        <?= htmlspecialchars(!empty($l['catatan']) ? $l['catatan'] : '-') ?>
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-500 text-xs whitespace-nowrap">
                                    <?= !empty($l['dibuat']) ? date('d M Y', strtotime($l['dibuat'])) : '-' ?>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="?edit=<?= $l['id'] ?>" class="text-xs text-navy-700 hover:text-navy-950 font-semibold transition">Edit</a>
                                        <form method="POST" action="laporan.php" onsubmit="return confirm('Hapus arsip laporan ini?')">
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold transition">Hapus</button>
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
