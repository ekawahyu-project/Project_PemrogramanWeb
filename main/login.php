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

$error     = '';
$success   = '';
$activeTab = $_GET['tab'] ?? 'login';

if (isset($_GET['registered'])) {
    $success   = 'Akun berhasil dibuat! Silakan masuk dengan akun baru Anda.';
    $activeTab = 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
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
        $activeTab = 'login';
    } elseif ($action === 'register') {
        $nama       = trim($_POST['nama']        ?? '');
        $uname      = trim($_POST['username']    ?? '');
        $email      = trim($_POST['email']       ?? '');
        $no_hp      = trim($_POST['no_hp']       ?? '');
        $nama_usaha = trim($_POST['nama_usaha']  ?? '');
        $kategori   = trim($_POST['kategori']    ?? '');
        $alamat     = trim($_POST['alamat']      ?? '');
        $pw         = $_POST['password']         ?? '';
        $pw2        = $_POST['password2']        ?? '';

        if (!$nama || !$uname || !$email || !$pw) {
            $error = 'Nama lengkap, username, email, dan kata sandi wajib diisi.';
            $activeTab = 'register';
        } elseif ($pw !== $pw2) {
            $error = 'Konfirmasi kata sandi tidak cocok.';
            $activeTab = 'register';
        } elseif (strlen($pw) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
            $activeTab = 'register';
        } elseif (isset($_SESSION['users'][$uname])) {
            $error = 'Username sudah terdaftar.';
            $activeTab = 'register';
        } else {
            foreach ($_SESSION['users'] as $u) {
                if (strtolower($u['email'] ?? '') === strtolower($email)) {
                    $error = 'Email sudah terdaftar.';
                    $activeTab = 'register';
                    break;
                }
            }
            if (!$error) {
                $_SESSION['users'][$uname] = [
                    'nama'       => $nama,
                    'username'   => $uname,
                    'email'      => $email,
                    'no_hp'      => $no_hp,
                    'nama_usaha' => $nama_usaha,
                    'kategori'   => $kategori,
                    'alamat'     => $alamat,
                    'password'   => $pw,
                ];
                $success = 'Akun berhasil didaftarkan! Silakan masuk.';
                $activeTab = 'login';
            }
        }
    }
}

$kategoriList = ['Makanan & Minuman','Fashion','Kerajinan','Jasa','Kecantikan','Elektronik','Lainnya'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpetBizz | Masuk & Daftar</title>
    <!-- Inter Font Matching Project Standard -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
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
        };
    </script>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="font-sans antialiased">

<!-- ── Hero Section (Controlled by User Scroll) ─────────────── -->
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

    <div id="hero-scroll-hint" aria-hidden="true">
        <span>SCROLL</span>
        <svg width="14" height="18" viewBox="0 0 14 18">
            <path d="M7 1 L7 17 M2 12 L7 17 L12 12"
                stroke="currentColor" stroke-width="1.8" fill="none"
                stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <div id="hero-progress-track" aria-hidden="true">
        <div id="hero-progress-bar"></div>
    </div>
</div>

<!-- ── Floating Top Pill Navbar ─────────────────────────────── -->
<nav id="site-nav" aria-label="Site navigation">
    <span class="nav-brand">AlpetBizz</span>
    <span class="nav-sep" aria-hidden="true"></span>
    <span class="nav-sub">Manajemen Usaha</span>
    <button id="nav-login-btn" onclick="openLogin('login')">
        Masuk
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
    </button>
</nav>

<!-- ── Unified Auth Modal Panel (Login & Register) ──────────── -->
<div id="login-panel" aria-modal="true" role="dialog" aria-label="Form Autentikasi">
    <div id="login-panel-card" class="<?= $activeTab === 'register' ? 'card-wide' : '' ?>">

        <!-- Global Alert Messages -->
        <?php if ($error): ?>
        <div class="login-error">
            <svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="login-success">
            <svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>

        <!-- ════════ TAB 1: FORM LOGIN ════════ -->
        <div id="tab-login-content" style="<?= $activeTab === 'register' ? 'display:none;' : '' ?>">
            <div class="panel-header">
                <div>
                    <h1 class="panel-title">Masuk ke Akun</h1>
                    <p class="panel-sub">Kelola keuangan dan stok usaha Anda di satu tempat.</p>
                </div>
                <button class="panel-close" onclick="closeLogin()" aria-label="Tutup form">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="login.php" class="auth-form">
                <input type="hidden" name="action" value="login">

                <div>
                    <label class="field-label" for="inp-username">Email atau Username</label>
                    <input id="inp-username" class="field-input" type="text"
                        name="username" required autocomplete="username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="Masukkan email atau username">
                </div>

                <div>
                    <label class="field-label" for="inp-password">Kata Sandi</label>
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
                <button type="button" class="tab-link" onclick="switchTab('register')">Daftar sekarang</button>
            </div>
        </div>

        <!-- ════════ TAB 2: FORM REGISTER ════════ -->
        <div id="tab-register-content" style="<?= $activeTab === 'register' ? '' : 'display:none;' ?>">
            <div class="panel-header">
                <div>
                    <h1 class="panel-title">Buat Akun Baru</h1>
                    <p class="panel-sub">Daftarkan akun dan profil usaha UMKM Anda di AlpetBizz.</p>
                </div>
                <button class="panel-close" onclick="closeLogin()" aria-label="Tutup form">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="login.php" class="auth-form">
                <input type="hidden" name="action" value="register">

                <!-- Bagian 1: Akun -->
                <div class="reg-grid">
                    <p class="section-label">Informasi Akun</p>

                    <div>
                        <label class="field-label" for="r-nama">Nama Lengkap *</label>
                        <input id="r-nama" class="field-input" type="text" name="nama" required
                            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                            placeholder="Nama pemilik usaha">
                    </div>

                    <div>
                        <label class="field-label" for="r-username">Username *</label>
                        <input id="r-username" class="field-input" type="text" name="username" required
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            placeholder="Username unik">
                    </div>

                    <div>
                        <label class="field-label" for="r-email">Alamat Email *</label>
                        <input id="r-email" class="field-input" type="email" name="email" required
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            placeholder="nama@domain.com">
                    </div>

                    <div>
                        <label class="field-label" for="r-hp">No. WhatsApp / HP</label>
                        <input id="r-hp" class="field-input" type="tel" name="no_hp"
                            value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"
                            placeholder="08123456789">
                    </div>

                    <div>
                        <label class="field-label" for="r-pw">Kata Sandi *</label>
                        <div class="field-wrap">
                            <input id="r-pw" class="field-input has-icon" type="password" name="password" required
                                placeholder="Min. 6 karakter">
                            <button type="button" class="pw-toggle" onclick="togglePw('r-pw', this)" aria-label="Lihat sandi">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="r-pw2">Ulangi Sandi *</label>
                        <div class="field-wrap">
                            <input id="r-pw2" class="field-input has-icon" type="password" name="password2" required
                                placeholder="Ulangi kata sandi">
                            <button type="button" class="pw-toggle" onclick="togglePw('r-pw2', this)" aria-label="Lihat sandi">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Bagian 2: Usaha -->
                    <p class="section-label">Informasi Usaha UMKM</p>

                    <div>
                        <label class="field-label" for="r-usaha">Nama Usaha / Toko</label>
                        <input id="r-usaha" class="field-input" type="text" name="nama_usaha"
                            value="<?= htmlspecialchars($_POST['nama_usaha'] ?? '') ?>"
                            placeholder="Contoh: Toko Berkah Jaya">
                    </div>

                    <div>
                        <label class="field-label" for="r-kat">Kategori Usaha</label>
                        <select id="r-kat" class="field-input" name="kategori">
                            <option value="">-- Pilih kategori usaha --</option>
                            <?php foreach ($kategoriList as $kat): ?>
                            <option value="<?= htmlspecialchars($kat) ?>"
                                <?= ($_POST['kategori'] ?? '') === $kat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="reg-full">
                        <label class="field-label" for="r-alamat">Alamat Lengkap Usaha</label>
                        <input id="r-alamat" class="field-input" type="text" name="alamat"
                            value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>"
                            placeholder="Alamat fisik toko / usaha">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Daftar Sekarang</button>
            </form>

            <div class="panel-footer">
                <span class="panel-footer-text">Sudah memiliki akun? </span>
                <button type="button" class="tab-link" onclick="switchTab('login')">Masuk di sini</button>
            </div>
        </div>

    </div>
</div>

<!-- Configuration Flags for JS Engine -->
<script>
    window.ACTIVE_TAB = <?= json_encode($activeTab) ?>;
    window.AUTO_OPEN_PANEL = <?= ($error || $success || isset($_GET['tab'])) ? 'true' : 'false' ?>;
</script>
<script src="assets/js/login.js"></script>

</body>
</html>
