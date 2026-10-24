@php
$currentPage = 'produk';
$kategoriOpt = ['Makanan', 'Minuman', 'Fashion', 'Kecantikan', 'Bahan Baku', 'Kesehatan & Herbal', 'Perlengkapan Rumah Tangga', 'Lainnya'];
$satuanOpt   = ['pcs', 'pack', 'kg', 'gram', 'liter', 'ml', 'box', 'lusin', 'meter', 'lembar'];

$editId = request()->query('edit');
$editPrd = $editId ? getProdukById((string) $editId) : null;
$errorsList = session('errors_list', []);
$notif = session('flash', null);

if ($errorsList) {
    $daftar = implode('', array_map(
        fn($e) => '<li style="margin-bottom:4px">• ' . htmlspecialchars($e) . '</li>',
        $errorsList
    ));
    $notif = [
        'icon' => 'error',
        'title' => 'Gagal!',
        'html' => '<ul style="text-align:left;list-style:none;padding:0;font-size:14px">' . $daftar . '</ul>',
        'confirmButtonText' => 'Coba lagi',
    ];
}

// {{ }} di Blade sudah meng-escape, jadi $val tidak perlu htmlspecialchars lagi
$raw = fn(string $k, $default = '') => (string) (old($k, $editPrd[$k] ?? $default));
$val = fn(string $k, $default = '') => $raw($k, $default);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        navy: {
                            '100': '#dbe8ff',
                            '700': '#1e4080',
                            '800': '#162e5e',
                            '900': '#0e1f42',
                            '950': '#080f21'
                        }
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-gray-50 font-sans">
    @include('includes.sidebar')

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <!-- Menghilangkan panah atas-bawah pada input harga -->
        <style>
            .no-spinner::-webkit-outer-spin-button,
            .no-spinner::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            .no-spinner {
                -moz-appearance: textfield;
                appearance: textfield;
            }
            
            .swal-toast-satu-baris { white-space: nowrap; }

        </style>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 max-w-7xl w-full mx-auto">
            <!-- Floating Header Card -->
            <header class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-navy-900 tracking-tight">Manajemen Produk</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Kelola data katalog produk: tambah, update, atau hapus produk.</p>
                </div>
            </header>

            @if ($errorsList)
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm rounded-xl p-4">
                <p class="font-semibold mb-1">Periksa kembali formulir:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errorsList as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Form Tambah / Edit (CREATE & UPDATE) -->
            <div class="bg-white rounded-xl border {{ $editPrd ? 'border-navy-700 ring-1 ring-navy-100' : 'border-gray-100' }} shadow-sm p-4 sm:p-6">
                <h3 class="font-semibold text-navy-900 text-sm sm:text-base mb-4 flex items-center gap-2">
                    {{ $editPrd ? 'Edit Data Produk' : '+ Tambah Produk Baru' }}
                </h3>
                <form method="POST" action="/produk" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @csrf
                    <input type="hidden" name="action" value="{{ $editPrd ? 'update' : 'tambah' }}">
                    @if ($editPrd)
                        <input type="hidden" name="id" value="{{ $editPrd['id'] }}">
                    @endif

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Produk</label>
                        <input type="text" name="nama" required maxlength="100" placeholder="Masukkan nama produk"
                            value="{{ $val('nama') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
                        <select name="kategori" class="{{ $inputClass }}">
                            @foreach ($kategoriOpt as $k)
                                <option value="{{ $k }}" {{ $raw('kategori', 'Lainnya') === $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Satuan</label>
                        <select name="satuan" class="{{ $inputClass }}">
                            @foreach ($satuanOpt as $s)
                                <option value="{{ $s }}" {{ $raw('satuan', 'pcs') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Beli (Rp)</label>
                        <input type="number" name="harga_beli" required min="0" step="1" placeholder="0"
                            value="{{ $val('harga_beli') }}" class="no-spinner {{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" required min="1" step="1" placeholder="0"
                            value="{{ $val('harga_jual') }}" class="no-spinner {{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">{{ $editPrd ? 'Stok Saat Ini' : 'Stok Awal' }}</label>
                        <input type="number" name="stok" required min="0" step="1" placeholder="0"
                            value="{{ $val('stok', 0) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Batas Stok Minimum</label>
                        <input type="number" name="stok_min" required min="0" step="1" placeholder="5"
                            value="{{ $val('stok_min', 5) }}" class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2.5 pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            {{ $editPrd ? 'Simpan Perubahan' : 'Tambah Produk' }}
                        </button>
                        @if ($editPrd)
                            <a href="/produk"
                                class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                                Batal
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Produk (READ & DELETE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm sm:text-base">Daftar Katalog Produk ({{ count(session('produk', [])) }})</h3>
                </div>
                @if (empty(session('produk', [])))
                    <p class="text-center text-gray-400 text-sm py-12">Belum ada produk.</p>
                @else
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-xs sm:text-sm min-w-[650px]">
                            <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Nama Produk</th>
                                    <th class="px-4 py-3 text-left">Kategori</th>
                                    <th class="px-4 py-3 text-right">Harga Beli</th>
                                    <th class="px-4 py-3 text-right">Harga Jual</th>
                                    <th class="px-4 py-3 text-center">Stok</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach (session('produk', []) as $p)
                                    @php
                                    $stokLow = ($p['stok'] ?? 0) > 0 && ($p['stok'] ?? 0) <= ($p['stok_min'] ?? 0);
                                    $stokEmpty = ($p['stok'] ?? 0) === 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition {{ ($p['id'] ?? '') === $editId ? 'bg-navy-100/40' : '' }}">
                                        <td class="px-4 py-3.5 font-medium text-navy-900">
                                            {{ $p['nama'] ?? '' }}
                                            <span class="text-xs text-gray-400 font-normal">/{{ $p['satuan'] ?? 'pcs' }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-500 text-xs">{{ $p['kategori'] ?? '' }}</td>
                                        <td class="px-4 py-3.5 text-right text-gray-600 whitespace-nowrap">Rp {{ number_format($p['harga_beli'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3.5 text-right font-medium text-navy-800 whitespace-nowrap">Rp {{ number_format($p['harga_jual'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3.5 text-center font-semibold">{{ $p['stok'] ?? 0 }}</td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            @if ($stokEmpty)
                                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Habis</span>
                                            @elseif ($stokLow)
                                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-600">Menipis</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">Normal</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-3">
                                                <a href="/produk?edit={{ $p['id'] }}" class="text-xs text-navy-700 hover:text-navy-950 font-semibold transition">Edit</a>
                                                <form method="POST" action="/produk">
                                                    @csrf
                                                    <input type="hidden" name="action" value="hapus">
                                                    <input type="hidden" name="id" value="{{ $p['id'] }}">
                                                    <button type="button"
                                                        data-nama="{{ $p['nama'] }}"
                                                        onclick="konfirmasiHapus(this)"
                                                        class="text-xs text-red-500 hover:text-red-700 font-semibold transition">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Konfirmasi hapus -->
        <script>
            window.konfirmasiHapus = function (btn) {
                const form = btn.closest('form');
                Swal.fire({
                    title: 'DELETE!',
                    text: 'Hapus produk ' + btn.dataset.nama + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Tidak',
                    confirmButtonColor: '#dc2626',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            };
        </script>

        <!-- Notifikasi: sukses = toast 3 detik, selain itu = dialog -->
        @if ($notif)
            <script>
                (function () {
                    var notif = @json($notif);

                    if (notif.icon === 'success') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: notif.text || notif.title,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: { title: 'swal-toast-satu-baris' },
                            didOpen: function (toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                    } else {
                        Swal.fire(notif);
                    }
                })();
            </script>
        @endif
    </main>
</body>
</html>