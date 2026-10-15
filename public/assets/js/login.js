/* login.js — AlpetBizz Auth Modal & Scroll-Scrub Engine */
'use strict';

/* ── Tab & Modal Switching ───────────────────────────────── */
function switchTab(tab) {
    var card = document.getElementById('login-panel-card');
    var loginTab = document.getElementById('tab-login-content');
    var regTab = document.getElementById('tab-register-content');

    if (tab === 'register') {
        if (loginTab) loginTab.style.display = 'none';
        if (regTab) regTab.style.display = 'block';
        if (card) card.classList.add('card-wide');
        var firstInp = regTab ? regTab.querySelector('input') : null;
        if (firstInp) setTimeout(function () { firstInp.focus(); }, 150);
    } else {
        if (regTab) regTab.style.display = 'none';
        if (loginTab) loginTab.style.display = 'block';
        if (card) card.classList.remove('card-wide');
        var firstInp = loginTab ? loginTab.querySelector('input') : null;
        if (firstInp) setTimeout(function () { firstInp.focus(); }, 150);
    }
}

function openLogin(tab) {
    if (tab) switchTab(tab);
    var panel = document.getElementById('login-panel');
    if (panel) panel.classList.add('open');
}

function closeLogin() {
    var panel = document.getElementById('login-panel');
    if (panel) panel.classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('login-panel');
    if (panel) {
        panel.addEventListener('click', function (e) {
            if (e.target === this) closeLogin();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLogin();
    });
});

/* ── Password Visibility Toggle ─────────────────────────── */
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

/* ── Scroll-Scrub Hero Engine ────────────────────────────── */
(function () {
    var SCRUB    = 2800;  /* px input needed for full scrub */
    var LERP     = 0.22;  /* frame smoothing factor         */
    var SEEK_MIN = 0.05;  /* min seconds delta to issue seek*/
    var V_END    = 0.78;
    var B_START  = 0.80;
    var B_END    = 0.93;
    var S_START  = 0.85;
    var S_END    = 0.96;

    var video   = document.getElementById('hero-video');
    var titleEl = document.getElementById('hero-title');
    var hint    = document.getElementById('hero-scroll-hint');
    var brand   = document.getElementById('hero-brand');
    var skyline = document.getElementById('hero-skyline');
    var progBar = document.getElementById('hero-progress-bar');
    var navBtn  = document.getElementById('nav-login-btn');

    if (!video) return;

    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion:reduce)').matches);
    var dur = 0, raf = 0;
    var target = 0, cur = 0;
    var started   = false;
    var seeking   = false, queued = null, lastT = -1;
    var locked    = false, lockY  = 0;
    var touchY    = 0;
    var done      = false;
    var rafActive = false;

    function clamp(v, a, b) { return v < a ? a : v > b ? b : v; }

    /* Fast, throttled video seeking */
    function seek(t) {
        if (Math.abs(t - lastT) < SEEK_MIN) return;
        if (seeking) { queued = t; return; }
        seeking = true;
        lastT = t;
        if ('fastSeek' in video) {
            video.fastSeek(t);
        } else {
            video.currentTime = t;
        }
    }

    function lock() {
        if (locked) return;
        locked = true;
        lockY = window.scrollY;
        var s = document.body.style;
        s.position = 'fixed';
        s.top = '-' + lockY + 'px';
        s.left = '0';
        s.right = '0';
        s.width = '100%';
    }

    function unlock() {
        if (!locked) return;
        locked = false;
        var y = lockY;
        var s = document.body.style;
        s.position = s.top = s.left = s.right = s.width = '';
        window.scrollTo(0, y);
    }

    function startRAF() {
        if (rafActive || done) return;
        rafActive = true;
        raf = requestAnimationFrame(frame);
    }

    function finish() {
        if (done) return;
        done = true;
        rafActive = false;
        cancelAnimationFrame(raf);
        unlock();

        window.removeEventListener('wheel',      onWheel);
        window.removeEventListener('touchstart', onTS);
        window.removeEventListener('touchmove',  onTM);

        if (hint)    hint.style.display    = 'none';
        if (titleEl) titleEl.style.display = 'none';
        if (navBtn)  navBtn.classList.add('visible');

        /* If video duration is available, seek to last frame */
        if (dur > 0) seek(dur * 0.99);

        /* Auto-open modal if requested (e.g. from error, direct register link, etc) */
        if (window.AUTO_OPEN_PANEL) {
            openLogin(window.ACTIVE_TAB || 'login');
        }
    }

    function addDelta(dy) {
        target = clamp(target + dy / SCRUB, 0, 1);
        if (target > 0.001) started = true;
        startRAF();
    }

    function onWheel(e) {
        addDelta(e.deltaY);
        e.preventDefault();
    }

    function onTS(e) {
        touchY = e.touches[0] ? e.touches[0].clientY : 0;
    }

    function onTM(e) {
        var y = e.touches[0] ? e.touches[0].clientY : touchY;
        addDelta(touchY - y);
        touchY = y;
        e.preventDefault();
    }

    function frame() {
        cur += (target - cur) * LERP;

        /* Video seek */
        if (dur > 0) seek(clamp(cur / V_END, 0, 1) * dur);

        /* Title fade out (first 28%) */
        var tT = 1 - clamp(cur / 0.28, 0, 1);
        if (titleEl) {
            titleEl.style.opacity   = tT;
            titleEl.style.transform = 'translateY(' + ((1 - tT) * -20) + 'px)';
        }

        /* Scroll hint hides as soon as user begins scrolling */
        if (hint) hint.style.opacity = started ? '0' : '1';

        /* Brand mark fade in (80% - 93%) */
        var tB = clamp((cur - B_START) / (B_END - B_START), 0, 1);
        if (brand) {
            brand.style.opacity   = tB;
            brand.style.transform = 'translateY(' + ((1 - tB) * 14) + 'px)';
        }

        /* Skyline silhouette (85% - 96%) */
        if (skyline) skyline.style.opacity = clamp((cur - S_START) / (S_END - S_START), 0, 1);

        /* Progress bar */
        if (progBar) progBar.style.transform = 'scaleX(' + cur + ')';

        if (cur >= 0.998) {
            finish();
            return;
        }

        /* Idle check: stop loop when converged to save CPU */
        if (Math.abs(target - cur) < 0.0004) {
            rafActive = false;
            return;
        }

        raf = requestAnimationFrame(frame);
    }

    /* Reliable boot sequence (handles cached video correctly) */
    function initHero() {
        dur = video.duration || 0;
        video.style.opacity = '1';

        if (reduceMotion || window.SKIP_HERO) {
            finish();
            return;
        }

        lock();
        window.addEventListener('wheel',      onWheel, { passive: false });
        window.addEventListener('touchstart', onTS,    { passive: true  });
        window.addEventListener('touchmove',  onTM,    { passive: false });

        /* Render initial visual state */
        frame();
    }

    if (video.readyState >= 2) {
        initHero();
    } else {
        video.addEventListener('loadeddata', initHero);
        /* Fallback if loadeddata doesn't fire */
        video.addEventListener('canplay', function () {
            if (!dur) initHero();
        });
    }

    video.addEventListener('seeked', function () {
        seeking = false;
        if (queued !== null) {
            var t = queued;
            queued = null;
            seeking = true;
            lastT = t;
            video.currentTime = t;
        }
    });

    video.addEventListener('error', finish);
}());
