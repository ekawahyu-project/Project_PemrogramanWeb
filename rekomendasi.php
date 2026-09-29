<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require_once 'includes/init.php';

$currentPage    = 'rekomendasi';
$prioritasOpt   = ['Tinggi', 'Sedang', 'Rendah'];
$prioritasColor = ['Tinggi' => 'bg-red-100 text-red-700', 'Sedang' => 'bg-yellow-100 text-yellow-700', 'Rendah' => 'bg-gray-100 text-gray-600'];

// ── CRUD: Catatan Rekomendasi (Action Items) ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE
    if ($action === 'tambah') {
        $judul     = trim($_POST['judul']    ?? '');
        $isi       = trim($_POST['isi']      ?? '');
        $prioritas = $_POST['prioritas']      ?? 'Sedang';
        if ($judul && $isi) {
            $_SESSION['catatan_rekomendasi'][] = [
                'id'        => uniqid('cr'),
                'judul'     => $judul,
                'isi'       => $isi,
                'prioritas' => $prioritas,
                'dibuat'    => date('Y-m-d'),
            ];
            header('Location: rekomendasi.php'); exit;
        }
    }

    // UPDATE
    if ($action === 'update') {
        $id = $_POST['id'] ?? '';
        foreach ($_SESSION['catatan_rekomendasi'] as &$c) {
            if ($c['id'] === $id) {
                $c['judul']     = trim($_POST['judul']    ?? $c['judul']);
                $c['isi']       = trim($_POST['isi']      ?? $c['isi']);
                $c['prioritas'] = $_POST['prioritas']      ?? $c['prioritas'];
                break;
            }
        }
        unset($c);
        header('Location: rekomendasi.php'); exit;
    }

    // DELETE
    if ($action === 'hapus') {
        $id = $_POST['id'] ?? '';
        $_SESSION['catatan_rekomendasi'] = array_values(
            array_filter($_SESSION['catatan_rekomendasi'], fn($c) => $c['id'] !== $id)
        );
        header('Location: rekomendasi.php'); exit;
    }
}

// Edit mode
$editId  = $_GET['edit'] ?? null;
$editCat = null;
if ($editId) {
    foreach ($_SESSION['catatan_rekomendasi'] as $c) {
        if ($c['id'] === $editId) { $editCat = $c; break; }
    }
}

// ── Analisis otomatis (READ-only) ───────────────────────────────
$produkStats = [];
foreach ($_SESSION['transaksi'] as $t) {
    if ($t['jenis'] !== 'Pemasukan' || !$t['produk_id']) continue;
    $pid = $t['produk_id'];
    if (!isset($produkStats[$pid])) {
        $p = getProdukById($pid);
        $produkStats[$pid] = ['count' => 0, 'revenue' => 0, 'nama' => $p ? $p['nama'] : 'Dihapus'];
    }
    $produkStats[$pid]['count']++;
    $produkStats[$pid]['revenue'] += $t['jumlah'];
}
uasort($produkStats, fn($a, $b) => $b['count'] - $a['count']);

$lowStock   = array_filter($_SESSION['produk'], fn($p) => $p['stok'] <= $p['stok_min']);
$soldIds    = array_keys($produkStats);
$unsoldProd = array_filter($_SESSION['produk'], fn($p) => !in_array($p['id'], $soldIds));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'includes/sidebar.php'; ?>
    <main class="ml-56 min-h-screen flex flex-col">
        <header class="bg-white border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-navy-900">Sistem Rekomendasi</h2>
            <p class="text-xs text-gray-500 mt-0.5">Analisis otomatis + catatan tindak lanjut yang bisa dikelola.</p>
        </header>

        <div class="p-6 flex-1 space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <!-- Kolom kiri: Analisis otomatis (READ) -->
                <div class="space-y-5">
                    <!-- Produk Terlaris -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="font-bold text-navy-900 text-sm">Produk Terlaris</h3>
                        </div>
                        <?php if (empty($produkStats)): ?>
                        <p class="text-gray-400 text-xs text-center py-5">Belum ada data penjualan terhubung ke produk.</p>
                        <?php else: ?>
                        <div class="divide-y divide-gray-50">
                            <?php $rank = 1; foreach ($produkStats as $pid => $s): ?>
                            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/60">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold
                                        <?= $rank === 1 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $rank ?>
                                    </span>
                                    <span class="text-sm font-medium text-navy-900"><?= htmlspecialchars($s['nama']) ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-semibold text-navy-800"><?= $s['count'] ?>x jual</p>
                                    <p class="text-xs text-gray-400">Rp <?= number_format($s['revenue'], 0, ',', '.') ?></p>
                                </div>
                            </div>
                            <?php $rank++; endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Perlu Restock -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="font-bold text-navy-900 text-sm">Perlu Restock</h3>
                        </div>
                        <?php if (empty($lowStock)): ?>
                        <p class="text-gray-400 text-xs text-center py-5">Semua stok aman.</p>
                        <?php else: ?>
                        <div class="divide-y divide-gray-50">
                            <?php foreach ($lowStock as $p): $saran = ($p['stok_min'] - $p['stok']) + $p['stok_min'] * 2; ?>
                            <div class="px-5 py-3 hover:bg-gray-50/60">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm font-medium text-navy-900"><?= htmlspecialchars($p['nama']) ?></p>
                                    <span class="text-xs text-orange-600 font-medium"><?= $p['stok'] === 0 ? 'Habis' : 'Menipis' ?></span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">Stok: <?= $p['stok'] ?> / Min: <?= $p['stok_min'] ?> — Saran isi: <?= $saran ?> <?= $p['satuan'] ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Produk Belum Terjual -->
                    <?php if (!empty($unsoldProd)): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="font-bold text-navy-900 text-sm">Belum Terjual</h3>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <?php foreach ($unsoldProd as $p): ?>
                            <div class="px-5 py-3 hover:bg-gray-50/60">
                                <p class="text-sm font-medium text-navy-900"><?= htmlspecialchars($p['nama']) ?></p>
                                <p class="text-xs text-gray-400 mt-0.5">Stok: <?= $p['stok'] ?> — Pertimbangkan promosi</p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Kolom kanan: Catatan Tindak Lanjut (CRUD) -->
                <div class="space-y-5">
                    <!-- CREATE & UPDATE: Form catatan -->
                    <div class="bg-white rounded-xl border <?= $editCat ? 'border-navy-200' : 'border-gray-100' ?> shadow-sm p-5">
                        <h3 class="font-semibold text-navy-900 text-sm mb-4">
                            <?= $editCat ? 'Edit Catatan' : '+ Tambah Catatan Tindak Lanjut' ?>
                        </h3>
                        <form method="POST" action="rekomendasi.php" class="space-y-3">
                            <input type="hidden" name="action" value="<?= $editCat ? 'update' : 'tambah' ?>">
                            <?php if ($editCat): ?>
                            <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul</label>
                                <input type="text" name="judul" required placeholder="Judul tindak lanjut"
                                    value="<?= htmlspecialchars($editCat['judul'] ?? '') ?>"
                                    class="<?= $inputClass ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan</label>
                                <textarea name="isi" required rows="3" placeholder="Jelaskan tindak lanjut yang akan dilakukan..."
                                    class="<?= $inputClass ?> resize-none"><?= htmlspecialchars($editCat['isi'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prioritas</label>
                                <select name="prioritas" class="<?= $inputClass ?>">
                                    <?php foreach ($prioritasOpt as $pr): ?>
                                    <option value="<?= $pr ?>" <?= ($editCat['prioritas'] ?? 'Sedang') === $pr ? 'selected' : '' ?>><?= $pr ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex gap-3 pt-1">
                                <button type="submit"
                                    class="px-5 py-2 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                                    <?= $editCat ? 'Simpan' : 'Tambah' ?>
                                </button>
                                <?php if ($editCat): ?>
                                <a href="rekomendasi.php" class="px-5 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- READ & DELETE: Daftar catatan -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-navy-900 text-sm">Daftar Tindak Lanjut</h3>
                        </div>
                        <?php if (empty($_SESSION['catatan_rekomendasi'])): ?>
                        <p class="text-center text-gray-400 text-xs py-6">Belum ada catatan. Tambah di atas.</p>
                        <?php else: ?>
                        <div class="divide-y divide-gray-50">
                            <?php foreach (array_reverse($_SESSION['catatan_rekomendasi']) as $c): ?>
                            <div class="px-5 py-3.5 hover:bg-gray-50/60 transition <?= $c['id'] === $editId ? 'bg-navy-100/40' : '' ?>">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded-md text-xs font-medium <?= $prioritasColor[$c['prioritas']] ?? 'bg-gray-100 text-gray-500' ?>">
                                                <?= $c['prioritas'] ?>
                                            </span>
                                            <p class="font-medium text-navy-900 text-sm truncate"><?= htmlspecialchars($c['judul']) ?></p>
                                        </div>
                                        <p class="text-xs text-gray-500 leading-relaxed"><?= htmlspecialchars($c['isi']) ?></p>
                                        <p class="text-xs text-gray-300 mt-1"><?= date('d M Y', strtotime($c['dibuat'])) ?></p>
                                    </div>
                                    <div class="flex flex-col gap-1 flex-shrink-0">
                                        <a href="?edit=<?= $c['id'] ?>" class="text-xs text-navy-700 hover:underline font-medium">Edit</a>
                                        <form method="POST" action="rekomendasi.php" onsubmit="return confirm('Hapus catatan ini?')">
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
