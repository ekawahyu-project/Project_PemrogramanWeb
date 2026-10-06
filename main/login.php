<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']}}}}</script>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="font-sans antialiased">

<!-- ── Hero: fullscreen scroll-scrub video ─────────────────── -->
<div id="hero-section">

    <video id="hero-video" muted playsinline preload="auto"
        src="https://cdn.21st.dev/assets/mirror/23/234bc821170e75a6b8d2e42a952078f858eb054a51fab2a5baa928a10fdc245d.mp4">
    </video>

    <img id="hero-skyline" alt="" aria-hidden="true"
        src="https://cdn.21st.dev/assets/mirror/97/97fe4402ea1d5d05c2b82befe6dbf73da1d6669b995e6d5ae9f2ebea68b1107a.png">

    <div id="hero-vignette" aria-hidden="true"></div>

    <div id="hero-brand" aria-hidden="true">
        <span>AlpetBizz</span>
    </div>

    <div id="hero-title">
        <span>Selamat Datang di AlpetBizz</span>
    </div>

    <!-- Navbar -->
    <nav id="site-nav" aria-label="Site navigation">
        <span class="nav-brand">AlpetBizz</span>
        <span class="nav-sep" aria-hidden="true"></span>
        <span class="nav-sub">Manajemen Usaha</span>
        <button id="nav-login-btn" onclick="openLogin()">
            Masuk
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </button>
    </nav>

    <!-- Scroll hint -->
    <div id="hero-scroll-hint">
        <span>SCROLL</span>
        <svg width="14" height="18" viewBox="0 0 14 18" aria-hidden="true">
            <path d="M7 1 L7 17 M2 12 L7 17 L12 12"
                stroke="currentColor" stroke-width="1.5" fill="none"
                stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <!-- Progress bar -->
    <div id="hero-progress-track" aria-hidden="true">
        <div id="hero-progress-bar"></div>
    </div>

</div>


<!-- ── Login panel: toggled by navbar button ───────────────── -->
<div id="login-panel" aria-modal="true" role="dialog" aria-label="Form Login">
    <div id="login-panel-card">

        <div class="panel-header">
            <div>
                <h1 class="panel-title">Masuk ke Akun</h1>
                <p class="panel-sub">Kelola keuangan dan stok usaha Anda di satu tempat.</p>
            </div>
            <button class="panel-close" onclick="closeLogin()" aria-label="Tutup form login">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <?php if ($error): ?>
        <div class="login-error">
            <svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="login-form">

            <div>
                <label class="field-label" for="inp-username">Email atau Username</label>
                <input id="inp-username" class="field-input" type="text"
                    name="username" required autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Masukkan email / username">
            </div>

            <div>
                <label class="field-label" for="inp-password">Kata Sandi (Password)</label>
                <div class="field-wrap">
                    <input id="inp-password" class="field-input has-icon" type="password"
                        name="password" required autocomplete="current-password"
                        placeholder="Masukkan kata sandi">
                    <button type="button" class="pw-toggle"
                        onclick="togglePw('inp-password', this)"
                        aria-label="Tampilkan kata sandi">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Masuk Sekarang</button>

        </form>

        <div class="panel-footer">
            <span class="panel-footer-text">Belum memiliki akun? </span>
            <a href="register.php">Daftar sekarang</a>
        </div>

    </div>
</div>

<!-- Pass PHP error flag to JS without mixing PHP into login.js -->
<script>window.LOGIN_HAS_ERROR = <?= $error ? 'true' : 'false' ?>;</script>
<script src="assets/js/login.js"></script>

</body>
</html>
