<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profil | AlpetBizz</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
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
        }
    </script>

</head>

<body class="bg-gray-50 font-sans antialiased text-gray-800">

    @include('includes.sidebar')

    <main
        class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300"
    >

        <div
            class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 max-w-7xl w-full mx-auto"
        >

            <!-- Header -->

            <header
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >

                <div>

                    <h2
                        class="font-bold text-xl sm:text-2xl text-navy-900 tracking-tight"
                    >
                        Profil Saya
                    </h2>

                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Kelola informasi akun pengguna dan identitas usaha Anda.
                    </p>

                </div>

            </header>

            <!-- Main Profile Card -->

            <div
                class="w-full bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
            >

                <!-- Profile Top Banner -->

                <div
                    class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-4 px-4 sm:px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-navy-100/50 to-white"
                >

                    <!-- FOTO + TOMBOL UBAH FOTO -->
                    <div
                        class="flex flex-col items-center gap-2 flex-shrink-0"
                    >
                        @if (!empty($fotoUrl))
                            <img
                                src="{{ $fotoUrl }}"
                                alt="Foto Profil"
                                class="w-16 h-16 rounded-full object-cover shadow-md"
                            >
                        @else
                            <div
                                class="w-16 h-16 rounded-full bg-gradient-to-br from-navy-800 to-navy-950 flex items-center justify-center text-white text-2xl font-bold shadow-md"
                            >
                                {{ $initial }}
                            </div>
                        @endif
                        <!-- Tombol Ubah Foto -->
                        <label
                            for="foto_profil"
                            class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-navy-800 rounded-lg text-xs font-semibold transition"
                        >
                            <svg
                                class="w-3.5 h-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                                />
                                <circle
                                    cx="12"
                                    cy="14"
                                    r="3"
                                    stroke-width="2"
                                />
                            </svg>
                            Ubah Foto
                        </label>
                    </div>

                    <!-- Informasi Profil -->

                    <div class="flex-1 min-w-0">

                        <p
                            class="font-bold text-base sm:text-lg text-navy-900 truncate"
                        >
                            {{ $nama ?: 'Profil Pemilik' }}
                        </p>

                        <p
                            class="text-xs sm:text-sm text-gray-500 mt-0.5 truncate"
                        >
                            {{ $username }}
                            |
                            {{ $email ?: 'Belum ada email' }}
                        </p>

                        <div
                            class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 mt-2"
                        >

                            <span
                                class="inline-flex items-center px-2.5 py-0.5 bg-navy-100 text-navy-800 text-xs font-semibold rounded-md"
                            >
                                Pemilik Usaha UMKM
                            </span>

                            @if (!empty($namaUsaha))

                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md"
                                >
                                    {{ $namaUsaha }}
                                </span>

                            @endif

                            @if (!empty($kategori))

                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-medium rounded-md"
                                >
                                    {{ $kategori }}
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

                <!-- Pesan Flash -->

                @if (session('flash'))
                    @php $flash = session('flash'); @endphp
                    <div
                        class="mx-4 sm:mx-6 mt-4 px-4 py-3 rounded-xl text-xs sm:text-sm font-medium
                        {{ ($flash['type'] ?? '') === 'success'
                            ? 'bg-green-50 text-green-700 border border-green-200'
                            : 'bg-red-50 text-red-600 border border-red-200' }}"
                    >
                        {{ $flash['message'] ?? '' }}
                    </div>
                @endif

                <!-- Form -->

                <form
                    method="POST"
                    action="/profil"
                    id="profileForm"
                    enctype="multipart/form-data"
                    class="px-4 sm:px-6 py-5 space-y-6"
                >

                    @csrf

                    <input
                        type="hidden"
                        name="action"
                        value="save"
                    >

                    <!-- INPUT FOTO -->

                    <input
                        type="file"
                        id="foto_profil"
                        name="foto_profil"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                    >

                    <!-- Informasi Akun -->

                    <div>

                        <div
                            class="flex items-center justify-between mb-3.5"
                        >
                            <h3
                                class="text-sm font-bold text-navy-900 flex items-center gap-2"
                            >
                                <svg
                                    class="w-4 h-4 text-navy-800"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                                Informasi Akun Pengguna
                            </h3>
                            <span
                                class="text-[11px] text-gray-400"
                            >
                                Klik kolom untuk mengubah
                            </span>
                        </div>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4"
                        >
                            <!-- Nama -->
                            <div>
                                <label
                                    for="nama"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Nama Lengkap
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    placeholder="Masukkan nama lengkap"
                                    value="{{ $nama }}"
                                    class="{{ $inputClass }}"
                                    required
                                >
                            </div>
                            <!-- Username -->
                            <div>
                                <label
                                    for="username"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Username
                                    <span class="text-gray-400 font-normal">
                                        (ID Akun)
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    value="{{ $username }}"
                                    readonly
                                    class="{{ $inputClass }} bg-gray-100 text-gray-500 cursor-not-allowed"
                                >
                                <p
                                    class="text-[11px] text-gray-400 mt-1"
                                >
                                    Username digunakan sebagai tanda pengenal login tetap.
                                </p>
                            </div>
                            <!-- Email -->

                            <div>

                                <label
                                    for="email"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Alamat Email
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Masukkan email aktif"
                                    value="{{ $email }}"
                                    class="{{ $inputClass }}"
                                    required
                                >

                            </div>

                            <!-- Nomor HP -->

                            <div>

                                <label
                                    for="no_hp"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Nomor WhatsApp / HP
                                </label>

                                <input
                                    type="tel"
                                    id="no_hp"
                                    name="no_hp"
                                    placeholder="Contoh: 08123456789"
                                    value="{{ $noHp }}"
                                    class="{{ $inputClass }}"
                                >

                            </div>

                        </div>

                    </div>

                    <!-- Informasi Usaha -->

                    <div>

                        <div
                            class="flex items-center justify-between mb-3.5"
                        >

                            <h3
                                class="text-sm font-bold text-navy-900 flex items-center gap-2"
                            >

                                <svg
                                    class="w-4 h-4 text-navy-800"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />

                                </svg>

                                Informasi Usaha UMKM

                            </h3>

                            <span
                                class="text-[11px] text-gray-400"
                            >
                                Data profil operasional bisnis
                            </span>

                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4"
                        >

                            <!-- Nama Usaha -->

                            <div>

                                <label
                                    for="nama_usaha"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Nama Usaha / Toko
                                </label>

                                <input
                                    type="text"
                                    id="nama_usaha"
                                    name="nama_usaha"
                                    placeholder="Masukkan nama usaha / toko"
                                    value="{{ $namaUsaha }}"
                                    class="{{ $inputClass }}"
                                >

                            </div>

                            <!-- Kategori -->

                            <div>

                                <label
                                    for="kategori"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Kategori Bidang Usaha
                                </label>

                                <select
                                    id="kategori"
                                    name="kategori"
                                    class="{{ $inputClass }} cursor-pointer"
                                >

                                    @foreach ($kategoriOptions as $opt)

                                        <option
                                            value="{{ $opt }}"
                                            {{ $kategori === $opt ? 'selected' : '' }}
                                        >
                                            {{ $opt ?: '-- Pilih kategori usaha --' }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <!-- Alamat -->

                            <div class="sm:col-span-2">

                                <label
                                    for="alamat"
                                    class="block text-xs font-semibold text-gray-600 mb-1.5"
                                >
                                    Alamat Lengkap Usaha
                                </label>

                                <textarea
                                    id="alamat"
                                    name="alamat"
                                    rows="3"
                                    placeholder="Masukkan alamat lokasi usaha"
                                    class="{{ $inputClass }} resize-y"
                                >{{ $alamat }}</textarea>
                            </div>
                        </div>

                    </div>

                    <!-- Actions -->

                    <div
                        class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-gray-100"
                    >

                        <button
                            type="submit"
                            id="saveButton"
                            class="w-full sm:w-auto px-6 py-2.5 bg-navy-800 hover:bg-navy-950 text-white rounded-lg text-sm font-semibold transition shadow-sm hover:shadow"
                        >
                            Simpan Perubahan
                        </button>

                        <button
                            type="reset"
                            id="resetButton"
                            class="w-full sm:w-auto text-center px-6 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium transition"
                        >
                            Reset Formulir
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </main>
    <script src="{{ asset('assets/js/profil.js') }}"></script>
</body>
</html>
