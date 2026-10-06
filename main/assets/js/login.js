/* login.js — AlpetBizz Hero Scroll-Scrub + Login Panel */
'use strict';

/* ── Login panel toggle ──────────────────────────────────── */
function openLogin() {
    document.getElementById('login-panel').classList.add('open');
    var inp = document.getElementById('login-panel').querySelector('input');
    if (inp) setTimeout(function () { inp.focus(); }, 420);
}

function closeLogin() {
    document.getElementById('login-panel').classList.remove('open');
}

document.getElementById('login-panel').addEventListener('click', function (e) {
    if (e.target === this) closeLogin();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeLogin();
});

/* ── Password visibility toggle ─────────────────────────── */
var EYE_OPEN   = '<svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
var EYE_CLOSED = '<svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>';

function togglePw(id, btn) {
    var inp = document.getElementById(id);
    if (!inp) return;
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    btn.innerHTML = show ? EYE_OPEN : EYE_CLOSED;
}

/* ── Scroll-scrub engine ─────────────────────────────────── */
(function () {
    /* Config */
    var SCRUB   = 2800;  /* px of input needed to reach progress = 1 */
    var LERP    = 0.14;  /* lerp smoothing factor per frame */
    var V_END   = 0.78;  /* progress where video is fully scrubbed */
    var B_START = 0.80;  /* brand mark fade-in start */
    var B_END   = 0.93;  /* brand mark fade-in end */
    var S_START = 0.85;  /* skyline fade-in start */
    var S_END   = 0.96;  /* skyline fade-in end */

    /* DOM refs */
    var video   = document.getElementById('hero-video');
    var titleEl = document.getElementById('hero-title');
    var hint    = document.getElementById('hero-scroll-hint');
    var brand   = document.getElementById('hero-brand');
    var skyline = document.getElementById('hero-skyline');
    var progBar = document.getElementById('hero-progress-bar');

    /* State */
    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion:reduce)').matches);
    var dur = 0, raf = 0;
    var target = 0, cur = 0;
    var started = false;
    var seeking = false, queued = null;
    var locked  = false, lockY  = 0;
    var touchY  = 0;
    var done    = false;

    function clamp(v, a, b) { return v < a ? a : v > b ? b : v; }

    /* Queue overlapping seeks — prevents browser stalling */
    function seek(t) {
        if (seeking) { queued = t; return; }
        seeking = true;
        video.currentTime = t;
    }

    /* Pin body to block native scroll while hero is active */
    function lock() {
        if (locked) return;
        locked = true;
        lockY = window.scrollY;
        var s = document.body.style;
        s.position = 'fixed'; s.top = '-' + lockY + 'px';
        s.left = '0'; s.right = '0'; s.width = '100%';
    }

    function unlock() {
        if (!locked) return;
        locked = false;
        var y = lockY;
        var s = document.body.style;
        s.position = s.top = s.left = s.right = s.width = '';
        window.scrollTo(0, y);
    }

    /* Called once when scrub reaches 100% */
    function finish() {
        if (done) return;
        done = true;
        cancelAnimationFrame(raf);
        unlock();
        window.removeEventListener('wheel',      onWheel);
        window.removeEventListener('touchstart', onTS);
        window.removeEventListener('touchmove',  onTM);

        hint.style.display    = 'none';
        titleEl.style.display = 'none';

        /* Reveal Masuk button in navbar */
        var btn = document.getElementById('nav-login-btn');
        if (btn) btn.classList.add('visible');

        /* Auto-open form if server flagged a login error */
        if (window.LOGIN_HAS_ERROR) openLogin();
    }

    /* ── Input handlers ──────────────────────────────────── */
    function addDelta(dy) {
        target = clamp(target + dy / SCRUB, 0, 1);
        if (target > 0.001) started = true;
    }

    function onWheel(e) { addDelta(e.deltaY); e.preventDefault(); }
    function onTS(e)    { touchY = e.touches[0] ? e.touches[0].clientY : 0; }
    function onTM(e) {
        var y = e.touches[0] ? e.touches[0].clientY : touchY;
        addDelta(touchY - y);
        touchY = y;
        e.preventDefault();
    }

    /* ── RAF animation loop ──────────────────────────────── */
    function frame() {
        cur += (target - cur) * LERP;

        /* Video scrub */
        if (dur > 0) seek(clamp(cur / V_END, 0, 1) * dur);

        /* Title: fade + slide out */
        var tT = 1 - clamp(cur / 0.28, 0, 1);
        titleEl.style.opacity   = tT;
        titleEl.style.transform = 'translateY(' + ((1 - tT) * -20) + 'px)';

        /* Scroll hint: hide on first movement */
        hint.style.opacity = started ? '0' : '1';

        /* Brand mark: fade + slide in */
        var tB = clamp((cur - B_START) / (B_END - B_START), 0, 1);
        brand.style.opacity   = tB;
        brand.style.transform = 'translateY(' + ((1 - tB) * 14) + 'px)';

        /* Skyline cutout */
        skyline.style.opacity = clamp((cur - S_START) / (S_END - S_START), 0, 1);

        /* Progress bar */
        progBar.style.transform = 'scaleX(' + cur + ')';

        if (cur >= 0.999) { finish(); return; }
        raf = requestAnimationFrame(frame);
    }

    /* ── Boot ────────────────────────────────────────────── */
    video.addEventListener('loadeddata', function () {
        dur = video.duration || 0;
        video.style.opacity = '1';

        if (reduceMotion) {
            if (dur) video.currentTime = dur;
            finish();
            return;
        }

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

    /* Graceful fallback: skip hero if video fails */
    video.addEventListener('error', function () { finish(); });
}());
