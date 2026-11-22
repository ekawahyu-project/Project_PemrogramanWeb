<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard utama bergaya Web3Bento tanpa duplikasi metrik.
     * Hanya mengeksekusi 3 query utama (transactions, products, debts) milik pengguna aktif.
     */
    public function index(Request $request): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $currentPage = 'dashboard';
        $user = $authUser->nama ?: 'Administrator';

        // 3 query terisolasi per akun (menggantikan ~10 query terpisah sebelumnya)
        $transactions = $authUser->transactions()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $products = $authUser->products()->get();
        $productsById = $products->keyBy('id');
        $debts = $authUser->debts()->get();

        $incomeTransactions = $transactions->where('jenis', Transaction::TYPE_INCOME);
        $expenseTransactions = $transactions->where('jenis', Transaction::TYPE_EXPENSE);

        $pemasukan = (int) $incomeTransactions->sum('jumlah');
        $pengeluaran = (int) $expenseTransactions->sum('jumlah');
        $transaksiPemasukanCount = $incomeTransactions->count();
        $transaksiPengeluaranCount = $expenseTransactions->count();

        $jmlStok = $products->count();
        $totalUnitStok = (int) $products->sum('stok');
        $lowStock = $products->filter(fn (Product $p): bool => $p->isLowStock())->count();
        $laba = $pemasukan - $pengeluaran;
        $marginPct = $pemasukan > 0 ? round(($laba / $pemasukan) * 100, 1) : 0.0;
        $stockHealthScore = $jmlStok > 0
            ? round((max(0, $jmlStok - $lowStock) / $jmlStok) * 100, 1)
            : 100.0;

        // 1. Data unik untuk Vault Cards: Valuasi Aset Inventaris Gudang
        $nilaiJualStok = (int) $products->sum(fn (Product $p): int => $p->harga_jual * $p->stok);
        $modalStok = (int) $products->sum(fn (Product $p): int => $p->harga_beli * $p->stok);
        $potensiLabaStok = max(0, $nilaiJualStok - $modalStok);

        // 2. Data unik untuk AllocationTile: Kontribusi Omzet per Kategori Produk
        $categoryRevenue = [];
        foreach ($incomeTransactions as $trx) {
            if ($trx->product_id === null) {
                continue;
            }

            $cat = $productsById->get($trx->product_id)?->kategori ?? 'Lainnya';
            $categoryRevenue[$cat] = ($categoryRevenue[$cat] ?? 0) + $trx->jumlah;
        }
        arsort($categoryRevenue);

        if (empty($categoryRevenue)) {
            foreach ($products->groupBy('kategori') as $cat => $items) {
                $categoryRevenue[$cat] = (int) $items->sum(fn (Product $p): int => $p->harga_jual * max($p->stok, 1));
            }
            arsort($categoryRevenue);
        }

        // 3. Data unik untuk Solid Lime Tile: Ringkasan Hutang & Piutang (Kasbon)
        $piutangTotal = (int) $debts->where('jenis', Debt::TYPE_RECEIVABLE)->sum('jumlah');
        $piutangCair = (int) $debts->where('jenis', Debt::TYPE_RECEIVABLE)->sum('dibayar');
        $sisaPiutang = max(0, $piutangTotal - $piutangCair);
        $sisaHutang = (int) $debts->where('jenis', Debt::TYPE_PAYABLE)->sum(fn (Debt $d): int => $d->remainingAmount());
        $activeDebtCount = $debts->where('status', '!=', Debt::STATUS_PAID)->count();
        $kolektibilitasPct = $piutangTotal > 0 ? round(($piutangCair / $piutangTotal) * 100, 1) : 100.0;

        $stats = [
            [
                'label' => 'Total Pemasukan',
                'value' => 'Rp '.number_format($pemasukan, 0, ',', '.'),
                'sub' => $transaksiPemasukanCount.' transaksi',
                'cardClass' => 'bento-tile',
                'labelClass' => 'text-[#8E9882]',
                'valueClass' => 'text-[#C4FF0D]',
                'subClass' => 'text-[#8E9882] border-t border-white/[0.08]',
            ],
            [
                'label' => 'Total Pengeluaran',
                'value' => 'Rp '.number_format($pengeluaran, 0, ',', '.'),
                'sub' => $transaksiPengeluaranCount.' transaksi',
                'cardClass' => 'bento-tile',
                'labelClass' => 'text-[#8E9882]',
                'valueClass' => 'text-[#C084FC]',
                'subClass' => 'text-[#8E9882] border-t border-white/[0.08]',
            ],
            [
                'label' => 'Laba Bersih',
                'value' => 'Rp '.number_format(abs($laba), 0, ',', '.'),
                'sub' => $laba >= 0 ? 'Surplus (Untung)' : 'Defisit (Rugi)',
                'cardClass' => 'bento-tile',
                'labelClass' => 'text-[#8E9882]',
                'valueClass' => $laba >= 0 ? 'text-[#C4FF0D]' : 'text-rose-400',
                'subClass' => 'text-[#8E9882] border-t border-white/[0.08]',
            ],
        ];

        $shortcuts = [
            ['href' => '/transaksi', 'label' => 'Transaksi', 'desc' => 'Catat pemasukan & pengeluaran'],
            ['href' => '/produk', 'label' => 'Produk', 'desc' => 'Kelola katalog data produk'],
            ['href' => '/stok', 'label' => 'Stok', 'desc' => 'Pantau & catat pergerakan barang'],
            ['href' => '/hutang-piutang', 'label' => 'Hutang & Piutang', 'desc' => 'Kelola kasbon pelanggan & supplier'],
            ['href' => '/laporan', 'label' => 'Laporan', 'desc' => 'Grafik analitik & laporan bulanan'],
            ['href' => '/profil', 'label' => 'Profil', 'desc' => 'Informasi akun & profil usaha'],
            ['href' => '/konsultasi-ai', 'label' => 'Konsultasi AI Bisnis', 'desc' => 'Tanya strategi bisnis, stok, dan arus kas langsung ke Gemini AI berbasis data toko Anda'],
        ];

        $transaksiTerbaru = $transactions->take(5)->values();

        return view('dashboard', compact(
            'currentPage',
            'user',
            'stats',
            'shortcuts',
            'lowStock',
            'transaksiTerbaru',
            'pemasukan',
            'pengeluaran',
            'laba',
            'jmlStok',
            'totalUnitStok',
            'marginPct',
            'stockHealthScore',
            'nilaiJualStok',
            'modalStok',
            'potensiLabaStok',
            'categoryRevenue',
            'sisaPiutang',
            'sisaHutang',
            'activeDebtCount',
            'kolektibilitasPct'
        ));
    }
}
