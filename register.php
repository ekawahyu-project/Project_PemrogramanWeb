<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

// Inisialisasi toko user yang sama dengan login.php
if (!isset($_SESSION['users'])) {
    $_SESSION['users']['admin'] = ['nama' => 'Administrator', 'email' => 'admin@gmail.com', 'password' => 'admin123'];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $nama  = trim($_POST['nama']     ?? '');
    $uname = trim($_POST['username'] ?? '');
    $email = trim($_POST['email']    ?? '');
    $pw    = $_POST['password']       ?? '';
    $pw2   = $_POST['password2']      ?? '';

    if (!$nama || !$uname || !$email || !$pw) {
        $error = 'Semua field formulir wajib diisi.';
    } elseif ($pw !== $pw2) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } elseif (strlen($pw) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif (isset($_SESSION['users'][$uname])) {
        $error = 'Username sudah terdaftar.';
    } else {
        // Cek duplikasi email
        foreach ($_SESSION['users'] as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $error = 'Email sudah terdaftar.'; break;
            }
        }
        if (!$error) {
            $_SESSION['users'][$uname] = ['nama' => $nama, 'email' => $email, 'password' => $pw];
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpetBizz | Daftar Akun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="min-h-screen bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 flex items-center justify-center p-3.5 sm:p-6 font-sans antialiased text-gray-800">
    <div class="w-full max-w-[440px] bg-white rounded-2xl shadow-2xl p-5 sm:p-8 mx-auto my-4 sm:my-8">
        <div class="text-center sm:text-left mb-6">
            <span class="inline-block px-3 py-1 bg-navy-100 text-navy-800 text-xs font-bold rounded-lg mb-2 tracking-wide uppercase">AlpetBizz</span>
            <h1 class="text-xl sm:text-2xl font-bold text-navy-900 tracking-tight">Buat Akun Baru</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Daftarkan usaha Anda dan mulai kelola dengan mudah.</p>
        </div>

        <?php if ($error): ?>
        <div class="text-xs sm:text-sm text-red-600 bg-red-50 border border-red-200 px-3.5 py-2.5 rounded-xl mb-4 font-medium"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-3.5">
            <input type="hidden" name="action" value="register">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama" required
                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                    placeholder="Masukkan nama lengkap pemilik"
                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Username</label>
                <input type="text" name="username" required
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Pilih nama pengguna (username)"
                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat Email</label>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="Contoh: nama@domain.com"
                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kata Sandi</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ulangi Kata Sandi</label>
                <input type="password" name="password2" required placeholder="Konfirmasi kata sandi"
                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div class="pt-2">
                <button type="submit"
                    class="w-full py-3 bg-navy-800 hover:bg-navy-950 text-white rounded-xl text-sm font-semibold transition shadow-md hover:shadow-lg">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <p class="text-center text-xs text-gray-500 mt-6 pt-4 border-t border-gray-100">
            Sudah memiliki akun?
            <a href="login.php" class="text-navy-700 font-bold hover:underline">Masuk di sini</a>
        </p>
    </div>
</body>
</html>
