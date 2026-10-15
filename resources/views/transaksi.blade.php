@php
$currentPage = 'transaksi';
$flash = session('flash', null);
$transaksiList = session('transaksi', []);
$produkList = session('produk', []);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi | AlpetBizz</title>
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
                            '200': '#b9d0ff',
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
                    <h2 class="font-bold text-xl sm:text-2xl text-navy-900 tracking-tight">Manajemen Transaksi</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Catat dan kelola riwayat pemasukan &amp; pengeluaran usaha.</p>
                </div>
            </header>

            <!-- Flash Message -->
            @if ($flash)
            <div id="flash"
                 role="{{ in_array($flash['type'] ?? '', ['success', 'danger']) ? 'status' : 'alert' }}"
                 class="flex items-start justify-between gap-3 rounded-xl border px-4 py-3 text-sm transition-opacity duration-300
                        {{ ($flash['type'] ?? '') === 'success'
                            ? 'bg-green-50 border-green-200 text-green-700'
                            : 'bg-red-50 border-red-200 text-red-700' }}">
                <span>{{ $flash['msg'] ?? '' }}</span>
                <button type="button" onclick="document.getElementById('flash').remove()"
                        class="font-semibold leading-none" aria-label="Tutup">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('flash');
                    if (el) { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 300); }
                }, 4000);
            </script>
            @endif

            <!-- Form Tambah / Edit (CREATE & UPDATE) -->
            <div class="bg-white rounded-xl border {{ $editTrx ? 'border-navy-200 ring-1 ring-navy-100' : 'border-gray-100' }} shadow-sm p-4 sm:p-6">
                <h3 class="font-semibold text-navy-900 text-sm sm:text-base mb-4 flex items-center gap-2">
                    {{ $editTrx ? 'Edit Transaksi' : '+ Tambah Transaksi Baru' }}
                </h3>
                <form method="POST" action="/transaksi" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @csrf
                    <input type="hidden" name="action" value="{{ $editTrx ? 'update' : 'tambah' }}">
                    @if ($editTrx)
                    <input type="hidden" name="id" value="{{ $editTrx['id'] }}">
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Transaksi</label>
                        <input type="date" name="tanggal" required
                            value="{{ $editTrx['tanggal'] ?? date('Y-m-d') }}"
                            class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Arus Kas</label>
                        <select name="jenis" class="{{ $inputClass }}">
                            <option value="Pemasukan"   {{ ($editTrx['jenis'] ?? 'Pemasukan') === 'Pemasukan'   ? 'selected' : '' }}>Pemasukan (+)</option>
                            <option value="Pengeluaran" {{ ($editTrx['jenis'] ?? '')          === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan</label>
                        <input type="text" name="keterangan" required placeholder="Contoh: Penjualan produk atau biaya listrik"
                            value="{{ $editTrx['keterangan'] ?? '' }}"
                            class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Nominal (Rp)</label>
                        <input type="number" name="jumlah" required min="1" placeholder="0"
                            value="{{ (string)($editTrx['jumlah'] ?? '') }}"
                            class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Produk Terkait <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <select name="produk_id" class="{{ $inputClass }}">
                            <option value="">— Tidak terhubung dengan stok produk —</option>
                            @foreach ($produkList as $p)
                            <option value="{{ $p['id'] }}"
                                {{ ($editTrx['produk_id'] ?? null) === $p['id'] ? 'selected' : '' }}>
                                {{ $p['nama'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2.5 pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition">
                            {{ $editTrx ? 'Simpan Perubahan' : 'Tambah Transaksi' }}
                        </button>
                        @if ($editTrx)
                        <a href="/transaksi"
                            class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                            Batal
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Transaksi (READ & DELETE) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-navy-900 text-sm sm:text-base">Daftar Riwayat Transaksi</h3>
                </div>
                @if (empty($transaksiList))
                <p class="text-center text-gray-400 text-sm py-12">Belum ada riwayat transaksi.</p>
                @else
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[620px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 sm:px-5 py-3 text-left">Tanggal</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Keterangan</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Produk</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Jenis</th>
                                <th class="px-4 sm:px-5 py-3 text-right">Jumlah</th>
                                <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach (array_reverse($transaksiList) as $t)
                            <tr class="hover:bg-gray-50/70 transition {{ ($t['id'] ?? '') === $editId ? 'bg-navy-100/40' : '' }}">
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500 whitespace-nowrap">
                                    {{ date('d M Y', strtotime($t['tanggal'] ?? '')) }}
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 font-medium text-navy-900">{{ $t['keterangan'] ?? '' }}</td>
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500 text-xs">
                                    @php $p = !empty($t['produk_id']) ? getProdukById($t['produk_id']) : null; @endphp
                                    {{ $p ? ($p['nama'] ?? '') : '—' }}
                                </td>
                                <td class="px-4 sm:px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium whitespace-nowrap
                                        {{ ($t['jenis'] ?? '') === 'Pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ $t['jenis'] ?? '' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-right font-semibold whitespace-nowrap {{ ($t['jenis'] ?? '') === 'Pemasukan' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ (($t['jenis'] ?? '') === 'Pemasukan' ? '+' : '-') . 'Rp ' . number_format($t['jumlah'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="/transaksi?edit={{ urlencode($t['id'] ?? '') }}" class="text-xs text-navy-700 hover:text-navy-950 font-semibold transition">Edit</a>
                                        <form method="POST" action="/transaksi" onsubmit="return confirm('Hapus transaksi ini?')">
                                            @csrf
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="{{ $t['id'] ?? '' }}">
                                            <button class="text-xs text-red-500 hover:text-red-700 font-semibold transition">Hapus</button>
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
</body>
</html>