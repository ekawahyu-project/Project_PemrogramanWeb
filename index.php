<?php
/**
 * Entry Point — AlpetBizz UMKM Management
 * Mengarahkan pengunjung secara otomatis ke Frontend React (SPA).
 */

if (file_exists(__DIR__ . '/frontend/dist/index.html')) {
    header('Location: frontend/dist/');
    exit;
}

// Fallback jika frontend belum di-build
header('Location: backend/index.php');
exit;
