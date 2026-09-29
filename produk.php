<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
require_once 'includes/init.php';

$currentPage = 'produk';
$kategoriOpt = ['Makanan & Minuman', 'Fashion', 'Kerajinan', 'Jasa', 'Kecantikan', 'Elektronik', 'Lainnya'];
$satuanOpt   = ['pcs', 'pack', 'kg', 'gram', 'liter', 'ml', 'box', 'lusin', 'meter', 'lembar'];
$error       = '';

// ── CRUD Handlers ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE
    if ($action === 'tambah') {
        $nama = trim($_POST['nama'] ?? '');
        if ($nama) {
            $_SESSION['produk'][] = [
                'id'         => uniqid('p'),
                'nama'       => $nama,
                'kategori'   => $_POST['kategori']               ?? 'Lainnya',
                'harga_beli' => (int) preg_replace('/\D/', '', $_POST['harga_beli'] ?? '0'),
                'harga_jual' => (int) preg_replace('/\D/', '', $_POST['harga_jual'] ?? '0'),
                'satuan'     => trim($_POST['satuan']             ?? 'pcs'),
                'stok'       => (int) ($_POST['stok']            ?? 0),
                'stok_min'   => (int) ($_POST['stok_min']        ?? 5),
            ];
            header('Location: produk.php'); exit;
        }
        $error = 'Nama produk wajib diisi.';
    }

    // UPDATE
    if ($action === 'update') {
        $id = $_POST['id'] ?? '';
        foreach ($_SESSION['produk'] as &$p) {
            if ($p['id'] === $id) {
                $p['nama']       = trim($_POST['nama']             ?? $p['nama']);
                $p['kategori']   = $_POST['kategori']              ?? $p['kategori'];
                $p['harga_beli'] = (int) preg_replace('/\D/', '', $_POST['harga_beli'] ?? $p['harga_beli']);
                $p['harga_jual'] = (int) preg_replace('/\D/', '', $_POST['harga_jual'] ?? $p['harga_jual']);
                $p['satuan']     = trim($_POST['satuan']           ?? $p['satuan']);
                $p['stok']       = (int) ($_POST['stok']          ?? $p['stok']);
                $p['stok_min']   = (int) ($_POST['stok_min']      ?? $p['stok_min']);
                break;
            }
        }
        unset($p);
        header('Location: produk.php'); exit;
    }

    // DELETE
    if ($action === 'hapus') {
        $id = $_POST['id'] ?? '';
        $_SESSION['produk'] = array_values(
            array_filter($_SESSION['produk'], fn($p) => $p['id'] !== $id)
        );
        // Bersihkan referensi produk di transaksi
        foreach ($_SESSION['transaksi'] as &$t) {
            if ($t['produk_id'] === $id) $t['produk_id'] = null;
        }
        unset($t);
        header('Location: produk.php'); exit;
    }
}

// READ — mode edit
$editId  = $_GET['edit'] ?? null;
$editPrd = null;
if ($editId) {
    foreach ($_SESSION['produk'] as $p) {
        if ($p['id'] === $editId) { $editPrd = $p; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk — UMKM Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'includes/sidebar.php'; ?>
    <main class="ml-56 min-h-screen flex flex-col">
        <header class="bg-white border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-navy-900">Manajemen Produk</h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola data produk — tambah, update, atau hapus produk.</p>
        </header>

        <div class="p-6 flex-1 space-y-6">
            <?php if ($error): ?>
            <p class="text-sm text-red-600 bg-red-50 px-4 py-2.5 rounded-lg"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- Form Tambah / Edit (CREATE & UPDATE) -->
            <div class="bg-white rounded-xl border <?= $editPrd ? 'border-navy-200' : 'border-gray-100' ?> shadow-sm p-5">
                <h3 class="font-semibold text-navy-900 text-sm mb-4">
                    <?= $editPrd ? '✏️ Edit Produk' : '+ Tambah Produk' ?>
                </h3>
                <form method="POST" action="produk.php" class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="<?= $editPrd ? 'update' : 'tambah' ?>">
                    <?php if ($editPrd): ?>
                    <input type="hidden" name="id" value="<?= $editPrd['id'] ?>">
                    <?php endif; ?>

                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Produk</label>
                        <input type="text" name="nama" required placeholder="Masukkan nama produk"
                            value="<?= htmlspecialchars($editPrd['nama'] ?? '') ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
                        <select name="kategori" class="<?= $inputClass ?>">
                            <?php foreach ($kategoriOpt as $k): ?>
                            <option value="<?= $k ?>" <?= ($editPrd['kategori'] ?? '') === $k ? 'selected' : '' ?>><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Satuan</label>
                        <select name="satuan" class="<?= $inputClass ?>">
                            <?php foreach ($satuanOpt as $s): ?>
                            <option value="<?= $s ?>" <?= ($editPrd['satuan'] ?? 'pcs') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Beli (Rp)</label>
                        <input type="number" name="harga_beli" min="0" placeholder="0"
                            value="<?= $editPrd['harga_beli'] ?? '' ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" min="0" placeholder="0"
                            value="<?= $editPrd['harga_jual'] ?? '' ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Stok Awal</label>
                        <input type="number" name="stok" min="0" placeholder="0"
                            value="<?= $editPrd['stok'] ?? 0 ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Stok Minimum</label>
                        <input type="number" name="stok_min" min="0" placeholder="5"
                            value="<?= $editPrd['stok_min'] ?? 5 ?>"
                            class="<?= $inputClass ?>">
                    </div>
                    <div class="col-span-2 flex gap-3">
                        <button type="submit"
                            class="px-5 py-2 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            <?= $editPrd ? 'Simpan Perubahan' : 'Tambah Produk' ?>
                        </button>
                        <?php if ($editPrd): ?>
                        <a href="produk.php"
                            class="px-5 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                            Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Produk (READ & DELETE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm">Daftar Produk (<?= count($_SESSION['produk']) ?>)</h3>
                </div>
                <?php if (empty($_SESSION['produk'])): ?>
                <p class="text-center text-gray-400 text-sm py-10">Belum ada produk.</p>
                <?php else: ?>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-right">H. Beli</th>
                            <th class="px-4 py-3 text-right">H. Jual</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($_SESSION['produk'] as $p):
                            $stokOk = $p['stok'] > $p['stok_min'];
                            $stokLow = $p['stok'] > 0 && $p['stok'] <= $p['stok_min'];
                            $stokEmpty = $p['stok'] === 0;
                        ?>
                        <tr class="hover:bg-gray-50/60 transition <?= $p['id'] === $editId ? 'bg-navy-100/40' : '' ?>">
                            <td class="px-4 py-3 font-medium text-navy-900">
                                <?= htmlspecialchars($p['nama']) ?>
                                <span class="text-xs text-gray-400 font-normal">/<?= $p['satuan'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs"><?= $p['kategori'] ?></td>
                            <td class="px-4 py-3 text-right text-gray-600">Rp <?= number_format($p['harga_beli'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-right font-medium text-navy-800">Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-center font-semibold"><?= $p['stok'] ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($stokEmpty): ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Habis</span>
                                <?php elseif ($stokLow): ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-600">Menipis</span>
                                <?php else: ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="?edit=<?= $p['id'] ?>" class="text-xs text-navy-700 hover:underline font-medium">Edit</a>
                                    <form method="POST" action="produk.php" onsubmit="return confirm('Hapus produk \'<?= htmlspecialchars(addslashes($p['nama'])) ?>\'?')">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
