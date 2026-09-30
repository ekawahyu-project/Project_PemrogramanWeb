<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/init.php';

$currentPage = 'profil';
$message = '';
$messageType = '';

$usernameLogin = $_SESSION['user'];

// Ambil akun pengguna yang sedang login dari session users
$akun = $_SESSION['users'][$usernameLogin] ?? [];

// Inisialisasi profil jika belum ada di session profil
if (!isset($_SESSION['profil'])) {
    $_SESSION['profil'] = [
        'no_hp'      => $akun['no_hp']      ?? '',
        'nama_usaha' => $akun['nama_usaha'] ?? '',
        'kategori'   => $akun['kategori']   ?? '',
        'alamat'     => $akun['alamat']     ?? ''
    ];
}

// Proses simpan profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $nama       = trim($_POST['nama'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $nama_usaha = trim($_POST['nama_usaha'] ?? '');
    $kategori   = trim($_POST['kategori'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');

    if (!$nama || !$email) {
        $message = 'Nama lengkap dan alamat email wajib diisi.';
        $messageType = 'error';
    } else {
        // Cek duplikasi email ke akun pengguna lain
        $emailConflict = false;
        foreach ($_SESSION['users'] as $uKey => $uData) {
            if ($uKey !== $usernameLogin && strtolower($uData['email'] ?? '') === strtolower($email)) {
                $emailConflict = true;
                break;
            }
        }

        if ($emailConflict) {
            $message = 'Alamat email sudah digunakan oleh akun lain.';
            $messageType = 'error';
        } else {
            // Update session users
            if (isset($_SESSION['users'][$usernameLogin])) {
                $_SESSION['users'][$usernameLogin]['nama']       = $nama;
                $_SESSION['users'][$usernameLogin]['email']      = $email;
                $_SESSION['users'][$usernameLogin]['no_hp']      = $no_hp;
                $_SESSION['users'][$usernameLogin]['nama_usaha'] = $nama_usaha;
                $_SESSION['users'][$usernameLogin]['kategori']   = $kategori;
                $_SESSION['users'][$usernameLogin]['alamat']     = $alamat;
            }

            // Update session nama & profil aktif
            $_SESSION['user_nama'] = $nama;
            $_SESSION['profil'] = [
                'no_hp'      => $no_hp,
                'nama_usaha' => $nama_usaha,
                'kategori'   => $kategori,
                'alamat'     => $alamat
            ];

            // Refresh data akun
            $akun = $_SESSION['users'][$usernameLogin] ?? [];

            $message = 'Profil dan informasi usaha berhasil diperbarui!';
            $messageType = 'success';
        }
    }
}

// Ambil data terbaru (prioritas data user registrasi, fallback profil)
$nama       = $akun['nama'] ?? ($_SESSION['user_nama'] ?? '');
$username   = $akun['username'] ?? $usernameLogin;
$email      = $akun['email'] ?? '';
$no_hp      = !empty($akun['no_hp']) ? $akun['no_hp'] : ($_SESSION['profil']['no_hp'] ?? '');
$nama_usaha = !empty($akun['nama_usaha']) ? $akun['nama_usaha'] : ($_SESSION['profil']['nama_usaha'] ?? '');
$kategori   = !empty($akun['kategori']) ? $akun['kategori'] : ($_SESSION['profil']['kategori'] ?? '');
$alamat     = !empty($akun['alamat']) ? $akun['alamat'] : ($_SESSION['profil']['alamat'] ?? '');

$initial = !empty($nama)
    ? strtoupper(mb_substr($nama, 0, 1))
    : '?';

$kategoriOptions = [
    '',
    'Makanan & Minuman',
    'Fashion',
    'Kerajinan',
    'Jasa',
    'Kecantikan',
    'Elektronik',
    'Lainnya'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <header class="bg-white border-b border-gray-100 px-4 sm:px-6 py-4">
            <h2 class="font-bold text-lg sm:text-xl text-navy-900">Profil Saya</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola informasi akun pengguna dan identitas usaha Anda.</p>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 w-full">
            <div class="w-full bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <!-- Profile Top Banner -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-4 px-4 sm:px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-navy-50/50 to-white">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-navy-800 to-navy-950
                                flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-md">
                        <?= $initial ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-base sm:text-lg text-navy-900 truncate">
                            <?= $nama ? htmlspecialchars($nama) : 'Profil Pemilik' ?>
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5 truncate">
                            @<?= htmlspecialchars($username) ?> • <?= htmlspecialchars($email ?: 'Belum ada email') ?>
                        </p>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-navy-100 text-navy-800 text-xs font-semibold rounded-md">
                                Pemilik Usaha UMKM
                            </span>
                            <?php if (!empty($nama_usaha)): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md">
                                <?= htmlspecialchars($nama_usaha) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($kategori)): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-medium rounded-md">
                                <?= htmlspecialchars($kategori) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="mx-4 sm:mx-6 mt-4 px-4 py-3 rounded-xl text-xs sm:text-sm font-medium
                    <?= $messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-600 border border-red-200' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="profil.php" id="profileForm" class="px-4 sm:px-6 py-5 space-y-6">
                    <input type="hidden" name="action" value="save">

                    <!-- Informasi Akun -->
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <h3 class="text-sm font-bold text-navy-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-navy-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Informasi Akun Pengguna
                            </h3>
                            <span class="text-[11px] text-gray-400">Klik kolom untuk mengubah</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="nama" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap"
                                    value="<?= htmlspecialchars($nama) ?>" class="<?= $inputClass ?>" required>
                            </div>
                            <div>
                                <label for="username" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Username <span class="text-gray-400 font-normal">(ID Akun)</span>
                                </label>
                                <input type="text" id="username" name="username"
                                    value="<?= htmlspecialchars($username) ?>" readonly
                                    class="<?= $inputClass ?> bg-gray-100 text-gray-500 cursor-not-allowed">
                                <p class="text-[11px] text-gray-400 mt-1">Username digunakan sebagai tanda pengenal login tetap.</p>
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Alamat Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" placeholder="Masukkan email aktif"
                                    value="<?= htmlspecialchars($email) ?>" class="<?= $inputClass ?>" required>
                            </div>
                            <div>
                                <label for="no_hp" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Nomor WhatsApp / HP
                                </label>
                                <input type="tel" id="no_hp" name="no_hp" placeholder="Contoh: 08123456789"
                                    value="<?= htmlspecialchars($no_hp) ?>" class="<?= $inputClass ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Usaha -->
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <h3 class="text-sm font-bold text-navy-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-navy-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Informasi Usaha UMKM
                            </h3>
                            <span class="text-[11px] text-gray-400">Data profil operasional bisnis</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="nama_usaha" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Nama Usaha / Toko
                                </label>
                                <input type="text" id="nama_usaha" name="nama_usaha" placeholder="Masukkan nama usaha / toko"
                                    value="<?= htmlspecialchars($nama_usaha) ?>" class="<?= $inputClass ?>">
                            </div>
                            <div>
                                <label for="kategori" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Kategori Bidang Usaha
                                </label>
                                <select id="kategori" name="kategori" class="<?= $inputClass ?> cursor-pointer">
                                    <?php foreach ($kategoriOptions as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>" <?= $kategori === $opt ? 'selected' : '' ?>>
                                        <?= $opt ?: '-- Pilih kategori usaha --' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="alamat" class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Alamat Lengkap Usaha
                                </label>
                                <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lokasi usaha"
                                    class="<?= $inputClass ?> resize-y"><?= htmlspecialchars($alamat) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="submit" id="saveButton"
                            class="w-full sm:w-auto px-6 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition shadow-sm hover:shadow">
                            Simpan Perubahan
                        </button>
                        <button type="reset" id="resetButton"
                            class="w-full sm:w-auto text-center px-6 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                            Reset Formulir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="assets/js/profil.js"></script>
</body>
</html>
