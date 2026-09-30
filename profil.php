<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/init.php';

$currentPage = 'profil';
$message = '';
$messageType = '';

$usernameLogin = $_SESSION['user'];

$akun = $_SESSION['users'][$usernameLogin] ?? [
    'nama' => '',
    'email' => ''
];

$nama = $akun['nama'];
$username = $usernameLogin;
$email = $akun['email'];

if (!isset($_SESSION['profil'])) {
    $_SESSION['profil'] = [
        'no_hp' => '',
        'nama_usaha' => '',
        'kategori' => '',
        'alamat' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $_SESSION['profil'] = [
        'no_hp'      => trim($_POST['no_hp'] ?? ''),
        'nama_usaha' => trim($_POST['nama_usaha'] ?? ''),
        'kategori'   => $_POST['kategori'] ?? '',
        'alamat'     => trim($_POST['alamat'] ?? '')
    ];

    $message = 'Profil berhasil disimpan.';
    $messageType = 'success';
}

$profil = $_SESSION['profil'];

$initial = !empty($nama)
    ? strtoupper($nama[0])
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
    <?php include 'includes/sidebar.php'; ?>

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <header class="bg-white border-b border-gray-100 px-4 sm:px-6 py-4">
            <h2 class="font-bold text-lg sm:text-xl text-navy-900">Profil Saya</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola informasi akun dan identitas usaha Anda.</p>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 w-full">
            <div class="w-full bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <!-- Profile Top -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-4 px-4 sm:px-6 py-5 border-b border-gray-100">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-navy-800 to-navy-950
                                flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-md">
                        <?= $initial ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-base sm:text-lg text-navy-900 truncate">
                            <?= $nama ? htmlspecialchars($nama) : 'Profil Pemilik' ?>
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                            <?= $username ? '@' . htmlspecialchars($username) : 'Lengkapi informasi akun Anda' ?>
                        </p>
                        <span class="inline-block mt-2 px-2.5 py-0.5 bg-navy-100 text-navy-800 text-xs font-semibold rounded-md">
                            Pemilik Usaha UMKM
                        </span>
                    </div>
                    <button type="button" id="editButton"
                        class="w-full sm:w-auto px-5 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                        Edit Profil
                    </button>
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
                        <h3 class="text-sm font-bold text-navy-900 mb-3.5 flex items-center gap-2">Informasi Akun Pengguna</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap"
                                    value="<?= htmlspecialchars($nama) ?>" disabled class="<?= $inputClass ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Username</label>
                                <input type="text" id="username" name="username" placeholder="Masukkan username"
                                    value="<?= htmlspecialchars($username) ?>" disabled class="<?= $inputClass ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat Email</label>
                                <input type="email" id="email" name="email" placeholder="Masukkan email aktif"
                                    value="<?= htmlspecialchars($email) ?>" disabled class="<?= $inputClass ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nomor WhatsApp / HP</label>
                                <input type="tel" id="no_hp" name="no_hp" placeholder="Contoh: 08123456789"
                                    value="<?= htmlspecialchars($profil['no_hp']) ?>" disabled class="<?= $inputClass ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Usaha -->
                    <div>
                        <h3 class="text-sm font-bold text-navy-900 mb-3.5 flex items-center gap-2">Informasi Usaha UMKM</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Usaha / Toko</label>
                                <input type="text" id="nama_usaha" name="nama_usaha" placeholder="Masukkan nama usaha"
                                    value="<?= htmlspecialchars($profil['nama_usaha']) ?>" disabled class="<?= $inputClass ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori Bidang Usaha</label>
                                <select id="kategori" name="kategori" disabled class="<?= $inputClass ?> cursor-pointer">
                                    <?php foreach ($kategoriOptions as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>"
                                        <?= $profil['kategori'] === $opt ? 'selected' : '' ?>>
                                        <?= $opt ?: '-- Pilih kategori usaha --' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat Lengkap Usaha</label>
                                <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lokasi usaha"
                                    disabled class="<?= $inputClass ?> resize-y"><?= htmlspecialchars($profil['alamat']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="submit" id="saveButton" disabled
                            class="w-full sm:w-auto px-6 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition
                                   disabled:opacity-40 disabled:cursor-not-allowed">
                            Simpan Perubahan
                        </button>
                        <button type="button" id="cancelButton" disabled
                            class="w-full sm:w-auto text-center px-6 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition
                                   disabled:opacity-40 disabled:cursor-not-allowed">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="assets/js/profil.js"></script>
</body>
</html>
