<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

if (!isset($_SESSION['users'])) {
    $_SESSION['users']['admin'] = [
        'nama'       => 'Administrator',
        'username'   => 'admin',
        'email'      => 'admin@gmail.com',
        'password'   => 'admin123',
        'no_hp'      => '08123456789',
        'nama_usaha' => 'AlpetBizz Store',
        'kategori'   => 'Makanan & Minuman',
        'alamat'     => 'Malang',
    ];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['username'] ?? '');
    $pw = $_POST['password'] ?? '';
    foreach ($_SESSION['users'] as $uname => $data) {
        if (($uname === $id || strtolower($data['email'] ?? '') === strtolower($id)) && ($data['password'] ?? '') === $pw) {
            $_SESSION['user']      = $uname;
            $_SESSION['user_nama'] = $data['nama'] ?? 'User';
            $_SESSION['profil']    = [
                'no_hp'      => $data['no_hp']      ?? '',
                'nama_usaha' => $data['nama_usaha'] ?? '',
                'kategori'   => $data['kategori']   ?? '',
                'alamat'     => $data['alamat']     ?? '',
            ];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Email / Username atau kata sandi salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpetBizz | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']}}}}</script>
    <link rel="stylesheet" href="assets/css/animations.css">
    <style>
        html, body { margin:0; padding:0; overflow:hidden; height:100%; }

        /* ── Scroll hint ───────────────── */
        @keyframes hero-bounce {
            0%, 100% { transform:translateY(0);   opacity:.5; }
            50%       { transform:translateY(5px); opacity:1;  }
        }
        #hero-scroll-hint svg { animation:hero-bounce 1.6s ease-in-out infinite; }

        /* ── Will-change hints ─────────── */
        #login-panel {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 11;
            pointer-events: none;
            /* dark scrim */
            background: rgba(0,0,0,0);
            transition: background .35s ease;
        }
        #login-panel.open {
            pointer-events: auto;
            background: rgba(0,0,0,.38);
        }
        #login-panel-card {
            width: 100%;
            max-width: 400px;
            background: rgba(18, 9, 3, 0.78);
            backdrop-filter: blur(6px) saturate(130%);
            -webkit-backdrop-filter: blur(6px) saturate(130%);
            border-radius: 1.375rem;
            box-shadow: 0 8px 48px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,200,100,.15);
            border: 1px solid rgba(255,180,80,.22);
            padding: clamp(1.375rem, 4vw, 2.25rem);
            transform: translateY(24px) scale(.97);
            opacity: 0;
            transition: transform .4s cubic-bezier(.16,1,.3,1), opacity .35s ease;
        }
        #login-panel.open #login-panel-card {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        /* ── Input placeholder ─────────── */
        ::placeholder { color: rgba(255,220,160,.45); }

        /* ── Will-change hints ─────────── */
        #hero-title, #hero-brand, #hero-skyline { will-change: opacity, transform; }
        #hero-progress-bar { will-change: transform; }
    </style>
</head>
<body class="font-sans antialiased">

<!-- ═══════════════════════════════════════════════════════════
     HERO — fullscreen scroll-scrub video
     ═══════════════════════════════════════════════════════════ -->
<div id="hero-section" style="position:fixed;inset:0;background:#1a0f0a;overflow:hidden;">

    <video id="hero-video" muted playsinline preload="auto"
        src="https://cdn.21st.dev/assets/mirror/23/234bc821170e75a6b8d2e42a952078f858eb054a51fab2a5baa928a10fdc245d.mp4"
        style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .6s ease;">
    </video>

    <img id="hero-skyline" alt="" aria-hidden="true"
        src="https://cdn.21st.dev/assets/mirror/97/97fe4402ea1d5d05c2b82befe6dbf73da1d6669b995e6d5ae9f2ebea68b1107a.png"
        style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;pointer-events:none;z-index:2;">

    <!-- Vignette -->
    <div aria-hidden="true" style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(26,15,10,.4),rgba(26,15,10,0) 30%,rgba(26,15,10,.2) 70%,rgba(26,15,10,.65));pointer-events:none;z-index:3;"></div>

    <!-- Brand mark -->
    <div id="hero-brand" aria-hidden="true" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:0 8% 22vh;text-align:center;opacity:0;pointer-events:none;z-index:1;">
        <span style="font-weight:800;font-size:clamp(48px,11vw,180px);line-height:1.05;letter-spacing:-.01em;color:#fff4e8;text-shadow:0 6px 40px rgba(0,0,0,.5);">AlpetBizz</span>
    </div>

    <!-- Welcome title -->
    <div id="hero-title" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:0 6%;text-align:center;pointer-events:none;z-index:3;">
        <span style="font-weight:800;font-size:clamp(22px,5vw,60px);line-height:1.25;letter-spacing:-.01em;color:#fff4e8;text-shadow:0 3px 20px rgba(0,0,0,.55);">Selamat Datang di AlpetBizz</span>
    </div>

    <!-- Pill navbar — white background, Masuk button integrated -->
    <nav id="site-nav" aria-label="Site navigation" style="position:absolute;top:clamp(14px,3vh,28px);left:50%;transform:translateX(-50%);display:flex;align-items:center;gap:clamp(10px,1.8vw,20px);padding:.5rem .5rem .5rem 1.25rem;border-radius:999px;background:rgba(255,255,255,0.92);backdrop-filter:blur(12px) saturate(140%);-webkit-backdrop-filter:blur(12px) saturate(140%);border:1px solid rgba(0,0,0,.06);box-shadow:0 4px 24px rgba(0,0,0,.14);z-index:5;white-space:nowrap;">
        <span style="font-weight:800;font-size:clamp(12px,1.3vw,14px);color:#1a0f0a;letter-spacing:-.01em;">AlpetBizz</span>
        <span aria-hidden="true" style="width:1px;height:14px;background:rgba(0,0,0,.12);display:inline-block;"></span>
        <span style="font-weight:500;font-size:clamp(11px,1.1vw,13px);color:rgba(26,15,10,.5);">Manajemen Usaha</span>
        <button id="nav-login-btn" onclick="openLogin()"
            style="display:flex;align-items:center;gap:.4rem;padding:.45rem 1rem;background:linear-gradient(135deg,#c96a1e,#a84f0e);color:#fff4e8;border:none;border-radius:999px;font-size:clamp(11px,1.2vw,13px);font-weight:700;font-family:inherit;cursor:pointer;box-shadow:0 2px 10px rgba(150,60,8,.4),inset 0 1px 0 rgba(255,190,80,.2);transition:background .2s,box-shadow .2s,transform .12s,opacity .5s ease;white-space:nowrap;opacity:0;pointer-events:none;"
            onmouseover="this.style.background='linear-gradient(135deg,#d97822,#b85c12)';this.style.boxShadow='0 4px 16px rgba(150,60,8,.55)'"
            onmouseout="this.style.background='linear-gradient(135deg,#c96a1e,#a84f0e)';this.style.boxShadow='0 2px 10px rgba(150,60,8,.4),inset 0 1px 0 rgba(255,190,80,.2)'"
            onmousedown="this.style.transform='scale(.95)'" onmouseup="this.style.transform='scale(1)'">
            Masuk
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </nav>

    <!-- Scroll hint -->
    <div id="hero-scroll-hint" style="position:absolute;left:50%;bottom:clamp(18px,5vh,44px);transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:7px;color:rgba(255,244,232,.7);font-size:clamp(10px,1.3vw,12px);font-weight:600;letter-spacing:.3em;transition:opacity .35s ease;pointer-events:none;z-index:5;">
        <span>SCROLL</span>
        <svg width="14" height="18" viewBox="0 0 14 18" aria-hidden="true">
            <path d="M7 1 L7 17 M2 12 L7 17 L12 12" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <!-- Progress bar -->
    <div aria-hidden="true" style="position:absolute;left:0;right:0;bottom:0;height:2px;background:rgba(255,244,232,.1);z-index:5;">
        <div id="hero-progress-bar" style="height:100%;width:100%;background:linear-gradient(90deg,rgba(255,244,232,.4),rgba(255,244,232,.9));transform:scaleX(0);transform-origin:left center;"></div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     LOGIN PANEL — slide-in overlay, toggled by navbar Masuk button
     ═══════════════════════════════════════════════════════════ -->
<div id="login-panel" onclick="handlePanelClick(event)" aria-modal="true" role="dialog" aria-label="Form Login">
    <div id="login-panel-card">

        <!-- Header + close -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.375rem;">
            <div>
                <h1 style="margin:0 0 .25rem;font-size:1.25rem;font-weight:800;color:#fff4e8;letter-spacing:-.02em;">Masuk ke Akun</h1>
                <p style="margin:0;font-size:.75rem;color:rgba(255,210,140,.7);">Kelola keuangan dan stok usaha Anda di satu tempat.</p>
            </div>
            <button onclick="closeLogin()" aria-label="Tutup form login"
                style="flex-shrink:0;margin-left:.75rem;padding:.375rem;background:rgba(255,200,100,.08);border:1px solid rgba(255,180,80,.18);border-radius:.5rem;color:rgba(255,210,140,.7);cursor:pointer;transition:background .2s,color .2s;line-height:0;"
                onmouseover="this.style.background='rgba(255,180,60,.18)';this.style.color='#fff4e8'"
                onmouseout="this.style.background='rgba(255,200,100,.08)';this.style.color='rgba(255,210,140,.7)'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <?php if ($error): ?>
        <div style="display:flex;align-items:center;gap:.5rem;background:rgba(220,60,40,.2);border:1px solid rgba(220,80,60,.38);border-radius:.75rem;padding:.625rem .875rem;margin-bottom:1rem;">
            <svg style="width:1rem;height:1rem;flex-shrink:0;color:#fca5a5;" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span style="font-size:.75rem;font-weight:500;color:#fca5a5;"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" style="display:flex;flex-direction:column;gap:1rem;">

            <!-- Email / Username -->
            <div>
                <label style="display:block;font-size:.7rem;font-weight:700;color:rgba(255,220,150,.9);margin-bottom:.4rem;letter-spacing:.05em;text-transform:uppercase;">Email atau Username</label>
                <input type="text" name="username" required autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="Masukkan email / username"
                    style="width:100%;box-sizing:border-box;padding:.65rem .9rem;background:rgba(255,220,150,.09);border:1px solid rgba(255,190,100,.3);border-radius:.75rem;color:#fff4e8;font-size:.875rem;outline:none;transition:border-color .18s ease,box-shadow .18s ease;font-family:inherit;"
                    onfocus="this.style.borderColor='rgba(255,160,60,.7)';this.style.boxShadow='0 0 0 3px rgba(200,100,30,.22)'"
                    onblur="this.style.borderColor='rgba(255,190,100,.3)';this.style.boxShadow='none'">
            </div>

            <!-- Password -->
            <div>
                <label style="display:block;font-size:.7rem;font-weight:700;color:rgba(255,220,150,.9);margin-bottom:.4rem;letter-spacing:.05em;text-transform:uppercase;">Kata Sandi (Password)</label>
                <div style="position:relative;">
                    <input type="password" id="loginPassword" name="password" required
                        placeholder="Masukkan kata sandi" autocomplete="current-password"
                        style="width:100%;box-sizing:border-box;padding:.65rem 2.75rem .65rem .9rem;background:rgba(255,220,150,.09);border:1px solid rgba(255,190,100,.3);border-radius:.75rem;color:#fff4e8;font-size:.875rem;outline:none;transition:border-color .18s ease,box-shadow .18s ease;font-family:inherit;"
                        onfocus="this.style.borderColor='rgba(255,160,60,.7)';this.style.boxShadow='0 0 0 3px rgba(200,100,30,.22)'"
                        onblur="this.style.borderColor='rgba(255,190,100,.3)';this.style.boxShadow='none'">
                    <button type="button" onclick="togglePw('loginPassword',this)"
                        style="position:absolute;right:.65rem;top:50%;transform:translateY(-50%);padding:.35rem;color:rgba(255,200,120,.6);background:none;border:none;cursor:pointer;border-radius:.5rem;display:flex;align-items:center;transition:color .2s;"
                        aria-label="Toggle Password"
                        onmouseover="this.style.color='rgba(255,210,130,1)'" onmouseout="this.style.color='rgba(255,200,120,.6)'">
                        <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                style="width:100%;padding:.75rem;background:linear-gradient(135deg,#c96a1e,#a84f0e);color:#fff4e8;border:none;border-radius:.75rem;font-size:.9rem;font-weight:700;font-family:inherit;cursor:pointer;box-shadow:0 4px 20px rgba(160,70,10,.45),inset 0 1px 0 rgba(255,200,100,.2);transition:background .2s,box-shadow .2s,transform .12s;letter-spacing:.01em;"
                onmouseover="this.style.background='linear-gradient(135deg,#d97822,#b85c12)';this.style.boxShadow='0 6px 28px rgba(160,70,10,.6),inset 0 1px 0 rgba(255,200,100,.25)'"
                onmouseout="this.style.background='linear-gradient(135deg,#c96a1e,#a84f0e)';this.style.boxShadow='0 4px 20px rgba(160,70,10,.45),inset 0 1px 0 rgba(255,200,100,.2)'"
                onmousedown="this.style.transform='scale(.98)'" onmouseup="this.style.transform='scale(1)'">
                Masuk Sekarang
            </button>
        </form>

        <div style="margin-top:1.125rem;padding-top:1rem;border-top:1px solid rgba(255,180,80,.14);text-align:center;">
            <span style="font-size:.75rem;color:rgba(255,210,130,.55);">Belum memiliki akun? </span>
            <a href="register.php"
                style="font-size:.75rem;font-weight:700;color:rgba(255,185,70,.9);text-decoration:none;transition:color .2s;"
                onmouseover="this.style.color='#ffd060'" onmouseout="this.style.color='rgba(255,185,70,.9)'">Daftar sekarang</a>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════════════════════ -->
<script>
/* ── CTA bar + login panel toggle ─────────────────────────── */
function openLogin() {
    document.getElementById('login-panel').classList.add('open');
    document.getElementById('cta-bar').classList.remove('visible'); /* hide bar while form open */
    var inp = document.getElementById('login-panel').querySelector('input');
    if (inp) setTimeout(function(){ inp.focus(); }, 420);
}
function closeLogin() {
    document.getElementById('login-panel').classList.remove('open');
    document.getElementById('cta-bar').classList.add('visible');
}
function handlePanelClick(e) {
    /* Close when clicking the dark scrim (not the card itself) */
    if (e.target === document.getElementById('login-panel')) closeLogin();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLogin();
});

/* ── Password toggle ──────────────────────────────────────── */
function togglePw(id, btn) {
    var inp = document.getElementById(id);
    if (!inp) return;
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Sembunyikan' : 'Tampilkan');
    btn.innerHTML = show
        ? '<svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
        : '<svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>';
}

/* ── Scroll-scrub engine ──────────────────────────────────── */
(function () {
    'use strict';

    var SCRUB   = 2800;
    var LERP    = 0.14;
    var V_END   = 0.78;
    var B_START = 0.80;
    var B_END   = 0.93;
    var S_START = 0.85;
    var S_END   = 0.96;

    var video   = document.getElementById('hero-video');
    var titleEl = document.getElementById('hero-title');
    var hint    = document.getElementById('hero-scroll-hint');
    var brand   = document.getElementById('hero-brand');
    var skyline = document.getElementById('hero-skyline');
    var progBar = document.getElementById('hero-progress-bar');

    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion:reduce)').matches);
    var dur = 0, raf = 0;
    var target = 0, cur = 0;
    var started = false;
    var seeking = false, queued = null;
    var locked = false, lockY = 0;
    var touchY = 0;
    var done = false;

    function clamp(v,a,b){ return v<a?a:v>b?b:v; }

    function seek(t) {
        if (seeking) { queued = t; return; }
        seeking = true;
        video.currentTime = t;
    }

    function lock() {
        if (locked) return;
        locked = true; lockY = window.scrollY;
        var s = document.body.style;
        s.position='fixed'; s.top='-'+lockY+'px'; s.left='0'; s.right='0'; s.width='100%';
    }

    function unlock() {
        if (!locked) return;
        locked = false;
        var y = lockY;
        var s = document.body.style;
        s.position=s.top=s.left=s.right=s.width='';
        window.scrollTo(0, y);
    }

    function finish() {
        if (done) return;
        done = true;
        cancelAnimationFrame(raf);
        unlock();
        window.removeEventListener('wheel',      onWheel);
        window.removeEventListener('touchstart', onTS);
        window.removeEventListener('touchmove',  onTM);
        hint.style.display = 'none';
        titleEl.style.display = 'none';
        /* Reveal the Masuk button inside navbar */
        var btn = document.getElementById('nav-login-btn');
        if (btn) { btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }
        /* Auto-open login if PHP returned an error (form was submitted) */
        <?php if ($error): ?>
        openLogin();
        <?php endif; ?>
    }

    function delta(dy) {
        target = clamp(target + dy / SCRUB, 0, 1);
        if (target > 0.001) started = true;
    }
    function onWheel(e) { delta(e.deltaY); e.preventDefault(); }
    function onTS(e) { touchY = e.touches[0] ? e.touches[0].clientY : 0; }
    function onTM(e) {
        var y = e.touches[0] ? e.touches[0].clientY : touchY;
        delta(touchY - y); touchY = y; e.preventDefault();
    }

    function frame() {
        cur += (target - cur) * LERP;

        if (dur > 0) seek(clamp(cur / V_END, 0, 1) * dur);

        var tT = 1 - clamp(cur / 0.28, 0, 1);
        titleEl.style.opacity   = tT;
        titleEl.style.transform = 'translateY(' + ((1 - tT) * -20) + 'px)';

        hint.style.opacity = started ? '0' : '1';

        var tB = clamp((cur - B_START) / (B_END - B_START), 0, 1);
        brand.style.opacity   = tB;
        brand.style.transform = 'translateY(' + ((1 - tB) * 14) + 'px)';

        skyline.style.opacity = clamp((cur - S_START) / (S_END - S_START), 0, 1);

        progBar.style.transform = 'scaleX(' + cur + ')';

        if (cur >= 0.999) { finish(); return; }
        raf = requestAnimationFrame(frame);
    }

    video.addEventListener('loadeddata', function () {
        dur = video.duration || 0;
        video.style.opacity = '1';
        if (reduceMotion) { if (dur) video.currentTime = dur; finish(); return; }
        lock();
        window.addEventListener('wheel',      onWheel, { passive: false });
        window.addEventListener('touchstart', onTS,    { passive: true  });
        window.addEventListener('touchmove',  onTM,    { passive: false });
        raf = requestAnimationFrame(frame);
    });

    video.addEventListener('seeked', function () {
        seeking = false;
        if (queued !== null) {
            var t = queued; queued = null;
            seeking = true; video.currentTime = t;
        }
    });

    video.addEventListener('error', function () { finish(); });
})();
</script>
</body>
</html>
