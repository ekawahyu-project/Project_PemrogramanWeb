<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan halaman data produk.
     */
    public function index(): View
    {
        return view('produk');
    }

    /**
     * Tangani request CRUD produk.
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = $request->input('action', '');

        if ($action === 'tambah' || $action === 'update') {
            return $this->storeOrUpdate($request);
        }

        if ($action === 'hapus') {
            return $this->destroy($request);
        }

        return redirect('/produk');
    }

    /**
     * Tambah atau perbarui data produk.
     */
    protected function storeOrUpdate(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $id = $action === 'update' ? $request->input('id') : null;

        $kategoriOpt = ['Makanan', 'Minuman', 'Fashion', 'Kecantikan', 'Bahan Baku', 'Kesehatan & Herbal', 'Perlengkapan Rumah Tangga', 'Lainnya'];
        $satuanOpt = ['pcs', 'pack', 'kg', 'gram', 'liter', 'ml', 'box', 'lusin', 'meter', 'lembar'];
        $maxAngka = 1000000000;

        $nama = trim((string) $request->input('nama', ''));
        $kategori = (string) $request->input('kategori', '');
        $satuan = (string) $request->input('satuan', '');
        $beli = filter_var($request->input('harga_beli'), FILTER_VALIDATE_INT);
        $jual = filter_var($request->input('harga_jual'), FILTER_VALIDATE_INT);
        $stok = filter_var($request->input('stok'), FILTER_VALIDATE_INT);
        $stokMin = filter_var($request->input('stok_min'), FILTER_VALIDATE_INT);

        $errors = [];
        $produkList = session('produk', []);

        if ($nama === '') {
            $errors[] = 'Nama produk wajib diisi.';
        } elseif (mb_strlen($nama) > 100) {
            $errors[] = 'Nama produk maksimal 100 karakter.';
        } else {
            foreach ($produkList as $p) {
                if (($p['id'] ?? '') !== $id && mb_strtolower((string) ($p['nama'] ?? '')) === mb_strtolower($nama)) {
                    $errors[] = 'Nama produk sudah digunakan.';
                    break;
                }
            }
        }

        if (! in_array($kategori, $kategoriOpt, true)) {
            $errors[] = 'Kategori tidak valid.';
        }
        if (! in_array($satuan, $satuanOpt, true)) {
            $errors[] = 'Satuan tidak valid.';
        }

        if ($beli === false || $beli < 0) {
            $errors[] = 'Harga beli harus berupa angka bulat 0 atau lebih.';
        }
        if ($jual === false || $jual <= 0) {
            $errors[] = 'Harga jual harus berupa angka bulat lebih dari 0.';
        }
        if ($beli !== false && $jual !== false && $jual < $beli) {
            $errors[] = 'Harga jual tidak boleh lebih rendah dari harga beli.';
        }
        if (($beli !== false && $beli > $maxAngka) || ($jual !== false && $jual > $maxAngka)) {
            $errors[] = 'Harga terlalu besar.';
        }

        if ($stok === false || $stok < 0) {
            $errors[] = 'Stok harus berupa angka bulat 0 atau lebih.';
        }
        if ($stokMin === false || $stokMin < 0) {
            $errors[] = 'Stok minimum harus berupa angka bulat 0 atau lebih.';
        }
        if (($stok !== false && $stok > $maxAngka) || ($stokMin !== false && $stokMin > $maxAngka)) {
            $errors[] = 'Nilai stok terlalu besar.';
        }

        if ($action === 'update' && getProdukById((string) $id) === null) {
            $errors[] = 'Produk yang diedit tidak ditemukan.';
        }

        if (! empty($errors)) {
            return redirect('/produk'.($action === 'update' ? '?edit='.$id : ''))
                ->withInput()
                ->with('errors_list', $errors);
        }

        $data = [
            'nama' => $nama,
            'kategori' => $kategori,
            'harga_beli' => (int) $beli,
            'harga_jual' => (int) $jual,
            'satuan' => $satuan,
            'stok' => (int) $stok,
            'stok_min' => (int) $stokMin,
        ];

        if ($action === 'tambah') {
            $produkList[] = ['id' => uniqid('p')] + $data;
            $pesan = 'Produk berhasil ditambahkan.';
        } else {
            foreach ($produkList as $i => $p) {
                if (($p['id'] ?? '') === $id) {
                    $produkList[$i] = ['id' => $id] + $data;
                    break;
                }
            }
            $pesan = 'Produk berhasil diperbarui.';
        }

        session(['produk' => $produkList]);
        simpanProduk();

        return redirect('/produk')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => $pesan,
            'confirmButtonText' => 'OK',
        ]);
    }

    /**
     * Hapus produk.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        $id = $request->input('id', '');
        $produkList = session('produk', []);
        $transaksiList = session('transaksi', []);

        $produkList = array_values(array_filter($produkList, fn ($p) => ($p['id'] ?? '') !== $id));

        foreach ($transaksiList as &$t) {
            if (($t['produk_id'] ?? '') === $id) {
                $t['produk_id'] = null;
            }
        }
        unset($t);

        session([
            'produk' => $produkList,
            'transaksi' => $transaksiList,
        ]);
        simpanProduk();

        return redirect('/produk')->with('flash', [
            'icon' => 'success',
            'title' => 'Terhapus!',
            'text' => 'Produk berhasil dihapus.',
            'confirmButtonText' => 'OK',
        ]);
    }
}
