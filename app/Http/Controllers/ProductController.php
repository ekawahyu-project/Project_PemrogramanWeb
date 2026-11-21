<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan halaman data produk milik pengguna yang sedang login.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $currentPage = 'produk';
        $kategoriOpt = Product::CATEGORIES;
        $satuanOpt = Product::UNITS;
        $produkList = $user->products()->orderBy('id')->get();

        $editId = $request->query('edit');
        $editPrd = is_numeric($editId) ? $user->products()->find((int) $editId) : null;

        $errorsList = session('errors_list', []);
        $notif = session('flash');

        if (! empty($errorsList)) {
            $daftar = implode('', array_map(
                fn (string $e): string => '<li style="margin-bottom:4px">• '.e($e).'</li>',
                $errorsList
            ));

            $notif = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'html' => '<ul style="text-align:left;list-style:none;padding:0;font-size:14px">'.$daftar.'</ul>',
                'confirmButtonText' => 'Coba lagi',
            ];
        }

        return view('produk', compact(
            'currentPage',
            'kategoriOpt',
            'satuanOpt',
            'produkList',
            'editId',
            'editPrd',
            'errorsList',
            'notif'
        ));
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
     * Tambah atau perbarui data produk milik pengguna aktif.
     */
    protected function storeOrUpdate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $action = (string) $request->input('action');
        $isUpdate = $action === 'update';
        $id = $isUpdate ? $request->input('id') : null;
        $product = $isUpdate && is_numeric($id) ? $user->products()->find((int) $id) : null;
        $maxAngka = 1000000000;

        $nama = trim((string) $request->input('nama', ''));

        $validator = Validator::make([
            'nama' => $nama,
            'kategori' => $request->input('kategori'),
            'satuan' => $request->input('satuan'),
            'harga_beli' => $request->input('harga_beli'),
            'harga_jual' => $request->input('harga_jual'),
            'stok' => $request->input('stok'),
            'stok_min' => $request->input('stok_min'),
        ], [
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'nama')
                    ->where('user_id', $user->id)
                    ->ignore($product?->id),
            ],
            'kategori' => ['required', Rule::in(Product::CATEGORIES)],
            'satuan' => ['required', Rule::in(Product::UNITS)],
            'harga_beli' => ['required', 'integer', 'min:0', "max:{$maxAngka}"],
            'harga_jual' => ['required', 'integer', 'min:1', "max:{$maxAngka}"],
            'stok' => ['required', 'integer', 'min:0', "max:{$maxAngka}"],
            'stok_min' => ['required', 'integer', 'min:0', "max:{$maxAngka}"],
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'nama.max' => 'Nama produk maksimal 100 karakter.',
            'nama.unique' => 'Nama produk sudah digunakan.',
            'kategori.required' => 'Kategori tidak valid.',
            'kategori.in' => 'Kategori tidak valid.',
            'satuan.required' => 'Satuan tidak valid.',
            'satuan.in' => 'Satuan tidak valid.',
            'harga_beli.required' => 'Harga beli harus berupa angka bulat 0 atau lebih.',
            'harga_beli.integer' => 'Harga beli harus berupa angka bulat 0 atau lebih.',
            'harga_beli.min' => 'Harga beli harus berupa angka bulat 0 atau lebih.',
            'harga_beli.max' => 'Harga terlalu besar.',
            'harga_jual.required' => 'Harga jual harus berupa angka bulat lebih dari 0.',
            'harga_jual.integer' => 'Harga jual harus berupa angka bulat lebih dari 0.',
            'harga_jual.min' => 'Harga jual harus berupa angka bulat lebih dari 0.',
            'harga_jual.max' => 'Harga terlalu besar.',
            'stok.required' => 'Stok harus berupa angka bulat 0 atau lebih.',
            'stok.integer' => 'Stok harus berupa angka bulat 0 atau lebih.',
            'stok.min' => 'Stok harus berupa angka bulat 0 atau lebih.',
            'stok.max' => 'Nilai stok terlalu besar.',
            'stok_min.required' => 'Stok minimum harus berupa angka bulat 0 atau lebih.',
            'stok_min.integer' => 'Stok minimum harus berupa angka bulat 0 atau lebih.',
            'stok_min.min' => 'Stok minimum harus berupa angka bulat 0 atau lebih.',
            'stok_min.max' => 'Nilai stok terlalu besar.',
        ]);

        $validator->after(function (ValidationValidator $validator) use ($isUpdate, $product, $request): void {
            if ($isUpdate && ! $product) {
                $validator->errors()->add('id', 'Produk yang diedit tidak ditemukan.');
            }

            $beli = filter_var($request->input('harga_beli'), FILTER_VALIDATE_INT);
            $jual = filter_var($request->input('harga_jual'), FILTER_VALIDATE_INT);

            if ($beli !== false && $jual !== false && $jual < $beli) {
                $validator->errors()->add('harga_jual', 'Harga jual tidak boleh lebih rendah dari harga beli.');
            }
        });

        if ($validator->fails()) {
            return redirect('/produk'.($isUpdate ? '?edit='.$id : ''))
                ->withInput()
                ->with('errors_list', $validator->errors()->all());
        }

        $validated = $validator->validated();
        $data = [
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'harga_beli' => (int) $validated['harga_beli'],
            'harga_jual' => (int) $validated['harga_jual'],
            'satuan' => $validated['satuan'],
            'stok' => (int) $validated['stok'],
            'stok_min' => (int) $validated['stok_min'],
        ];

        if ($isUpdate && $product) {
            $product->update($data);
            $pesan = 'Produk berhasil diperbarui.';
        } else {
            $user->products()->create($data);
            $pesan = 'Produk berhasil ditambahkan.';
        }

        return redirect('/produk')->with('flash', [
            'icon' => 'success',
            'title' => 'Sukses!',
            'text' => $pesan,
            'confirmButtonText' => 'OK',
        ]);
    }

    /**
     * Hapus produk milik pengguna aktif.
     */
    protected function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $id = $request->input('id');
        $product = is_numeric($id) ? $user->products()->find((int) $id) : null;

        if (! $product) {
            return redirect('/produk')->with('flash', [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Produk tidak ditemukan atau Anda tidak memiliki akses.',
                'confirmButtonText' => 'OK',
            ]);
        }

        $product->delete();

        return redirect('/produk')->with('flash', [
            'icon' => 'success',
            'title' => 'Terhapus!',
            'text' => 'Produk berhasil dihapus.',
            'confirmButtonText' => 'OK',
        ]);
    }
}
