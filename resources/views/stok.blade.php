@php
$currentPage = 'stok';
$error = session('error', '');
$flash = session('flash', null);
$produkList = session('produk', []);
$stokLogList = session('stok_log', []);

// Hitung stok rendah
$lowStock = array_filter($produkList, fn($p) => ($p['stok'] ?? 0) <= ($p['stok_min'] ?? 0));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok | AlpetBizz</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter','sans-serif'] },
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
<body class="bg-gray-50 font-sans antialiased text-gray-800">
    @include('includes.sidebar')

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 max-w-7xl w-full mx-auto">
            <!-- Floating Header Card -->
            <header class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-navy-900 tracking-tight">Manajemen Stok</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Pantau stok saat ini dan catat mutasi keluar masuk barang.</p>
                </div>
            </header>

            @if ($error)
            <div class="text-xs sm:text-sm text-red-700 bg-red-50 border border-red-200 px-4 py-3 rounded-xl font-medium">{{ $error }}</div>
            @endif

            @if (!empty($lowStock))
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3.5 sm:p-4 flex items-start sm:items-center gap-3">
                <span class="text-orange-500 text-lg flex-shrink-0">⚠</span>
                <p class="text-xs sm:text-sm text-orange-800 font-medium leading-relaxed">
                    {{ count($lowStock) }} produk stok menipis/habis:
                    <span class="font-normal text-orange-950">{{ implode(', ', array_column($lowStock, 'nama')) }}</span>
                </p>
            </div>
            @endif

            <!-- Stok Saat Ini (READ) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm sm:text-base">Kondisi Stok Saat Ini</h3>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[550px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 sm:px-5 py-3 text-left">Produk</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Kategori</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Stok Saat Ini</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Batas Min.</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($produkList as $p)
                                @php
                                $stok = $p['stok'] ?? 0;
                                $stokMin = $p['stok_min'] ?? 0;
                                $pct = $stokMin > 0 ? min(100, ($stok / max($stokMin * 2, 1)) * 100) : 100;
                                $barColor = $stok === 0 ? 'bg-gray-300' : ($stok <= $stokMin ? 'bg-orange-400' : 'bg-green-400');
                                $pctStyle = 'width:' . $pct . '%';
                                @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 sm:px-5 py-3.5 font-medium text-navy-900">
                                    {{ $p['nama'] ?? '' }}
                                    <span class="text-gray-400 text-xs font-normal">/{{ $p['satuan'] ?? 'pcs' }}</span>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500 text-xs">{{ $p['kategori'] ?? '' }}</td>
                                <td class="px-4 sm:px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="font-semibold text-navy-900 w-7 text-right">{{ $stok }}</span>
                                        <div class="w-16 sm:w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full {{ $barColor }} rounded-full" style="{{ $pctStyle }}"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-center text-gray-500">{{ $stokMin }}</td>
                                <td class="px-4 sm:px-5 py-3.5 text-center whitespace-nowrap">
                                    @if ($stok === 0)
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Habis</span>
                                    @elseif ($stok <= $stokMin)
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-600">Menipis</span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Catat Pergerakan (CREATE & UPDATE) -->
            <div id="form-stok" class="bg-white rounded-xl border {{ $editLog ? 'border-navy-200 ring-1 ring-navy-100' : 'border-gray-100' }} shadow-sm p-4 sm:p-6">
                <h3 class="font-semibold text-navy-900 text-sm sm:text-base mb-4 flex items-center gap-2">
                    {{ $editLog ? 'Edit Catatan Mutasi Stok' : '+ Catat Mutasi / Pergerakan Stok' }}
                </h3>
                <form method="POST" action="/stok" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @csrf
                    <input type="hidden" name="action" value="{{ $editLog ? 'update_log' : 'catat' }}">
                    @if ($editLog)
                    <input type="hidden" name="id" value="{{ $editLog['id'] }}">
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pilih Produk</label>
                        <select name="produk_id" required class="{{ $inputClass }}">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($produkList as $p)
                            <option value="{{ $p['id'] }}" {{ ($editLog && ($editLog['produk_id'] ?? '') === $p['id']) ? 'selected' : '' }}>
                                {{ $p['nama'] }} (Sisa: {{ $p['stok'] }} {{ $p['satuan'] }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Mutasi</label>
                        @php $currJenis = $editLog['jenis'] ?? 'Masuk'; @endphp
                        <select name="jenis" class="{{ $inputClass }}">
                            <option value="Masuk" {{ $currJenis === 'Masuk' ? 'selected' : '' }}>Barang Masuk (Restock / Tambah)</option>
                            <option value="Keluar" {{ $currJenis === 'Keluar' ? 'selected' : '' }}>Barang Keluar (Terjual / Rusak)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Mutasi</label>
                        <input type="date" name="tanggal" required
                            value="{{ $editLog['tanggal'] ?? date('Y-m-d') }}"
                            class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Unit</label>
                        <input type="number" name="jumlah" required min="1" placeholder="0"
                            value="{{ (string)($editLog['jumlah'] ?? '') }}"
                            class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan Catatan</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Restock dari supplier batch #4"
                            value="{{ $editLog['keterangan'] ?? '' }}"
                            class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2.5 pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            {{ $editLog ? 'Simpan Perubahan' : 'Simpan Catatan Mutasi' }}
                        </button>
                        @if ($editLog)
                        <a href="/stok" class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">Batal</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Riwayat Pergerakan (READ, UPDATE & DELETE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm sm:text-base">Riwayat Mutasi Stok</h3>
                </div>
                @if (empty($stokLogList))
                <p class="text-center text-gray-400 text-sm py-12">Belum ada riwayat pergerakan stok.</p>
                @else
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[600px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 sm:px-5 py-3 text-left">Tanggal</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Produk</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Jenis</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Jumlah</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Keterangan</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach (array_reverse($stokLogList) as $log)
                                @php $prd = getProdukById($log['produk_id'] ?? ''); @endphp
                            <tr class="hover:bg-gray-50/70 transition {{ ($log['id'] ?? '') === $editId ? 'bg-navy-100/40' : '' }}">
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ date('d M Y', strtotime($log['tanggal'] ?? '')) }}</td>
                                <td class="px-4 sm:px-5 py-3.5 font-medium text-navy-900">{{ $prd ? ($prd['nama'] ?? '') : 'Dihapus' }}</td>
                                <td class="px-4 sm:px-5 py-3.5 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium
                                        {{ ($log['jenis'] ?? '') === 'Masuk' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-600' }}">
                                        {{ $log['jenis'] ?? '' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-center font-semibold whitespace-nowrap {{ ($log['jenis'] ?? '') === 'Masuk' ? 'text-blue-600' : 'text-orange-500' }}">
                                    {{ (($log['jenis'] ?? '') === 'Masuk' ? '+' : '-') . ($log['jumlah'] ?? 0) }}
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500">{{ ($log['keterangan'] ?? '') ?: '—' }}</td>
                                <td class="px-4 sm:px-5 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="/stok?edit={{ $log['id'] }}#form-stok" class="text-xs text-navy-700 hover:text-navy-950 font-semibold transition">Edit</a>
                                        <form method="POST" action="/stok"
                                            onsubmit="return confirm('Hapus riwayat log ini? Stok produk akan dikembalikan (reversal).')">
                                            @csrf
                                            <input type="hidden" name="action" value="hapus_log">
                                            <input type="hidden" name="id" value="{{ $log['id'] }}">
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold transition">Hapus</button>
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
    </main>

    @if ($flash)
    <script>
        var _flash = {!! json_encode($flash) !!};
        Swal.fire(_flash);
    </script>
    @endif
</body>
</html>
