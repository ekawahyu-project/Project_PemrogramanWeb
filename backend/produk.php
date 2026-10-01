<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/init.php';

$currentPage = 'produk';
$kategoriOpt = ['Makanan','Minuman', 'Fashion', 'Kecantikan', 'Bahan Baku', 'Kesehatan & Herbal', 'Perlengkapan Rumah Tangga' ,'Lainnya'];
$satuanOpt   = ['pcs', 'pack', 'kg', 'gram', 'liter', 'ml', 'box', 'lusin', 'meter', 'lembar'];
$errors      = [];
$formData    = null;
$maxAngka    = 1000000000;

$notif = null;

function setFlash(string $icon, string $title, string $text): void
{
    $_SESSION['flash'] = [
        'icon' => $icon,
        'title' => $title,
        'text' => $text,
        'confirmButtonText' => 'OK',
    ];
}

// mengubah input jadi integer lalu return null kalau kosong / bukan bilangan bulat.
function ambilInt($v): ?int
{
    $v = trim((string) $v);
    $hasil = filter_var($v, FILTER_VALIDATE_INT);
    return $hasil === false ? null : $hasil;
}

// Validasi + normalisasi. Return [$data, $errors]
function validasiProduk(array $in, ?string $idSaatIni = null): array
{
    global $kategoriOpt, $satuanOpt, $maxAngka;
    $err = [];

    $nama     = trim($in['nama'] ?? '');
    $kategori = $in['kategori'] ?? '';
    $satuan   = $in['satuan'] ?? '';
    $beli     = ambilInt($in['harga_beli'] ?? '');
    $jual     = ambilInt($in['harga_jual'] ?? '');
    $stok     = ambilInt($in['stok'] ?? '');
    $stokMin  = ambilInt($in['stok_min'] ?? '');

    // Nama
    if ($nama === '') {
        $err[] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($nama) > 100) {
        $err[] = 'Nama produk maksimal 100 karakter.';
    } else {
        foreach ($_SESSION['produk'] as $p) {
            if ($p['id'] !== $idSaatIni && mb_strtolower($p['nama']) === mb_strtolower($nama)) {
                $err[] = 'Nama produk sudah digunakan.';
                break;
            }
        }
    }

    // Kategori & satuan
    if (!in_array($kategori, $kategoriOpt, true)) $err[] = 'Kategori tidak valid.';
    if (!in_array($satuan, $satuanOpt, true))     $err[] = 'Satuan tidak valid.';

    // Harga
    if ($beli === null || $beli < 0)      $err[] = 'Harga beli harus berupa angka bulat 0 atau lebih.';
    if ($jual === null || $jual <= 0)     $err[] = 'Harga jual harus berupa angka bulat lebih dari 0.';
    if ($beli !== null && $jual !== null && $jual < $beli) {
        $err[] = 'Harga jual tidak boleh lebih rendah dari harga beli.';
    }
    if (($beli !== null && $beli > $maxAngka) || ($jual !== null && $jual > $maxAngka)) {
        $err[] = 'Harga terlalu besar.';
    }

    // Stok
    if ($stok === null || $stok < 0)       $err[] = 'Stok harus berupa angka bulat 0 atau lebih.';
    if ($stokMin === null || $stokMin < 0) $err[] = 'Stok minimum harus berupa angka bulat 0 atau lebih.';
    if (($stok !== null && $stok > $maxAngka) || ($stokMin !== null && $stokMin > $maxAngka)) {
        $err[] = 'Nilai stok terlalu besar.';
    }

    $data = [
        'nama'       => $nama,
        'kategori'   => $kategori,
        'harga_beli' => $beli,
        'harga_jual' => $jual,
        'satuan'     => $satuan,
        'stok'       => $stok,
        'stok_min'   => $stokMin,
    ];
    return [$data, $err];
}

// CRUD Handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE & UPDATE
    if ($action === 'tambah' || $action === 'update') {
        $id = $action === 'update' ? ($_POST['id'] ?? '') : null;

        [$data, $errors] = validasiProduk($_POST, $id);

        if ($action === 'update' && getProdukById($id) === null) {
            $errors[] = 'Produk yang diedit tidak ditemukan.';
        }

        if (empty($errors)) {
            if ($action === 'tambah') {
                $_SESSION['produk'][] = ['id' => uniqid('p')] + $data;
                $pesan = 'Produk berhasil ditambahkan.';
            } else {
                foreach ($_SESSION['produk'] as $i => $p) {
                    if ($p['id'] === $id) {
                        $_SESSION['produk'][$i] = ['id' => $id] + $data;
                        break;
                    }
                }
                $pesan = 'Produk berhasil diperbarui.';
            }

            if (simpanProduk()) {
                setFlash('success', 'Sukses!', $pesan);
                header('Location: produk.php');
                exit;
            }
            $errors[] = 'Data gagal ditulis ke file. Periksa izin folder data/.';
        }

        // simpan input untuk mengisi ulang form jika gagal
        $formData = $_POST;
    }

    // DELETE
    if ($action === 'hapus') {
        $id = $_POST['id'] ?? '';
        $_SESSION['produk'] = array_values(
            array_filter($_SESSION['produk'], fn($p) => $p['id'] !== $id)
        );
        foreach ($_SESSION['transaksi'] as &$t) {
            if ($t['produk_id'] === $id) $t['produk_id'] = null;
        }
        unset($t);
        if (simpanProduk()) {
            setFlash('success', 'Terhapus!', 'Produk berhasil dihapus.');
        } else {
            setFlash('error', 'Gagal!', 'Data gagal disimpan ke file.');
        }
        header('Location: produk.php');
        exit;
    }
}

// Notifikasi: flash dari redirect, atau error validasi pada request ini
if (isset($_SESSION['flash'])) {
    $notif = $_SESSION['flash'];
    unset($_SESSION['flash']);
} elseif ($errors) {
    $daftar = implode('', array_map(
        fn($e) => '<li style="margin-bottom:4px">• ' . htmlspecialchars($e) . '</li>',
        $errors
    ));
    $notif = [
        'icon' => 'error',
        'title' => 'Gagal!',
        'html' => '<ul style="text-align:left;list-style:none;padding:0;font-size:14px">' . $daftar . '</ul>',
        'confirmButtonText' => 'Coba lagi',
    ];
}
// fitur edit tetap aktif kalau update gagal
$editId = ($_POST['action'] ?? '') === 'update' ? ($_POST['id'] ?? null) : ($_GET['edit'] ?? null);
$editPrd = $editId ? getProdukById($editId) : null;

// prioritas input yang gagal, lalu data produk, lalu default
$raw = fn(string $k, $default = '') => (string) ($formData[$k] ?? $editPrd[$k] ?? $default);
$val = fn(string $k, $default = '') => htmlspecialchars($raw($k, $default));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        navy: {
                            '100': '#dbe8ff',
                            '700': '#1e4080',
                            '800': '#162e5e',
                            '900': '#0e1f42',
                            '950': '#080f21'
                        }
                    }
                }
            }
        }
    </script>
</head>

<?php include __DIR__ . '/includes/sidebar.php'; ?>
<main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
    <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 max-w-7xl w-full mx-auto">
        <!-- Floating Header Card -->
        <header class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-navy-900 tracking-tight">Manajemen Produk</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Kelola data katalog produk: tambah, update, atau hapus produk.</p>
            </div>
        </header>
        <?php if ($errors): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm rounded-xl p-4">
                <p class="font-semibold mb-1">Periksa kembali formulir:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Tambah / Edit (CREATE & UPDATE) -->
        <div class="bg-white rounded-xl border <?= $editPrd ? 'border-navy-700 ring-1 ring-navy-100' : 'border-gray-100' ?> shadow-sm p-4 sm:p-6">
            <h3 class="font-semibold text-navy-900 text-sm sm:text-base mb-4 flex items-center gap-2">
                <?= $editPrd ? 'Edit Data Produk' : '+ Tambah Produk Baru' ?>
            </h3>
            <form method="POST" action="produk.php" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <input type="hidden" name="action" value="<?= $editPrd ? 'update' : 'tambah' ?>">
                <?php if ($editPrd): ?>
                    <input type="hidden" name="id" value="<?= $editPrd['id'] ?>">
                <?php endif; ?>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Produk</label>
                    <input type="text" name="nama" required maxlength="100" placeholder="Masukkan nama produk"
                        value="<?= $val('nama') ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
                    <select name="kategori" class="<?= $inputClass ?>">
                        <?php foreach ($kategoriOpt as $k): ?>
                            <option value="<?= htmlspecialchars($k) ?>" <?= $raw('kategori', 'Lainnya') === $k ? 'selected' : '' ?>><?= htmlspecialchars($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Satuan</label>
                    <select name="satuan" class="<?= $inputClass ?>">
                        <?php foreach ($satuanOpt as $s): ?>
                            <option value="<?= $s ?>" <?= $raw('satuan', 'pcs') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" required min="0" step="1" placeholder="0"
                        value="<?= $val('harga_beli') ?>" class="no-spinner <?= $inputClass ?>">
                </div>

                 <!-- menghilangkan arrow atas bawah(harga beli dan jual only) -->
                <style>
                    .no-spinner::-webkit-outer-spin-button,
                    .no-spinner::-webkit-inner-spin-button {
                        -webkit-appearance: none;
                        margin: 0;
                    }

                    .no-spinner {
                        -moz-appearance: textfield;
                        appearance: textfield;
                    }
                </style>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" required min="1" step="1" placeholder="0"
                        value="<?= $val('harga_jual') ?>" class="no-spinner <?= $inputClass ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5"><?= $editPrd ? 'Stok Saat Ini' : 'Stok Awal' ?></label>
                    <input type="number" name="stok" required min="0" step="1" placeholder="0"
                        value="<?= $val('stok', 0) ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Batas Stok Minimum</label>
                    <input type="number" name="stok_min" required min="0" step="1" placeholder="5"
                        value="<?= $val('stok_min', 5) ?>" class="<?= $inputClass ?>">
                </div>
                <div class="sm:col-span-2 flex flex-wrap items-center gap-2.5 pt-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                        <?= $editPrd ? 'Simpan Perubahan' : 'Tambah Produk' ?>
                    </button>
                    <?php if ($editPrd): ?>
                        <a href="produk.php"
                            class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Produk (READ & DELETE) -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                <h3 class="font-semibold text-navy-900 text-sm sm:text-base">Daftar Katalog Produk (<?= count($_SESSION['produk']) ?>)</h3>
            </div>
            <?php if (empty($_SESSION['produk'])): ?>
                <p class="text-center text-gray-400 text-sm py-12">Belum ada produk.</p>
            <?php else: ?>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[650px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Nama Produk</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-right">Harga Beli</th>
                                <th class="px-4 py-3 text-right">Harga Jual</th>
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
                                <tr class="hover:bg-gray-50/70 transition <?= $p['id'] === $editId ? 'bg-navy-100/40' : '' ?>">
                                    <td class="px-4 py-3.5 font-medium text-navy-900">
                                        <?= htmlspecialchars($p['nama']) ?>
                                        <span class="text-xs text-gray-400 font-normal">/<?= $p['satuan'] ?></span>
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-500 text-xs"><?= $p['kategori'] ?></td>
                                    <td class="px-4 py-3.5 text-right text-gray-600 whitespace-nowrap">Rp <?= number_format($p['harga_beli'], 0, ',', '.') ?></td>
                                    <td class="px-4 py-3.5 text-right font-medium text-navy-800 whitespace-nowrap">Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?></td>
                                    <td class="px-4 py-3.5 text-center font-semibold"><?= $p['stok'] ?></td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <?php if ($stokEmpty): ?>
                                            <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Habis</span>
                                        <?php elseif ($stokLow): ?>
                                            <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-600">Menipis</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="?edit=<?= $p['id'] ?>" class="text-xs text-navy-700 hover:text-navy-950 font-semibold transition">Edit</a>
                                            <form method="POST" action="produk.php" onsubmit="return confirm('Hapus produk \'<?= htmlspecialchars(addslashes($p['nama'])) ?>\'?')">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

    <?php if ($notif): ?>
        <script>
            Swal.fire(<?= json_encode($notif, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
        </script>
    <?php endif; ?>

</main>
</body>

</html>