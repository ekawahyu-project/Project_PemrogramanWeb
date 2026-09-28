<?php
session_start();

$message = '';
$messageType = '';

if (!isset($_SESSION['profil'])) {
    $_SESSION['profil'] = [
        'nama' => '',
        'username' => '',
        'email' => '',
        'no_hp' => '',
        'nama_usaha' => '',
        'kategori' => '',
        'alamat' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save') {
        $_SESSION['profil'] = [
            'nama' => trim($_POST['nama'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
            'nama_usaha' => trim($_POST['nama_usaha'] ?? ''),
            'kategori' => $_POST['kategori'] ?? '',
            'alamat' => trim($_POST['alamat'] ?? '')
        ];

        $message = 'Profil berhasil disimpan.';
        $messageType = 'success';
    }
}

$profil = $_SESSION['profil'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - UMKM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="index.css">
</head>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1 style="color: white; font-size: 32px;">Kelola Informasi Akun dan Data Usaha Anda.</h1>
        </div>

        <div class="profile-card">
            <div class="profile-top">
                <div class="profile-photo">
                    <span>
                        <?= !empty($profil['nama']) ? htmlspecialchars(strtoupper(substr($profil['nama'], 0, 1))) : '' ?>
                    </span>
                </div>

                <div class="profile-info">
                    <h2>
                        <?= !empty($profil['nama']) ? htmlspecialchars($profil['nama']) : 'Profil Pemilik' ?>
                    </h2>

                    <p>
                        <?= !empty($profil['username']) ? '@' . htmlspecialchars($profil['username']) : 'Lengkapi informasi akun Anda' ?>
                    </p>

                    <span class="role">Pemilik UMKM</span>
                </div>

                <button
                    type="button"
                    class="edit-button"
                    id="editButton"
                >
                    Edit Profil
                </button>
            </div>

            <div class="divider"></div>

            <?php if (isset($message)): ?>
                <div class="status-message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="profileForm">
                <input type="hidden" name="action" value="save">

                <div class="section">
                    <h3>Informasi Akun</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                value="<?= htmlspecialchars($profil['nama']) ?>"
                                placeholder="Masukkan nama lengkap"
                                disabled
                            >
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars($profil['username']) ?>"
                                placeholder="Masukkan username"
                                disabled
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($profil['email']) ?>"
                                placeholder="Masukkan email"
                                disabled
                            >
                        </div>

                        <div class="form-group">
                            <label for="no_hp">Nomor WhatsApp</label>
                            <input
                                type="tel"
                                id="no_hp"
                                name="no_hp"
                                value="<?= htmlspecialchars($profil['no_hp']) ?>"
                                placeholder="Masukkan nomor WhatsApp"
                                disabled
                            >
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3>Informasi Usaha</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_usaha">Nama Usaha</label>
                            <input
                                type="text"
                                id="nama_usaha"
                                name="nama_usaha"
                                value="<?= htmlspecialchars($profil['nama_usaha']) ?>"
                                placeholder="Masukkan nama usaha"
                                disabled
                            >
                        </div>

                        <div class="form-group">
                            <label for="kategori">Kategori Usaha</label>
                            <select
                                id="kategori"
                                name="kategori"
                                disabled
                            >
                                <option value="">Pilih kategori usaha</option>

                                <option
                                    value="Makanan & Minuman"
                                    <?= $profil['kategori'] === 'Makanan & Minuman' ? 'selected' : '' ?>
                                >
                                    Makanan & Minuman
                                </option>

                                <option
                                    value="Fashion"
                                    <?= $profil['kategori'] === 'Fashion' ? 'selected' : '' ?>
                                >
                                    Fashion
                                </option>

                                <option
                                    value="Kerajinan"
                                    <?= $profil['kategori'] === 'Kerajinan' ? 'selected' : '' ?>
                                >
                                    Kerajinan
                                </option>

                                <option
                                    value="Jasa"
                                    <?= $profil['kategori'] === 'Jasa' ? 'selected' : '' ?>
                                >
                                    Jasa
                                </option>

                                <option
                                    value="Kecantikan"
                                    <?= $profil['kategori'] === 'Kecantikan' ? 'selected' : '' ?>
                                >
                                    Kecantikan
                                </option>

                                <option
                                    value="Elektronik"
                                    <?= $profil['kategori'] === 'Elektronik' ? 'selected' : '' ?>
                                >
                                    Elektronik
                                </option>

                                <option
                                    value="Lainnya"
                                    <?= $profil['kategori'] === 'Lainnya' ? 'selected' : '' ?>
                                >
                                    Lainnya
                                </option>
                            </select>
                        </div>

                        <div class="form-group full">
                            <label for="alamat">Alamat Usaha</label>
                            <textarea
                                id="alamat"
                                name="alamat"
                                rows="3"
                                placeholder="Masukkan alamat usaha"
                                disabled
                            ><?= htmlspecialchars($profil['alamat']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="account-actions">
                    <button
                        type="submit"
                        class="save-button"
                        id="saveButton"
                        disabled
                    >
                        Simpan Perubahan
                    </button>

                    <button
                        type="button"
                        class="cancel-button"
                        id="cancelButton"
                        disabled
                    >
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="index.js"></script>
</body>
</html>