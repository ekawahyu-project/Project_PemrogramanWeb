@extends('layouts.app')

@section('title', 'Konsultasi AI Bisnis')

@section('content')
    @php
        $keu = $snapshot['keuangan'];
        $inv = $snapshot['inventaris'];
        $kas = $snapshot['kasbon'];
        $profil = $snapshot['profil'];
    @endphp

    <!-- Gaya khusus halaman Konsultasi AI (di dalam main agar ikut termuat oleh spa.js) -->
    <style>
            .ai-markdown p {
                margin-bottom: 0.6rem;
                line-height: 1.65;
            }
            .ai-markdown p:last-child {
                margin-bottom: 0;
            }
            .ai-markdown h3, .ai-markdown h4 {
                color: #ffffff;
                font-weight: 700;
                margin-top: 0.85rem;
                margin-bottom: 0.35rem;
                font-size: 0.92rem;
            }
            .ai-markdown h3:first-child, .ai-markdown h4:first-child {
                margin-top: 0;
            }
            .ai-markdown ul, .ai-markdown ol {
                margin: 0.45rem 0 0.7rem 1.25rem;
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }
            .ai-markdown ul {
                list-style-type: disc;
            }
            .ai-markdown ol {
                list-style-type: decimal;
            }
            .ai-markdown li {
                line-height: 1.6;
            }
            .ai-markdown strong {
                color: #F4F7F0;
                font-weight: 600;
            }
            .ai-markdown code {
                background: rgba(196, 255, 13, 0.12);
                color: #C4FF0D;
                padding: 0.1rem 0.4rem;
                border-radius: 0.4rem;
                font-size: 0.82em;
            }
            @keyframes aiDotPulse {
                0%, 80%, 100% { transform: scale(0.7); opacity: 0.35; }
                40% { transform: scale(1); opacity: 1; }
            }
            .ai-typing-dot {
                width: 7px;
                height: 7px;
                border-radius: 9999px;
                background: #C4FF0D;
                display: inline-block;
                animation: aiDotPulse 1.3s infinite ease-in-out both;
            }
            .ai-typing-dot:nth-child(2) { animation-delay: 0.18s; }
            .ai-typing-dot:nth-child(3) { animation-delay: 0.36s; }
        </style>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 max-w-[1380px] w-full mx-auto space-y-5">

            <!-- Web3Bento Top Status Bar -->
            <nav class="flex flex-wrap items-center justify-between gap-3 px-1" aria-label="Konsultasi AI Header">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs sm:text-sm uppercase tracking-wider text-[#9AA68E]">Workspace /</span>
                    <h2 class="text-base sm:text-lg font-semibold text-white">Konsultasi AI Bisnis</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">

                    <button type="button"
                            id="ai-reset-btn"
                            class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-[#12170D] px-3.5 py-1.5 text-xs font-semibold text-[#9AA68E] hover:border-rose-500/40 hover:text-rose-300 transition cursor-pointer {{ $messages->isEmpty() ? 'hidden' : '' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Reset Chat
                    </button>
                </div>
            </nav>

            @if (! $isConfigured)
                <div class="rounded-[22px] border border-amber-400/35 bg-amber-400/10 px-5 py-4 text-xs sm:text-sm text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-amber-300">API Key Gemini belum dipasang</p>
                        <p class="mt-0.5 text-amber-200/85">
                            Tambahkan variabel <code class="px-1.5 py-0.5 rounded bg-black/30 text-[#C4FF0D]">GEMINI_API_KEY</code> di file <code class="px-1.5 py-0.5 rounded bg-black/30">.env</code> (lokal) atau di <strong>Vercel → Environment Variables</strong> (online) agar fitur konsultasi aktif.
                        </p>
                    </div>
                    <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer"
                       class="shrink-0 rounded-xl bg-[#C4FF0D] px-4 py-2 text-xs font-bold text-[#152208] hover:brightness-105 transition text-center">
                        Ambil API Key Gratis →
                    </a>
                </div>
            @endif

            <!-- 12-COLUMN RESPONSIVE BENTO SPLIT -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">

                <!-- LEFT: INTERACTIVE GEMINI CHAT WORKSPACE (8/12, pas 1 layar & sticky agar langsung bisa ngetik tanpa scroll) -->
                <div class="xl:col-span-8 xl:sticky xl:top-6 min-w-0">
                    <section data-no-reveal class="bento-tile !p-0 !overflow-hidden flex flex-col h-[calc(100dvh-8.25rem)] sm:h-[calc(100dvh-7.5rem)] lg:h-[calc(100dvh-6.75rem)] min-h-[460px] max-h-[780px]">
                    <!-- Chat Header -->
                    <header class="shrink-0 px-4 py-3.5 sm:px-6 border-b border-white/[0.08] flex items-center justify-between gap-3 bg-[#151B0F]/80">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}"
                                 alt="AlpetBizz AI"
                                 class="size-9 sm:size-10 shrink-0 object-contain drop-shadow-[0_0_12px_rgba(196,255,13,0.4)]">
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-bold text-white truncate">AlpetBizz AI Consultant</h3>
                                <p class="text-xs text-[#9AA68E] truncate">
                                    Terhubung ke data live <span class="text-[#C4FF0D] font-medium">{{ $profil['nama_usaha'] }}</span>
                                </p>
                            </div>
                        </div>
                        <span id="ai-message-count" class="shrink-0 rounded-full border border-white/10 bg-[#181F11] px-3 py-1 text-[11px] font-medium text-[#9AA68E]">
                            {{ $messages->count() }} pesan
                        </span>
                    </header>

                    <!-- Scrollable Message Feed -->
                    <div id="ai-chat-feed" class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-4 space-y-4">
                        <!-- Empty Welcome State (kompak agar input langsung terlihat di bawah tanpa scroll) -->
                        <div id="ai-empty-state" class="{{ $messages->isNotEmpty() ? 'hidden' : '' }} min-h-full flex flex-col items-center justify-center text-center max-w-xl mx-auto py-2">
                            <div class="size-11 rounded-2xl bg-[#C4FF0D] text-[#0E1308] grid place-items-center mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <h4 class="text-sm sm:text-base font-bold text-white">
                                Halo {{ $profil['nama_pemilik'] }}, ada yang ingin didiskusikan soal usaha?
                            </h4>
                            <p class="text-xs text-[#9AA68E] mt-1 leading-relaxed max-w-md">
                                AlpetBizz AI membaca transaksi, stok, margin, dan kasbon tokomu secara langsung agar sarannya tepat sasaran.
                            </p>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 w-full text-left">
                                @foreach ($quickPrompts as $qp)
                                    <button type="button"
                                            data-ai-quick-prompt="{{ $qp['prompt'] }}"
                                            class="group rounded-2xl bg-[#181F11] border border-white/[0.07] hover:border-[#C4FF0D]/45 p-3 transition cursor-pointer">
                                        <p class="text-xs font-semibold text-[#C4FF0D] flex items-center justify-between gap-2">
                                            <span>{{ $qp['label'] }}</span>
                                            <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                                        </p>
                                        <p class="text-[11px] text-[#9AA68E] mt-0.5 line-clamp-2">{{ $qp['prompt'] }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Existing Messages -->
                        @foreach ($messages as $m)
                            @if ($m->isFromUser())
                                <div class="flex justify-end" data-ai-msg>
                                    <div class="max-w-[85%] sm:max-w-[78%] rounded-2xl rounded-br-md bg-[#C4FF0D] text-[#152208] px-4 py-3 shadow-[0_8px_24px_-10px_rgba(196,255,13,0.4)]">
                                        <p class="text-xs sm:text-sm font-medium whitespace-pre-wrap break-words">{{ $m->content }}</p>
                                        <p class="text-[10px] font-semibold opacity-65 text-right mt-1.5">{{ $m->created_at?->format('H:i') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start gap-2.5" data-ai-msg>
                                    <span class="size-7 shrink-0 rounded-xl bg-[#181F11] border border-[#C4FF0D]/30 text-[#C4FF0D] text-[10px] font-bold grid place-items-center mt-0.5">AI</span>
                                    <div class="max-w-[88%] sm:max-w-[82%] rounded-2xl rounded-tl-md bg-[#181F11] border border-white/[0.08] px-4 py-3.5 text-xs sm:text-sm text-[#D7E0CE]">
                                        <div class="ai-markdown break-words" data-ai-raw="{{ $m->content }}"></div>
                                        <p class="text-[10px] text-[#9AA68E]/75 mt-2">{{ $m->created_at?->format('H:i') }} · AI</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <!-- Typing Indicator -->
                        <div id="ai-typing-indicator" class="hidden items-start gap-2.5">
                            <span class="size-7 shrink-0 rounded-xl bg-[#181F11] border border-[#C4FF0D]/30 text-[#C4FF0D] text-[10px] font-bold grid place-items-center mt-0.5">AI</span>
                            <div class="rounded-2xl rounded-tl-md bg-[#181F11] border border-[#C4FF0D]/25 px-4 py-3 flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="ai-typing-dot"></span>
                                    <span class="ai-typing-dot"></span>
                                    <span class="ai-typing-dot"></span>
                                </span>
                                <span class="text-xs text-[#9AA68E]">AI sedang menganalisis data toko...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Composer Form (selalu tampil di bagian bawah card tanpa perlu scroll) -->
                    <div class="shrink-0 p-3.5 sm:px-5 sm:py-3.5 border-t border-white/[0.08] bg-[#151B0F]">
                        <form id="ai-chat-form" method="POST" action="/konsultasi-ai/kirim" data-spa-ignore="true" class="space-y-2">
                            @csrf
                            <div class="flex items-end gap-2">
                                <textarea id="ai-prompt-input"
                                          name="pesan"
                                          rows="1"
                                          maxlength="2000"
                                          required
                                          placeholder="Tanyakan strategi bisnis atau stok..."
                                          class="flex-1 min-w-0 px-3.5 py-2.5 sm:px-4 sm:py-3 bg-[#181F11] border border-white/12 rounded-2xl text-xs sm:text-sm leading-snug text-[#F4F7F0] placeholder:text-[#8E9882]/65 outline-none focus:border-[#C4FF0D] focus:ring-2 focus:ring-[#C4FF0D]/15 transition resize-none min-h-[44px] max-h-32"></textarea>
                                <button type="submit"
                                        id="ai-send-btn"
                                        class="shrink-0 btn-lime !rounded-2xl !px-4 sm:!px-5 !py-2.5 sm:!py-3 text-xs sm:text-sm min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span>Kirim</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 12h14m-7-7l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-between gap-2 text-[11px] text-[#9AA68E]/80 px-1">
                                <span class="truncate">Enter untuk kirim · Shift+Enter baris baru</span>
                                <span id="ai-char-counter" class="shrink-0 tabular-nums">0/2000</span>
                            </div>
                        </form>
                    </div>
                    </section>
                </div>

                <!-- RIGHT: LIVE STORE CONTEXT & QUICK ACTIONS (4/12) -->
                <div class="xl:col-span-4 space-y-4">

                    <!-- 1. SOLID LIME ACCENT TILE: Live Business Snapshot -->
                    <section class="bento-tile-accent justify-between">
                        <div>
                            <header class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-bold opacity-85">Konteks Data Toko</h3>
                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold" style="background: rgba(21, 34, 8, 0.14);">
                                    Live Sync
                                </span>
                            </header>

                            <p class="mt-4 text-3xl sm:text-4xl font-bold leading-none tracking-[-0.04em] tabular-nums">
                                {{ $keu['laba_bersih'] >= 0 ? '+' : '−' }}Rp {{ number_format(abs($keu['laba_bersih']), 0, ',', '.') }}
                            </p>
                            <p class="mt-1.5 text-xs sm:text-sm font-medium opacity-80">
                                Laba Bersih · Margin {{ number_format($keu['margin_pct'], 1) }}%
                            </p>

                            <dl class="mt-4 rounded-2xl p-3.5 space-y-2 text-xs sm:text-sm" style="background: rgba(21, 34, 8, 0.1);">
                                <div class="flex justify-between gap-2">
                                    <dt class="opacity-80">Total Pemasukan</dt>
                                    <dd class="font-bold tabular-nums">Rp {{ number_format($keu['pemasukan'], 0, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="opacity-80">Stok Kritis</dt>
                                    <dd class="font-bold tabular-nums">{{ $inv['stok_kritis_count'] }} dari {{ $inv['total_sku'] }} SKU</dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="opacity-80">Sisa Piutang</dt>
                                    <dd class="font-bold tabular-nums">Rp {{ number_format($kas['sisa_piutang'], 0, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="opacity-80">Sisa Hutang</dt>
                                    <dd class="font-bold tabular-nums">Rp {{ number_format($kas['sisa_hutang'], 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-5 pt-3.5 border-t border-[#152208]/15">
                            <button type="button"
                                    data-ai-quick-prompt="Berikan audit bisnis menyeluruh untuk toko saya berdasarkan data keuangan, stok produk, dan kasbon saat ini. Susun dalam 3 bagian: (1) Kondisi Sehat, (2) Risiko Mendesak, dan (3) Rencana Aksi Minggu Ini."
                                    class="block w-full text-center rounded-2xl py-3 text-xs sm:text-sm font-semibold transition-transform active:scale-[0.98] cursor-pointer"
                                    style="background: #152208; color: #C4FF0D;">
                                Analisis Bisnis Menyeluruh
                            </button>
                        </div>
                    </section>

                    <!-- 2. QUICK PROMPTS TILE -->
                    <section class="bento-tile space-y-3">
                        <header class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-white">Topik Cepat</h3>
                            <span class="text-[11px] font-semibold text-[#C4FF0D]">1-Klik</span>
                        </header>

                        <div class="space-y-2">
                            @foreach ($quickPrompts as $qp)
                                <button type="button"
                                        data-ai-quick-prompt="{{ $qp['prompt'] }}"
                                        class="w-full text-left rounded-2xl bg-[#181F11] border border-white/[0.07] hover:border-[#C4FF0D]/40 px-3.5 py-2.5 transition cursor-pointer flex items-center justify-between gap-3">
                                    <span class="text-xs sm:text-sm font-medium text-[#F4F7F0]">{{ $qp['label'] }}</span>
                                    <span class="text-xs text-[#C4FF0D] shrink-0">Tanya →</span>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    <!-- 3. CRITICAL SIGNALS TILE -->
                    <section class="bento-tile space-y-3">
                        <header class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-white">Sinyal Data yang Dibaca AI</h3>
                            <span class="text-[11px] text-[#9AA68E]">Otomatis</span>
                        </header>

                        <div class="space-y-2 text-xs">
                            @if (! empty($inv['produk_kritis']))
                                @foreach (array_slice($inv['produk_kritis'], 0, 3) as $pk)
                                    <div class="rounded-xl bg-[#181F11] border border-white/[0.06] px-3 py-2.5 flex items-center justify-between gap-2">
                                        <span class="text-white font-medium truncate">{{ $pk['nama'] }}</span>
                                        <span class="shrink-0 text-amber-300 font-semibold tabular-nums">
                                            Stok {{ $pk['stok'] }}/{{ $pk['stok_min'] }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p class="rounded-xl bg-[#181F11] border border-white/[0.06] px-3 py-2.5 text-[#C4FF0D]">
                                    Semua stok produk berada di atas batas minimum.
                                </p>
                            @endif

                            @if (! empty($kas['jatuh_tempo']))
                                @foreach (array_slice($kas['jatuh_tempo'], 0, 2) as $jt)
                                    <div class="rounded-xl bg-[#181F11] border border-white/[0.06] px-3 py-2.5 flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-white font-medium truncate">{{ $jt['nama_pihak'] }}</p>
                                            <p class="text-[11px] text-[#9AA68E]">{{ $jt['jenis'] }} · Rp {{ number_format($jt['sisa'], 0, ',', '.') }}</p>
                                        </div>
                                        <span class="shrink-0 font-semibold {{ $jt['overdue'] ? 'text-rose-400' : 'text-[#C084FC]' }}">
                                            {{ $jt['jatuh_tempo'] }}
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="pt-2 border-t border-white/[0.06] flex items-center justify-between text-xs">
                            <a href="/stok" class="text-[#C4FF0D] hover:underline font-medium">Cek Stok →</a>
                            <a href="/hutang-piutang" class="text-[#C084FC] hover:underline font-medium">Cek Kasbon →</a>
                        </div>
                    </section>
                </div>

            </div>
        </div>

        {{-- Skrip halaman ini di dalam <main> agar selalu ikut dieksekusi ulang oleh spa.js --}}
        <script>
            (function () {
                const form = document.getElementById('ai-chat-form');
                const input = document.getElementById('ai-prompt-input');
                const sendBtn = document.getElementById('ai-send-btn');
                const feed = document.getElementById('ai-chat-feed');
                const emptyState = document.getElementById('ai-empty-state');
                const typingEl = document.getElementById('ai-typing-indicator');
                const resetBtn = document.getElementById('ai-reset-btn');
                const countBadge = document.getElementById('ai-message-count');
                const charCounter = document.getElementById('ai-char-counter');

                if (!form || !input || !feed) return;

                let isSending = false;

                function escapeHtml(str) {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function formatInline(escaped) {
                    return escaped
                        .replace(/`([^`]+)`/g, '<code>$1</code>')
                        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                        .replace(/(^|[^*])\*([^*]+)\*(?!\*)/g, '$1<em>$2</em>');
                }

                function renderSafeMarkdown(rawText) {
                    const lines = escapeHtml(rawText).replace(/\r\n/g, '\n').split('\n');
                    let html = '';
                    let listType = null;

                    function closeList() {
                        if (listType) {
                            html += listType === 'ol' ? '</ol>' : '</ul>';
                            listType = null;
                        }
                    }

                    lines.forEach(function (line) {
                        const trimmed = line.trim();

                        if (trimmed === '') {
                            closeList();
                            return;
                        }

                        const headingMatch = trimmed.match(/^#{1,4}\s+(.+)$/);
                        if (headingMatch) {
                            closeList();
                            html += '<h4>' + formatInline(headingMatch[1]) + '</h4>';
                            return;
                        }

                        const ulMatch = trimmed.match(/^[-*•]\s+(.+)$/);
                        if (ulMatch) {
                            if (listType !== 'ul') {
                                closeList();
                                html += '<ul>';
                                listType = 'ul';
                            }
                            html += '<li>' + formatInline(ulMatch[1]) + '</li>';
                            return;
                        }

                        const olMatch = trimmed.match(/^(\d+)[.)]\s+(.+)$/);
                        if (olMatch) {
                            if (listType !== 'ol') {
                                closeList();
                                const startNum = parseInt(olMatch[1], 10);
                                html += startNum > 1 ? '<ol start="' + startNum + '">' : '<ol>';
                                listType = 'ol';
                            }
                            html += '<li>' + formatInline(olMatch[2]) + '</li>';
                            return;
                        }

                        closeList();
                        html += '<p>' + formatInline(trimmed) + '</p>';
                    });

                    closeList();
                    return html || '<p>' + formatInline(escapeHtml(rawText)) + '</p>';
                }

                function scrollToBottom() {
                    feed.scrollTop = feed.scrollHeight;
                }

                function updateMessageCount() {
                    const total = feed.querySelectorAll('[data-ai-msg]').length;
                    if (countBadge) countBadge.textContent = total + ' pesan';
                    if (resetBtn) {
                        if (total > 0) resetBtn.classList.remove('hidden');
                        else resetBtn.classList.add('hidden');
                    }
                    if (emptyState) {
                        if (total > 0) emptyState.classList.add('hidden');
                        else emptyState.classList.remove('hidden');
                    }
                }

                function showToast(icon, title) {
                    if (typeof Swal === 'undefined') return;
                    if (icon === 'success') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            iconColor: '#C4FF0D',
                            title: title,
                            background: '#12170D',
                            color: '#F4F7F0',
                            showConfirmButton: false,
                            timer: 2600,
                            timerProgressBar: true,
                            customClass: { popup: 'swal-toast-bento' }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: title,
                            background: '#12170D',
                            color: '#F4F7F0',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#252f1b'
                        });
                    }
                }

                function appendUserBubble(content, time) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex justify-end';
                    wrapper.setAttribute('data-ai-msg', '');
                    wrapper.innerHTML =
                        '<div class="max-w-[85%] sm:max-w-[78%] rounded-2xl rounded-br-md bg-[#C4FF0D] text-[#152208] px-4 py-3 shadow-[0_8px_24px_-10px_rgba(196,255,13,0.4)]">' +
                            '<p class="text-xs sm:text-sm font-medium whitespace-pre-wrap break-words">' + escapeHtml(content) + '</p>' +
                            '<p class="text-[10px] font-semibold opacity-65 text-right mt-1.5">' + escapeHtml(time) + '</p>' +
                        '</div>';
                    feed.insertBefore(wrapper, typingEl);
                    return wrapper;
                }

                function appendAiBubble(content, time) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-start gap-2.5';
                    wrapper.setAttribute('data-ai-msg', '');
                    wrapper.innerHTML =
                        '<span class="size-7 shrink-0 rounded-xl bg-[#181F11] border border-[#C4FF0D]/30 text-[#C4FF0D] text-[10px] font-bold grid place-items-center mt-0.5">AI</span>' +
                        '<div class="max-w-[88%] sm:max-w-[82%] rounded-2xl rounded-tl-md bg-[#181F11] border border-white/[0.08] px-4 py-3.5 text-xs sm:text-sm text-[#D7E0CE]">' +
                            '<div class="ai-markdown break-words">' + renderSafeMarkdown(content) + '</div>' +
                            '<p class="text-[10px] text-[#9AA68E]/75 mt-2">' + escapeHtml(time) + ' · AI</p>' +
                        '</div>';
                    feed.insertBefore(wrapper, typingEl);
                    return wrapper;
                }

                function setLoading(loading) {
                    isSending = loading;
                    input.disabled = loading;
                    if (sendBtn) sendBtn.disabled = loading;
                    if (typingEl) {
                        if (loading) {
                            typingEl.classList.remove('hidden');
                            typingEl.classList.add('flex');
                        } else {
                            typingEl.classList.add('hidden');
                            typingEl.classList.remove('flex');
                        }
                    }
                    scrollToBottom();
                }

                async function sendMessage(text) {
                    const clean = text.trim();
                    if (!clean || isSending) return;

                    const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
                    const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    if (emptyState) emptyState.classList.add('hidden');
                    const tempUserBubble = appendUserBubble(clean, nowTime);
                    input.value = '';
                    input.style.height = '';
                    if (charCounter) charCounter.textContent = '0/2000';
                    setLoading(true);

                    try {
                        const res = await fetch('/konsultasi-ai/kirim', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ pesan: clean })
                        });

                        const data = await res.json().catch(function () {
                            return { ok: false, message: 'Respons server tidak valid.' };
                        });

                        if (!res.ok || !data.ok) {
                            tempUserBubble.remove();
                            input.value = clean;
                            if (charCounter) charCounter.textContent = clean.length + '/2000';
                            showToast('error', data.message || 'Gagal memproses permintaan ke AI.');
                            updateMessageCount();
                            return;
                        }

                        appendAiBubble(data.ai_message.content, data.ai_message.time || nowTime);
                        updateMessageCount();
                        scrollToBottom();
                    } catch (_) {
                        tempUserBubble.remove();
                        input.value = clean;
                        if (charCounter) charCounter.textContent = clean.length + '/2000';
                        showToast('error', 'Koneksi terputus saat menghubungi server. Coba lagi.');
                        updateMessageCount();
                    } finally {
                        setLoading(false);
                        input.focus();
                    }
                }

                // Render Markdown untuk pesan AI yang sudah tersimpan dari database
                feed.querySelectorAll('[data-ai-raw]').forEach(function (el) {
                    el.innerHTML = renderSafeMarkdown(el.dataset.aiRaw || '');
                });
                updateMessageCount();
                scrollToBottom();

                input.addEventListener('input', function () {
                    if (charCounter) charCounter.textContent = input.value.length + '/2000';
                    input.style.height = 'auto';
                    input.style.height = Math.min(input.scrollHeight, 128) + 'px';
                });

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        form.requestSubmit();
                    }
                });

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    sendMessage(input.value);
                });

                document.querySelectorAll('[data-ai-quick-prompt]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const promptText = this.getAttribute('data-ai-quick-prompt') || '';
                        if (!promptText || isSending) return;
                        sendMessage(promptText);
                    });
                });

                if (resetBtn) {
                    resetBtn.addEventListener('click', function () {
                        if (isSending) return;

                        const doReset = async function () {
                            const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
                            try {
                                const res = await fetch('/konsultasi-ai/reset', {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    }
                                });
                                if (res.ok) {
                                    feed.querySelectorAll('[data-ai-msg]').forEach(function (n) { n.remove(); });
                                    updateMessageCount();
                                    showToast('success', 'Riwayat konsultasi berhasil dibersihkan.');
                                } else {
                                    showToast('error', 'Gagal menghapus riwayat konsultasi.');
                                }
                            } catch (_) {
                                showToast('error', 'Koneksi gagal saat mereset percakapan.');
                            }
                        };

                        window.alpetbizzConfirm({
                            title: 'Reset Riwayat Chat?',
                            text: 'Seluruh percakapan konsultasi AI di akun ini akan dihapus untuk memulai sesi baru.',
                            confirmButtonText: 'Ya, reset'
                        }, doReset);
                    });
                }
            })();
        </script>
@endsection
