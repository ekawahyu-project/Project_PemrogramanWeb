<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require_once 'includes/init.php';

$currentPage = 'transaksi';

// ── CRUD Handlers ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE
    if ($action === 'tambah') {
        $jumlah = (int) preg_replace('/\D/', '', $_POST['jumlah'] ?? '0');
        if (trim($_POST['keterangan'] ?? '') && $jumlah > 0) {
            $_SESSION['transaksi'][] = [
                'id'         => uniqid('t'),
                'tanggal'    => $_POST['tanggal']    ?? date('Y-m-d'),
                'keterangan' => trim($_POST['keterangan']),
                'jenis'      => $_POST['jenis']      ?? 'Pemasukan',
                'jumlah'     => $jumlah,
                'produk_id'  => $_POST['produk_id']  ?: null,
            ];
        }
        header('Location: transaksi.php'); exit;
    }

    // UPDATE
    if ($action === 'update') {
        $id     = $_POST['id'] ?? '';
        $jumlah = (int) preg_replace('/\D/', '', $_POST['jumlah'] ?? '0');
        foreach ($_SESSION['transaksi'] as &$t) {
            if ($t['id'] === $id) {
                $t['tanggal']    = $_POST['tanggal']    ?? $t['tanggal'];
                $t['keterangan'] = trim($_POST['keterangan'] ?? $t['keterangan']);
                $t['jenis']      = $_POST['jenis']      ?? $t['jenis'];
                $t['produk_id']  = $_POST['produk_id']  ?: null;
                if ($jumlah > 0) $t['jumlah'] = $jumlah;
                break;
            }
        }
        unset($t);
        header('Location: transaksi.php'); exit;
    }

    // DELETE
    if ($action === 'hapus') {
        $id = $_POST['id'] ?? '';
        $_SESSION['transaksi'] = array_values(
            array_filter($_SESSION['transaksi'], fn($t) => $t['id'] !== $id)
        );
        header('Location: transaksi.php'); exit;
    }
}

// READ — mode edit
$editId  = $_GET['edit'] ?? null;
$editTrx = null;
if ($editId) {
    foreach ($_SESSION['transaksi'] as $t) {
        if ($t['id'] === $editId) { $editTrx = $t; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi — UMKM Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'includes/sidebar.php'; ?>
    <main class="ml-56 min-h-screen flex flex-col">
        <header class="bg-white border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-navy-900">Manajemen Transaksi</h2>
            <p class="text-xs text-gray-500 mt-0.5">Catat dan kelola pemasukan & pengeluaran usaha.</p>
        </header>

        <div class="p-6 flex-1 space-y-6">
            <!-- Form Tambah / Edit (CREATE & UPDATE) -->
            <div class="bg-white rounded-xl border <?= $editTrx ? 'border-navy-200' : 'border-gray-100' ?> shadow-sm p-5">
                <h3 class="font-semibold text-navy-900 text-sm mb-4">
                    <?= $editTrx ? '✏️ Edit Transaksi' : '+ Tambah Transaksi' ?>
                </h3>
                <form method="POST" action="transaksi.php" class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="<?= $editTrx ? 'update' : 'tambah' ?>">
                    <?php if ($editTrx): ?>
                    <input type="hidden" name="id" value="<?= $editTrx['id'] ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" required
                            value="<?= $editTrx['tanggal'] ?? date('Y-m-d') ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis</label>
                        <select name="jenis" class="<?= $inputClass ?>">
                            <option value="Pemasukan"   <?= ($editTrx['jenis'] ?? 'Pemasukan')   === 'Pemasukan'   ? 'selected' : '' ?>>Pemasukan</option>
                            <option value="Pengeluaran" <?= ($editTrx['jenis'] ?? '')             === 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan</label>
                        <input type="text" name="keterangan" required placeholder="Keterangan transaksi"
                            value="<?= htmlspecialchars($editTrx['keterangan'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah (Rp)</label>
                        <input type="number" name="jumlah" required min="1" placeholder="0"
                            value="<?= $editTrx['jumlah'] ?? '' ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Produk Terkait <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <select name="produk_id" class="<?= $inputClass ?>">
                            <option value="">— Tidak terkait produk —</option>
                            <?php foreach ($_SESSION['produk'] as $p): ?>
                            <option value="<?= $p['id'] ?>"
                                <?= ($editTrx['produk_id'] ?? null) === $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-span-2 flex gap-3">
                        <button type="submit"
                            class="px-5 py-2 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            <?= $editTrx ? 'Simpan Perubahan' : 'Tambah' ?>
                        </button>
                        <?php if ($editTrx): ?>
                        <a href="transaksi.php"
                            class="px-5 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                            Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Transaksi (READ & DELETE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm">Daftar Transaksi</h3>
                </div>
                <?php if (empty($_SESSION['transaksi'])): ?>
                <p class="text-center text-gray-400 text-sm py-10">Belum ada transaksi.</p>
                <?php else: ?>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach (array_reverse($_SESSION['transaksi']) as $t): ?>
                        <tr class="hover:bg-gray-50/60 transition <?= $t['id'] === $editId ? 'bg-navy-100/40' : '' ?>">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                <?= date('d M Y', strtotime($t['tanggal'])) ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-navy-900"><?= htmlspecialchars($t['keterangan']) ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?php $p = $t['produk_id'] ? getProdukById($t['produk_id']) : null; ?>
                                <?= $p ? htmlspecialchars($p['nama']) : '—' ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium
                                    <?= $t['jenis'] === 'Pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                    <?= $t['jenis'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold <?= $t['jenis'] === 'Pemasukan' ? 'text-green-600' : 'text-red-500' ?>">
                                <?= ($t['jenis'] === 'Pemasukan' ? '+' : '-') . 'Rp ' . number_format($t['jumlah'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="?edit=<?= $t['id'] ?>" class="text-xs text-navy-700 hover:underline font-medium">Edit</a>
                                    <form method="POST" action="transaksi.php" onsubmit="return confirm('Hapus transaksi ini?')">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                    </form>
                                </div>
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
