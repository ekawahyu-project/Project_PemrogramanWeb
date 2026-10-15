<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard utama.
     */
    public function index(): View
    {
        $currentPage = 'dashboard';
        $user = session('user_nama', session('user', 'Administrator'));
        $transaksi = session('transaksi', []);
        $produk = session('produk', []);

        $pemasukan = 0;
        $pengeluaran = 0;
        foreach ($transaksi as $t) {
            if (($t['jenis'] ?? '') === 'Pemasukan') {
                $pemasukan += (int) ($t['jumlah'] ?? 0);
            } else {
                $pengeluaran += (int) ($t['jumlah'] ?? 0);
            }
        }

        $laba = $pemasukan - $pengeluaran;
        $jmlStok = count($produk);
        $lowStock = count(array_filter($produk, fn ($p) => ($p['stok'] ?? 0) <= ($p['stok_min'] ?? 0)));

        $transaksiPemasukanCount = count(array_filter($transaksi, fn ($t) => ($t['jenis'] ?? '') === 'Pemasukan'));
        $transaksiPengeluaranCount = count(array_filter($transaksi, fn ($t) => ($t['jenis'] ?? '') === 'Pengeluaran'));

        $stats = [
            [
                'label' => 'Total Pemasukan',
                'value' => 'Rp '.number_format($pemasukan, 0, ',', '.'),
                'sub' => $transaksiPemasukanCount.' transaksi',
                'cardClass' => 'bg-emerald-50/70 border border-emerald-100/90',
                'labelClass' => 'text-emerald-900/70',
                'valueClass' => 'text-emerald-700',
                'subClass' => 'text-emerald-700/70 border-t border-emerald-100',
            ],
            [
                'label' => 'Total Pengeluaran',
                'value' => 'Rp '.number_format($pengeluaran, 0, ',', '.'),
                'sub' => $transaksiPengeluaranCount.' transaksi',
                'cardClass' => 'bg-rose-50/70 border border-rose-100/90',
                'labelClass' => 'text-rose-900/70',
                'valueClass' => 'text-rose-700',
                'subClass' => 'text-rose-700/70 border-t border-rose-100',
            ],
            [
                'label' => 'Produk',
                'value' => $jmlStok.' item',
                'sub' => $lowStock > 0 ? $lowStock.' stok menipis' : 'Semua aman',
                'cardClass' => 'bg-sky-50/70 border border-sky-100/90',
                'labelClass' => 'text-sky-900/70',
                'valueClass' => 'text-sky-700',
                'subClass' => 'text-sky-700/70 border-t border-sky-100',
            ],
            [
                'label' => 'Laba Bersih',
                'value' => 'Rp '.number_format(abs($laba), 0, ',', '.'),
                'sub' => $laba >= 0 ? 'Surplus (Untung)' : 'Defisit (Rugi)',
                'cardClass' => $laba >= 0 ? 'bg-indigo-50/70 border border-indigo-100/90' : 'bg-rose-50/70 border border-rose-100/90',
                'labelClass' => $laba >= 0 ? 'text-indigo-900/70' : 'text-rose-900/70',
                'valueClass' => $laba >= 0 ? 'text-indigo-700' : 'text-rose-700',
                'subClass' => $laba >= 0 ? 'text-indigo-700/70 border-t border-indigo-100' : 'text-rose-700/70 border-t border-rose-100',
            ],
        ];

        $transaksiTerbaru = array_slice(array_reverse($transaksi), 0, 5);

        return view('dashboard', compact(
            'currentPage',
            'user',
            'stats',
            'lowStock',
            'transaksiTerbaru'
        ));
    }
}
