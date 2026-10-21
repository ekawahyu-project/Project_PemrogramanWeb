<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login
Route::get('/', fn () => redirect('/login'));

// Auth routes (tanpa auth middleware)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'handle'])->name('login.handle');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Register (redirect ke /login?tab=register)
Route::get('/register', fn () => redirect('/login?tab=register'))->name('register');

// Protected routes (dengan auth.custom middleware)
Route::middleware('auth.custom')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produk', [ProductController::class, 'index'])->name('produk');
    Route::post('/produk', [ProductController::class, 'handle'])->name('produk.handle');

    Route::get('/stok', [StockController::class, 'index'])->name('stok');
    Route::post('/stok', [StockController::class, 'handle'])->name('stok.handle');

    Route::get('/transaksi', [TransactionController::class, 'index'])->name('transaksi');
    Route::post('/transaksi', [TransactionController::class, 'handle'])->name('transaksi.handle');

    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan');
    Route::post('/laporan', [ReportController::class, 'handle'])->name('laporan.handle');

    Route::get('/rekomendasi', [RecommendationController::class, 'index'])->name('rekomendasi');
    Route::post('/rekomendasi', [RecommendationController::class, 'handle'])->name('rekomendasi.handle');

    Route::get('/profil', [ProfileController::class, 'index'])->name('profil');
    Route::post('/profil', [ProfileController::class, 'handle'])->name('profil.handle');
});
