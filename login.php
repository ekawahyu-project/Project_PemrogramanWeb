<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

// Inisialisasi toko user dengan akun default
if (!isset($_SESSION['users'])) {
    $_SESSION['users']['admin'] = ['nama' => 'Administrator', 'email' => 'admin@gmail.com', 'password' => 'admin123'];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['username'] ?? '');
    $pw = $_POST['password'] ?? '';

    foreach ($_SESSION['users'] as $uname => $data) {
        if (($uname === $id || strtolower($data['email']) === strtolower($id)) && $data['password'] === $pw) {
            $_SESSION['user']      = $uname;
            $_SESSION['user_nama'] = $data['nama'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Email / Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMKM Manager — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
</head>
<body class="min-h-screen bg-gradient-to-br from-navy-950 to-navy-800 grid place-items-center p-6 font-sans">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8">
        <h1 class="text-xl font-bold text-navy-900 mb-1">Masuk ke akun Anda</h1>
        <p class="text-sm text-gray-500 mb-6">Kelola keuangan dan stok usaha Anda di satu tempat.</p>

        <?php if ($error): ?>
        <p class="text-sm text-red-600 bg-red-50 px-3 py-2.5 rounded-lg mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email / Username</label>
                <input type="text" name="username" required autocomplete="off"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Masukkan email atau username"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition">
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                Masuk
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-5">
            Belum punya akun?
            <a href="register.php" class="text-navy-700 font-semibold hover:underline">Daftar sekarang</a>
        </p>
    </div>
</body>
</html>
