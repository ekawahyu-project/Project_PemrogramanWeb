<?php

/**
 * Berkas Konfigurasi Koneksi Basis Data Menggunakan PDO
 * Mata Kuliah: Pemrograman Web (CIE61205)
 */
$host = 'localhost';
$db = 'alpetbizz_db';
$port = 3306;
$user = 'root';
$pass = ''; // Default XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Koneksi basis data gagal. Silakan periksa konfigurasi server database Anda.');
}

return $pdo;
