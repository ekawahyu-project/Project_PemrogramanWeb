@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    @php
        $filledFields = collect([$nama, $email, $noHp, $namaUsaha, $kategori, $alamat])->filter(fn ($v) => ! empty($v))->count();
        $completePct = round(($filledFields / 6) * 100);
    @endphp

    <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Profil Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Profil Saya</h2>
                </div>
            </nav>

            <!-- Pesan Flash -->
            @if (! empty($flash))
                <div class="px-5 py-4 rounded-[22px] text-sm font-medium
                    {{ ($flash['type'] ?? '') === 'success'
                        ? 'bg-[#C4FF0D]/10 text-[#C4FF0D] border border-[#C4FF0D]/30'
                        : 'bg-rose-500/10 text-rose-300 border border-rose-500/30' }}">
                    {{ $flash['message'] ?? '' }}
                </div>
            @endif

            <!-- ══════════════════════════════════════════════════════════
                 WEB3BENTO 12-COLUMN RESPONSIVE GRID — PROFIL
                 ══════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-12 items-start">

                <!-- LEFT COLUMN (xl:col-span-4): Digital Identity Card + Solid Lime Completeness Tile -->
                <div class="xl:col-span-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 gap-4">

                    <!-- 1. DIGITAL MERCHANT PASS TILE -->
                    <section class="bento-tile justify-between relative !overflow-hidden">
                        <div class="pointer-events-none absolute -top-12 -right-12 w-36 h-36 rounded-full bg-[#C4FF0D]/15 blur-2xl"></div>

                        <div>
                            <header class="flex items-center justify-between mb-4">
                                <span class="text-xs font-semibold uppercase tracking-wider text-[#9AA68E]">Merchant ID</span>
                            </header>

                            <div class="flex flex-col items-center text-center py-2">
                                @if (! empty($fotoUrl))
                                    <img
                                        src="{{ $fotoUrl }}"
                                        alt="Foto Profil"
                                        class="w-24 h-24 rounded-full object-cover ring-2 ring-[#C4FF0D] shadow-[0_0_28px_rgba(196,255,13,0.3)]"
                                    >
                                @else
                                    <div class="w-24 h-24 rounded-full bg-[#C4FF0D] flex items-center justify-center text-[#152208] text-3xl font-extrabold shadow-[0_0_28px_rgba(196,255,13,0.3)]">
                                        {{ $initial }}
                                    </div>
                                @endif

                                <h3 class="mt-4 font-bold text-xl text-white truncate max-w-full">
                                    {{ $nama ?: 'Profil Pemilik' }}
                                </h3>
                                <p class="text-sm text-[#9AA68E] mt-1 truncate max-w-full">
                                    {{ '@' . $username }} • {{ $email ?: 'Belum ada email' }}
                                </p>

                                <div class="flex flex-wrap items-center justify-center gap-2 mt-3.5">
                                    <span class="px-3 py-1 bg-[#C4FF0D] text-[#0E1308] text-xs font-semibold rounded-full">
                                        Pemilik Usaha UMKM
                                    </span>
                                    @if (! empty($namaUsaha))
                                        <span class="px-3 py-1 bg-[#181F11] text-white border border-white/10 text-xs font-medium rounded-full">
                                            {{ $namaUsaha }}
                                        </span>
                                    @endif
                                    @if (! empty($kategori))
                                        <span class="px-3 py-1 bg-[#C084FC] text-[#0E1308] text-xs font-semibold rounded-full">
                                            {{ $kategori }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-white/[0.08] flex flex-col gap-2.5">
                            <label
                                for="foto_profil"
                                class="cursor-pointer w-full btn-ghost-dark text-sm justify-center hover:border-[#C4FF0D]/50 hover:text-white"
                            >
                                <svg class="w-4 h-4 text-[#C4FF0D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z" />
                                    <circle cx="12" cy="14" r="3" stroke-width="2" />
                                </svg>
                                Ubah Foto Profil
                            </label>
                            @if (! empty($fotoUrl))
                                <form method="POST" action="/profil"
                                      onsubmit="return confirm('Hapus foto profil Anda?')">
                                    @csrf
                                    <input type="hidden" name="action" value="hapus_foto">
                                    <button type="submit"
                                            class="w-full btn-ghost-dark text-sm justify-center text-rose-300 hover:border-rose-500/50 hover:text-rose-200">
                                        Hapus Foto Profil
                                    </button>
                                </form>
                            @endif
                        </div>
                    </section>

                    <!-- 2. SOLID LIME ACCENT TILE: Kelengkapan Identitas -->
                    <section class="bento-tile-accent justify-between">
                        <div>
                            <header class="flex items-center justify-between">
                                <h3 class="text-sm font-bold opacity-85">Skor Identitas</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold"
                                      style="background: rgba(21, 34, 8, 0.14);">
                                    {{ $filledFields }}/6 Data
                                </span>
                            </header>

                            <p class="mt-4 text-4xl sm:text-5xl font-bold leading-none tracking-[-0.04em]">
                                {{ $completePct }}<span class="text-2xl">%</span>
                            </p>
                            <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">Kelengkapan Profil &amp; Toko</p>

                            <div class="mt-5 rounded-2xl p-3.5" style="background: rgba(21, 34, 8, 0.1);">
                                <div class="flex items-baseline justify-between text-xs sm:text-sm font-semibold">
                                    <span class="opacity-80">Status Verifikasi</span>
                                    <span>{{ $completePct === 100 ? 'Lengkap' : 'Sebagian' }}</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: rgba(21, 34, 8, 0.16);">
                                    <div class="h-full rounded-full bg-[#152208]" style="width: {{ $completePct }}%;"></div>
                                </div>
                            </div>

                            <ul class="mt-4 space-y-2 text-xs sm:text-sm">
                                <li class="flex justify-between">
                                    <span class="opacity-80">Akun &amp; Email</span>
                                    <span class="font-bold">{{ (! empty($nama) && ! empty($email)) ? '✓ Aktif' : 'Belum' }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="opacity-80">Kontak WhatsApp</span>
                                    <span class="font-bold">{{ ! empty($noHp) ? '✓ Terisi' : 'Kosong' }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="opacity-80">Identitas Toko</span>
                                    <span class="font-bold">{{ ! empty($namaUsaha) ? '✓ Terisi' : 'Kosong' }}</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                </div>

                <!-- RIGHT COLUMN (xl:col-span-8): Bento Profile & Merchant Settings Form + Zona Hapus Akun -->
                <div class="xl:col-span-8 space-y-4">
                <section class="bento-tile bento-tile-lg justify-between">
                    <form
                        method="POST"
                        action="/profil"
                        id="profileForm"
                        enctype="multipart/form-data"
                        class="space-y-6"
                    >
                        @csrf
                        <input type="hidden" name="action" value="save">
                        <input
                            type="file"
                            id="foto_profil"
                            name="foto_profil"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                        >

                        <!-- Sub-Bento 1: Informasi Akun Pengguna -->
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    Informasi Akun Pengguna
                                </h3>
                                <span class="text-xs text-[#9AA68E]">Kredensial Utama</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="nama" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
                                        Nama Lengkap <span class="text-[#C4FF0D]">*</span>
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
                                <div>
                                    <label for="username" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
                                        Username <span class="text-[#9AA68E]/60 font-normal">(ID Akun Tetap)</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="username"
                                        name="username"
                                        value="{{ $username }}"
                                        readonly
                                        class="{{ $inputClass }} opacity-60 cursor-not-allowed"
                                    >
                                </div>
                                <div>
                                    <label for="email" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
                                        Alamat Email <span class="text-[#C4FF0D]">*</span>
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
                                <div>
                                    <label for="no_hp" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
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

                        <!-- Sub-Bento 2: Informasi Usaha UMKM -->
                        <div class="rounded-2xl bg-[#181F11] border border-white/[0.07] p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    Informasi Usaha UMKM
                                </h3>
                                <span class="text-xs text-[#9AA68E]">Operasional Bisnis</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="nama_usaha" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
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
                                <div>
                                    <label for="kategori" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
                                        Kategori Bidang Usaha
                                    </label>
                                    <select
                                        id="kategori"
                                        name="kategori"
                                        class="{{ $inputClass }} cursor-pointer"
                                    >
                                        @foreach ($kategoriOptions as $opt)
                                            <option value="{{ $opt }}" {{ $kategori === $opt ? 'selected' : '' }}>
                                                {{ $opt ?: '-- Pilih kategori usaha --' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="alamat" class="block text-xs sm:text-sm font-medium text-[#9AA68E] mb-1.5">
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
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                            <p class="text-xs sm:text-sm text-[#9AA68E]">Perubahan profil akan langsung diperbarui pada sesi aktif.</p>
                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                                <button type="reset" id="resetButton" class="w-full sm:w-auto btn-ghost-dark">
                                    Reset Formulir
                                </button>
                                <button type="submit" id="saveButton" class="w-full sm:w-auto btn-lime">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- ZONA BERBAHAYA: Hapus Akun Permanen -->
                <section class="bento-tile border-rose-500/30">
                    <div class="flex flex-col lg:flex-row lg:items-end gap-5">
                        <div class="flex-1">
                            <h3 class="text-base font-bold text-rose-300">Hapus Akun</h3>
                            <p class="text-xs sm:text-sm text-[#9AA68E] mt-1.5 leading-relaxed">
                                Akun, foto profil, dan seluruh catatan hutang &amp; piutang milik Anda akan dihapus permanen
                                dan tidak dapat dikembalikan. Masukkan kata sandi untuk konfirmasi.
                            </p>
                        </div>
                        <form method="POST" action="/profil" data-spa-ignore="true"
                              class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto"
                              onsubmit="return confirm('Yakin ingin menghapus akun secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            <input type="hidden" name="action" value="hapus_akun">
                            <label for="password_konfirmasi" class="sr-only">Kata sandi</label>
                            <input type="password" id="password_konfirmasi" name="password_konfirmasi" required
                                   autocomplete="current-password" placeholder="Kata sandi Anda"
                                   class="{{ $inputClass }} w-full sm:w-56">
                            <button type="submit"
                                    class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-semibold bg-rose-500/15 text-rose-300 border border-rose-500/40 hover:bg-rose-500 hover:text-white transition">
                                Hapus Akun Permanen
                            </button>
                        </form>
                    </div>
                </section>
                </div>

            </div>
        </div>
@endsection
