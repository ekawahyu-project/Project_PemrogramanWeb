<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login/index.php'); exit; }
require_once '../includes/init.php';

$currentPage = 'stok';
$error       = '';

// ── Handlers ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE — catat pergerakan stok & update produk.stok
    if ($action === 'catat') {
        $produk_id = $_POST['produk_id'] ?? '';
        $jenis     = $_POST['jenis']     ?? 'Masuk';
        $jumlah    = (int) ($_POST['jumlah'] ?? 0);
        $ket       = trim($_POST['keterangan'] ?? '');

        if ($produk_id && $jumlah > 0) {
            $valid = true;
            foreach ($_SESSION['produk'] as &$p) {
                if ($p['id'] === $produk_id) {
                    if ($jenis === 'Keluar' && $p['stok'] < $jumlah) {
                        $error = "Stok {$p['nama']} tidak mencukupi (tersedia: {$p['stok']}).";
                        $valid = false;
                    } else {
                        $p['stok'] = $jenis === 'Masuk' ? $p['stok'] + $jumlah : $p['stok'] - $jumlah;
                    }
                    break;
                }
            }
            unset($p);

            if ($valid) {
                $_SESSION['stok_log'][] = [
                    'id'          => uniqid('sl'),
                    'tanggal'     => $_POST['tanggal'] ?? date('Y-m-d'),
                    'produk_id'   => $produk_id,
                    'jenis'       => $jenis,
                    'jumlah'      => $jumlah,
                    'keterangan'  => $ket,
                ];
                header('Location: index.php'); exit;
            }
        }
    }

    // DELETE — hapus log & balikkan perubahan stok (reversal)
    if ($action === 'hapus_log') {
        $id = $_POST['id'] ?? '';
        foreach ($_SESSION['stok_log'] as $log) {
            if ($log['id'] === $id) {
                foreach ($_SESSION['produk'] as &$p) {
                    if ($p['id'] === $log['produk_id']) {
                        // Balikkan: jika log Masuk, kurangi; jika Keluar, tambah
                        $p['stok'] = $log['jenis'] === 'Masuk'
                            ? max(0, $p['stok'] - $log['jumlah'])
                            : $p['stok'] + $log['jumlah'];
                        break;
                    }
                }
                unset($p);
                break;
            }
        }
        $_SESSION['stok_log'] = array_values(
            array_filter($_SESSION['stok_log'], fn($l) => $l['id'] !== $id)
        );
        header('Location: index.php'); exit;
    }
}

// Hitung stok rendah
$lowStock = array_filter($_SESSION['produk'], fn($p) => $p['stok'] <= $p['stok_min']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok — UMKM Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/sidebar.php'; ?>
    <main class="ml-56 min-h-screen flex flex-col">
        <header class="bg-white border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-navy-900">Manajemen Stok</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pantau stok saat ini dan catat pergerakan barang masuk/keluar.</p>
        </header>

        <div class="p-6 flex-1 space-y-6">
            <?php if ($error): ?>
            <div class="text-sm text-red-600 bg-red-50 border border-red-200 px-4 py-3 rounded-lg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($lowStock)): ?>
            <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <span class="text-orange-500 text-lg">⚠</span>
                <p class="text-sm text-orange-700 font-medium">
                    <?= count($lowStock) ?> produk stok menipis/habis:
                    <span class="font-normal"><?= implode(', ', array_column($lowStock, 'nama')) ?></span>
                </p>
            </div>
            <?php endif; ?>

            <!-- Stok Saat Ini (READ) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm">Stok Saat Ini</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Produk</th>
                            <th class="px-5 py-3 text-left">Kategori</th>
                            <th class="px-5 py-3 text-center">Stok Saat Ini</th>
                            <th class="px-5 py-3 text-center">Stok Min.</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($_SESSION['produk'] as $p):
                            $pct = $p['stok_min'] > 0 ? min(100, ($p['stok'] / max($p['stok_min'] * 2, 1)) * 100) : 100;
                            $barColor = $p['stok'] === 0 ? 'bg-gray-300' : ($p['stok'] <= $p['stok_min'] ? 'bg-orange-400' : 'bg-green-400');
                        ?>
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-5 py-3 font-medium text-navy-900">
                                <?= htmlspecialchars($p['nama']) ?>
                                <span class="text-gray-400 text-xs font-normal">/<?= $p['satuan'] ?></span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs"><?= $p['kategori'] ?></td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="font-semibold text-navy-900 w-6 text-right"><?= $p['stok'] ?></span>
                                    <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full <?= $barColor ?> rounded-full" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center text-gray-500"><?= $p['stok_min'] ?></td>
                            <td class="px-5 py-3 text-center">
                                <?php if ($p['stok'] === 0): ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Habis</span>
                                <?php elseif ($p['stok'] <= $p['stok_min']): ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-600">Menipis</span>
                                <?php else: ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">Normal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Catat Pergerakan (CREATE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-semibold text-navy-900 text-sm mb-4">+ Catat Pergerakan Stok</h3>
                <form method="POST" action="index.php" class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="catat">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Produk</label>
                        <select name="produk_id" required class="<?= $inputClass ?>">
                            <option value="">Pilih produk</option>
                            <?php foreach ($_SESSION['produk'] as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?> (stok: <?= $p['stok'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis</label>
                        <select name="jenis" class="<?= $inputClass ?>">
                            <option value="Masuk">Masuk</option>
                            <option value="Keluar">Keluar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah</label>
                        <input type="number" name="jumlah" required min="1" placeholder="0" class="<?= $inputClass ?>">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Misal: Restock dari supplier" class="<?= $inputClass ?>">
                    </div>
                    <div class="col-span-2">
                        <button type="submit"
                            class="px-5 py-2 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            Catat
                        </button>
                    </div>
                </form>
            </div>

            <!-- Riwayat Pergerakan (READ & DELETE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm">Riwayat Pergerakan</h3>
                </div>
                <?php if (empty($_SESSION['stok_log'])): ?>
                <p class="text-center text-gray-400 text-sm py-8">Belum ada riwayat.</p>
                <?php else: ?>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Produk</th>
                            <th class="px-5 py-3 text-center">Jenis</th>
                            <th class="px-5 py-3 text-center">Jumlah</th>
                            <th class="px-5 py-3 text-left">Keterangan</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach (array_reverse($_SESSION['stok_log']) as $log):
                            $prd = getProdukById($log['produk_id']);
                        ?>
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap"><?= date('d M Y', strtotime($log['tanggal'])) ?></td>
                            <td class="px-5 py-3 font-medium text-navy-900"><?= $prd ? htmlspecialchars($prd['nama']) : '<span class="text-gray-400">Dihapus</span>' ?></td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium
                                    <?= $log['jenis'] === 'Masuk' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-600' ?>">
                                    <?= $log['jenis'] ?>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center font-semibold <?= $log['jenis'] === 'Masuk' ? 'text-blue-600' : 'text-orange-500' ?>">
                                <?= $log['jenis'] === 'Masuk' ? '+' : '-' ?><?= $log['jumlah'] ?>
                            </td>
                            <td class="px-5 py-3 text-gray-500"><?= htmlspecialchars($log['keterangan']) ?: '—' ?></td>
                            <td class="px-5 py-3 text-center">
                                <form method="POST" action="index.php"
                                    onsubmit="return confirm('Hapus log ini? Stok produk akan dibalikkan.')">
                                    <input type="hidden" name="action" value="hapus_log">
                                    <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                    <button class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
