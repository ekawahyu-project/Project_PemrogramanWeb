<?php
session_start();

$VALID_USERNAME = "admin";
$VALID_EMAIL = "admin@gmail.com";
$VALID_PASSWORD = "admin123";

$errorMsg = "";
$errorColor = "#dc2626";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = ($_POST["username"]);
    $password = ($_POST["password"]);

    $isValid = ($identifier === $VALID_USERNAME || strtolower($identifier) === $VALID_EMAIL)
        && $password === $VALID_PASSWORD;

    if ($isValid) {
        $errorColor = "#16a34a";
        $errorMsg = "Login berhasil! Mengalihkan...";
        // contoh: simpan session lalu redirect
        // $_SESSION['user'] = $identifier;
        // header("Location: dashboard.php");
        // exit;
    } else {
        $errorColor = "#dc2626";
        $errorMsg = "Email / Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UMKM Manager — Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="login-card">
    <h1>Masuk ke akun Anda</h1>
    <p class="subtitle">Kelola keuangan dan stok usaha Anda di satu tempat.</p>

    <form id="loginForm" method="POST" action="">
      <label for="username">Email / Username</label>
      <input type="text" id="username" name="username" placeholder="Masukkan email atau username" autocomplete="off"
        required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Masukkan password" required>

      <p id="errorMsg" class="error-msg <?php echo ($errorColor === "#16a34a") ? "success" : "failed" ; ?>">
        <?php echo htmlspecialchars($errorMsg ?? ""); ?>
      </p>
      <button type="submit">Masuk</button>
    </form>
  </div>

</body>

</html>