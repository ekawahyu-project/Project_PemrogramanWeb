<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AlpetBizz</title>
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
                    <h2 class="font-bold text-xl sm:text-2xl text-navy-900 tracking-tight">Dashboard</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Selamat datang, <span class="font-semibold text-navy-800">{{ $user }}</span>
                    </p>
                </div>
            </header>

            <!-- Stok rendah alert -->
            @if ($lowStock > 0)
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3.5 sm:p-4 flex items-start sm:items-center gap-3">
                <span class="text-orange-500 text-lg flex-shrink-0">⚠</span>
                <p class="text-xs sm:text-sm text-orange-800 font-medium leading-relaxed">
                    {{ $lowStock }} produk stok menipis.
                    <a href="/stok" class="underline font-semibold hover:text-orange-950">Cek halaman Stok</a>
                </p>
            </div>
            @endif

            <!-- Kartu Statistik -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach ($stats as $s)
                <div class="{{ $s['cardClass'] }} rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-semibold {{ $s['labelClass'] }} mb-1.5">{{ $s['label'] }}</p>
                        <p class="text-lg sm:text-xl font-bold tracking-tight {{ $s['valueClass'] }}">{{ $s['value'] }}</p>
                    </div>
                    <p class="text-xs {{ $s['subClass'] }} mt-2.5 pt-2">{{ $s['sub'] }}</p>
                </div>
                @endforeach
            </div>

            <!-- Shortcut navigasi -->
            <div>
                <h3 class="font-semibold text-navy-900 text-sm mb-3">Akses Cepat Modul</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @php
                    $shortcuts = [
                        ['href' => '/transaksi',   'label' => 'Transaksi',   'desc' => 'Catat pemasukan & pengeluaran'],
                        ['href' => '/produk',      'label' => 'Produk',      'desc' => 'Kelola katalog data produk'],
                        ['href' => '/stok',        'label' => 'Stok',        'desc' => 'Pantau & catat pergerakan barang'],
                        ['href' => '/laporan',     'label' => 'Laporan',     'desc' => 'Grafik analitik & laporan bulanan'],
                        ['href' => '/rekomendasi', 'label' => 'Rekomendasi', 'desc' => 'Analisis pintar & catatan bisnis'],
                        ['href' => '/profil',      'label' => 'Profil',      'desc' => 'Informasi akun & profil usaha'],
                    ];
                    @endphp
                    @foreach ($shortcuts as $s)
                    <a href="{{ $s['href'] }}"
                        class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:border-navy-300 hover:shadow-md transition group flex flex-col justify-between">
                        <div>
                            <p class="font-semibold text-navy-900 text-sm group-hover:text-navy-700 flex items-center justify-between">
                                {{ $s['label'] }}
                                <span class="text-gray-300 group-hover:text-navy-600 transition-transform group-hover:translate-x-0.5">→</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $s['desc'] }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- 5 Transaksi Terakhir -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-navy-900 text-sm">Transaksi Terakhir</h3>
                    <a href="/transaksi" class="text-xs font-semibold text-navy-700 hover:text-navy-950 transition">Lihat semua</a>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs sm:text-sm min-w-[500px]">
                        <thead class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 sm:px-5 py-3 text-left">Tanggal</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Keterangan</th>
                                <th class="px-4 sm:px-5 py-3 text-left">Jenis</th>
                                <th class="px-4 sm:px-5 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($transaksiTerbaru as $t)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 sm:px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ date('d M Y', strtotime($t['tanggal'])) }}</td>
                                <td class="px-4 sm:px-5 py-3.5 font-medium text-navy-900">{{ $t['keterangan'] }}</td>
                                <td class="px-4 sm:px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-medium {{ ($t['jenis'] ?? '') === 'Pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ $t['jenis'] }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-right font-semibold whitespace-nowrap {{ ($t['jenis'] ?? '') === 'Pemasukan' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ (($t['jenis'] ?? '') === 'Pemasukan' ? '+' : '-') . 'Rp ' . number_format($t['jumlah'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
