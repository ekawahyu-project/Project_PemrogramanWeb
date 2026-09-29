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
        $error = 'Semua field wajib diisi.';
    } elseif ($pw !== $pw2) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($pw) < 6) {
        $error = 'Password minimal 6 karakter.';
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
    <title>UMKM Manager — Daftar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="min-h-screen bg-gradient-to-br from-navy-950 to-navy-800 grid place-items-center p-6 font-sans">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8">
        <h1 class="text-xl font-bold text-navy-900 mb-1">Buat akun baru</h1>
        <p class="text-sm text-gray-500 mb-6">Daftarkan usaha Anda di UMKM Manager.</p>

        <?php if ($error): ?>
        <p class="text-sm text-red-600 bg-red-50 px-3 py-2.5 rounded-lg mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-4">
            <input type="hidden" name="action" value="register">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama" required
                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                    placeholder="Masukkan nama lengkap"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Username</label>
                <input type="text" name="username" required
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Masukkan username"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="Masukkan email"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password2" required placeholder="Ulangi password"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                Daftar
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-5">
            Sudah punya akun?
            <a href="login.php" class="text-navy-700 font-semibold hover:underline">Masuk</a>
        </p>
    </div>
</body>
</html>
