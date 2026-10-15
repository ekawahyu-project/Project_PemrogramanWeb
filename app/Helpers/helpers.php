<?php

if (! function_exists('getProdukById')) {
    /**
     * Cari produk berdasarkan ID.
     */
    function getProdukById(string $id): ?array
    {
        $produkList = session('produk', []);
        foreach ($produkList as $p) {
            if (($p['id'] ?? '') === $id) {
                return $p;
            }
        }

        return null;
    }
}

if (! function_exists('simpanProduk')) {
    /**
     * Simpan data produk dari session ke file JSON di storage.
     */
    function simpanProduk(): bool
    {
        $dir = storage_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $produk = session('produk', []);

        return file_put_contents(
            storage_path('app/produk.json'),
            json_encode(array_values($produk), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        ) !== false;
    }
}
