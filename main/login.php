<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

// Inisialisasi toko user dengan akun default lengkap
if (!isset($_SESSION['users'])) {
    $_SESSION['users']['admin'] = [
        'nama'       => 'Administrator',
        'username'   => 'admin',
        'email'      => 'admin@gmail.com',
        'password'   => 'admin123',
        'no_hp'      => '08123456789',
        'nama_usaha' => 'AlpetBizz Store',
        'kategori'   => 'Makanan & Minuman',
        'alamat'     => 'Malang',
    ];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['username'] ?? '');
    $pw = $_POST['password'] ?? '';

    foreach ($_SESSION['users'] as $uname => $data) {
        if (($uname === $id || strtolower($data['email'] ?? '') === strtolower($id)) && ($data['password'] ?? '') === $pw) {
            $_SESSION['user']      = $uname;
            $_SESSION['user_nama'] = $data['nama'] ?? 'User';
            $_SESSION['profil']    = [
                'no_hp'      => $data['no_hp']      ?? '',
                'nama_usaha' => $data['nama_usaha'] ?? '',
                'kategori'   => $data['kategori']   ?? '',
                'alamat'     => $data['alamat']     ?? '',
            ];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Email / Username atau kata sandi salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpetBizz | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{navy:{'100':'#dbe8ff','700':'#1e4080','800':'#162e5e','900':'#0e1f42','950':'#080f21'}}}}}</script>
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body class="min-h-screen bg-cover bg-center bg-no-repeat relative flex items-center justify-center p-3.5 sm:p-6 font-sans antialiased text-gray-800"
      style="background-image: url('img/background.jpg');">

    <!-- Overlay Gelap Elegan untuk meningkatkan keterbacaan -->
    <div class="absolute inset-0 bg-navy-950/30"></div>

    <!-- Container Card Login -->
    <div class="relative z-10 w-full max-w-[420px] bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-6 sm:p-8 mx-auto border border-white/20">
        <div class="text-center sm:text-left mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-navy-900 tracking-tight">Masuk ke Akun</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Kelola keuangan dan stok usaha Anda di satu tempat.</p>
        </div>

        <?php if ($error): ?>
        <div class="text-xs sm:text-sm text-red-600 bg-red-50 border border-red-200 px-3.5 py-2.5 rounded-xl mb-4 font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email atau Username</label>
                <input type="text" name="username" required autocomplete="off"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Masukkan email / username"
                    class="w-full px-3.5 py-2.5 sm:py-3 border border-gray-200 rounded-xl text-sm outline-none
                           focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition bg-white/90">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kata Sandi (Password)</label>
                <div class="relative">
                    <input type="password" id="loginPassword" name="password" required placeholder="Masukkan kata sandi"
                        class="w-full pl-3.5 pr-11 py-2.5 sm:py-3 border border-gray-200 rounded-xl text-sm outline-none
                               focus:border-navy-700 focus:ring-2 focus:ring-navy-100 transition bg-white/90">
                    <button type="button" onclick="togglePasswordVisibility('loginPassword', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-navy-800 focus:outline-none transition rounded-lg"
                        title="Tampilkan / Sembunyikan Kata Sandi" aria-label="Toggle Password Visibility">
                        <!-- Ikon Mata Tertutup (Bawaan) -->
                        <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-3 bg-navy-800 hover:bg-navy-950 text-white rounded-xl text-sm font-semibold transition shadow-md hover:shadow-lg">
                Masuk Sekarang
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-6 pt-4 border-t border-gray-100">
            Belum memiliki akun?
            <a href="register.php" class="text-navy-700 font-bold hover:underline">Daftar sekarang</a>
        </p>
    </div>

    <!-- Script Toggle Mata (Show / Hide Password) -->
    <script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        // Ikon mata terbuka saat teks terlihat, ikon mata tercoret saat teks disembunyikan
        if (isPassword) {
            btn.innerHTML = `
                <svg class="w-5 h-5 text-navy-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="around" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            `;
            btn.title = "Sembunyikan Kata Sandi";
        } else {
            btn.innerHTML = `
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                </svg>
            `;
            btn.title = "Tampilkan Kata Sandi";
        }
    }
    </script>
</body>
</html>
