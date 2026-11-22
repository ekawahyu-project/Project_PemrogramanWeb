/* login.js — Parallax × Overlapping Scroll Stack × Velocity Scroll Physics & Unified Auth Modal */
'use strict';

function switchTab(tab) {
    var card = document.getElementById('login-panel-card');
    var loginTab = document.getElementById('tab-login-content');
    var regTab = document.getElementById('tab-register-content');

    if (tab === 'register') {
        if (loginTab) loginTab.style.display = 'none';
        if (regTab) regTab.style.display = 'block';
        if (card) card.classList.add('card-wide');
        var firstInp = regTab ? regTab.querySelector('input:not([type="hidden"])') : null;
        if (firstInp) setTimeout(function () { firstInp.focus(); }, 120);
    } else {
        if (regTab) regTab.style.display = 'none';
        if (loginTab) loginTab.style.display = 'block';
        if (card) card.classList.remove('card-wide');
        var firstInp = loginTab ? loginTab.querySelector('input:not([type="hidden"])') : null;
        if (firstInp) setTimeout(function () { firstInp.focus(); }, 120);
    }
}

function openLogin(tab) {
    if (tab) switchTab(tab);
    var panel = document.getElementById('login-panel');
    if (panel) {
        panel.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeLogin() {
    var panel = document.getElementById('login-panel');
    if (panel) {
        panel.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function startFromHeroCta() {
    var heroInput = document.getElementById('hero-quick-email');
    var val = heroInput ? heroInput.value.trim() : '';
    if (val) {
        var loginUser = document.getElementById('inp-username');
        var regEmail = document.getElementById('r-email');
        if (loginUser && !loginUser.value) loginUser.value = val;
        if (regEmail && val.includes('@') && !regEmail.value) regEmail.value = val;
    }
    openLogin('login');
}

var EYE_OPEN = '<svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
var EYE_CLOSED = '<svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>';

function togglePw(id, btn) {
    var inp = document.getElementById(id);
    if (!inp) return;
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    btn.innerHTML = show ? EYE_OPEN : EYE_CLOSED;
}

document.addEventListener('DOMContentLoaded', function () {
    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 1. Auth Modal Events
    var panel = document.getElementById('login-panel');
    if (panel) {
        panel.addEventListener('click', function (e) {
            if (e.target === this) closeLogin();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLogin();
    });

    var heroInput = document.getElementById('hero-quick-email');
    if (heroInput) {
        heroInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                startFromHeroCta();
            }
        });
    }

    if (window.AUTO_OPEN_PANEL) {
        openLogin(window.ACTIVE_TAB || 'login');
    }

    // 2. Custom Bento Select in Register Modal
    document.querySelectorAll('select:not([data-bento-select-ready])').forEach(function (selectEl) {
        selectEl.setAttribute('data-bento-select-ready', 'true');
        var wrapper = document.createElement('div');
        wrapper.className = 'bento-select-wrapper';
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'bento-select-trigger';
        var labelSpan = document.createElement('span');
        labelSpan.className = 'truncate';
        var selectedOpt = selectEl.options[selectEl.selectedIndex];
        labelSpan.textContent = selectedOpt ? selectedOpt.textContent : 'Pilih...';
        var chevron = document.createElement('span');
        chevron.innerHTML = '<svg class="bento-select-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"/></svg>';
        trigger.appendChild(labelSpan);
        trigger.appendChild(chevron.firstElementChild);
        var menu = document.createElement('div');
        menu.className = 'bento-select-menu';

        function buildOptions() {
            menu.innerHTML = '';
            Array.from(selectEl.options).forEach(function (opt, idx) {
                var item = document.createElement('button');
                item.type = 'button';
                var isSelected = idx === selectEl.selectedIndex;
                item.className = 'bento-select-option' + (isSelected ? ' selected' : '');
                item.textContent = opt.textContent;
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectEl.selectedIndex = idx;
                    labelSpan.textContent = opt.textContent;
                    buildOptions();
                    wrapper.classList.remove('open');
                });
                menu.appendChild(item);
            });
        }
        buildOptions();
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            wrapper.classList.toggle('open');
        });
        document.addEventListener('click', function () {
            wrapper.classList.remove('open');
        });
        selectEl.style.display = 'none';
        selectEl.parentNode.insertBefore(wrapper, selectEl);
        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        wrapper.appendChild(selectEl);
    });

    // 3. UNIFIED 60FPS PHYSICS LOOP: Parallax + Overlapping Scroll Stack + Velocity Scroll
    var nav = document.getElementById('landing-nav');
    var progressBar = document.getElementById('landing-scroll-progress');
    var navLinks = Array.from(document.querySelectorAll('.landing-nav-link[href^="#"]'));
    var spySections = navLinks
        .map(function (a) { return document.querySelector(a.getAttribute('href')); })
        .filter(Boolean);

    var heroCurtain = document.getElementById('hero-curtain');
    var parallaxEls = Array.from(document.querySelectorAll('[data-parallax]'));
    var overlapSlabs = Array.from(document.querySelectorAll('.overlap-slab'));
    var velocitySkewEls = Array.from(document.querySelectorAll('[data-velocity-skew]'));
    var velocityMarqueeTrack = document.getElementById('velocity-marquee-track');

    var smoothY = window.scrollY || 0;
    var lastSmoothY = smoothY;
    var smoothVel = 0;
    var marqueeOffset = 0;
    var marqueeDir = -1; // -1 = left, +1 = right

    function updateNavAndProgress(y) {
        var docH = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
        var pct = Math.min(100, Math.max(0, (y / docH) * 100));
        if (progressBar) progressBar.style.width = pct.toFixed(2) + '%';
        if (nav) nav.classList.toggle('scrolled', y > 32);

        var currentId = '';
        spySections.forEach(function (sec) {
            if (sec.getBoundingClientRect().top <= 200) {
                currentId = '#' + sec.id;
            }
        });
        navLinks.forEach(function (link) {
            link.classList.toggle('active', link.getAttribute('href') === currentId);
        });
    }

    function physicsFrame() {
        var targetY = window.scrollY || window.pageYOffset || 0;
        smoothY += (targetY - smoothY) * 0.14;
        var rawVel = smoothY - lastSmoothY;
        lastSmoothY = smoothY;

        // Damped scroll velocity for buttery skew & marquee acceleration
        smoothVel += (rawVel - smoothVel) * 0.16;

        updateNavAndProgress(targetY);

        var viewH = window.innerHeight || 900;
        var viewW = window.innerWidth || 1280;
        var isDesktopStack = viewW >= 1024 && viewH >= 700;

        // A. Hero Curtain Overlapping Depth (scales down & dims smoothly as sheet rises over it)
        if (heroCurtain && isDesktopStack) {
            var heroProg = Math.min(1, Math.max(0, smoothY / (viewH * 0.92)));
            var heroScale = 1 - heroProg * 0.065;
            var heroOpacity = 1 - heroProg * 0.62;
            heroCurtain.style.transform = 'scale(' + heroScale.toFixed(4) + ') translate3d(0,' + (-heroProg * 28).toFixed(1) + 'px,0)';
            heroCurtain.style.opacity = heroOpacity.toFixed(3);
        } else if (heroCurtain) {
            heroCurtain.style.transform = '';
            heroCurtain.style.opacity = '';
        }

        // B. True Multi-Layer Scroll Parallax
        parallaxEls.forEach(function (el) {
            var speed = parseFloat(el.getAttribute('data-parallax') || '0');
            var axis = el.getAttribute('data-parallax-axis') || 'y';
            var rect = el.getBoundingClientRect();
            var centerDelta = (rect.top + rect.height * 0.5) - (viewH * 0.5);
            var shift = Math.max(-140, Math.min(140, centerDelta * speed));

            if (axis === 'x') {
                el.style.transform = 'translate3d(' + shift.toFixed(1) + 'px, 0, 0)';
            } else {
                el.style.transform = 'translate3d(0, ' + shift.toFixed(1) + 'px, 0)';
            }
        });

        // C. Sticky Overlapping Scroll Story Slabs (depth compression & dim as next slab slides on top)
        if (isDesktopStack && overlapSlabs.length > 1) {
            for (var i = 0; i < overlapSlabs.length - 1; i++) {
                var slab = overlapSlabs[i];
                var nextSlab = overlapSlabs[i + 1];
                var slabRect = slab.getBoundingClientRect();
                var nextRect = nextSlab.getBoundingClientRect();

                // Progress 0 -> 1 as nextSlab approaches & covers slab
                var overlapDist = viewH * 0.68;
                var delta = nextRect.top - slabRect.top;
                var progress = Math.min(1, Math.max(0, 1 - (delta / Math.max(overlapDist, 1))));

                var s = 1 - progress * 0.055;
                var b = 1 - progress * 0.38;
                slab.style.transform = 'scale(' + s.toFixed(4) + ') translate3d(0, ' + (-progress * 8).toFixed(1) + 'px, 0)';
                slab.style.filter = 'brightness(' + b.toFixed(3) + ')';
            }
            var lastSlab = overlapSlabs[overlapSlabs.length - 1];
            lastSlab.style.transform = '';
            lastSlab.style.filter = '';
        } else {
            overlapSlabs.forEach(function (slab) {
                slab.style.transform = '';
                slab.style.filter = '';
            });
        }

        // D. Velocity-Driven Kinetic Marquee (accelerates & reverses smoothly with scroll direction)
        if (velocityMarqueeTrack) {
            var halfWidth = velocityMarqueeTrack.scrollWidth / 2;
            if (halfWidth > 0) {
                if (Math.abs(smoothVel) > 0.35) {
                    marqueeDir = smoothVel > 0 ? -1 : 1;
                }
                var baseDrift = 0.65 * marqueeDir;
                var velBoost = -smoothVel * 0.42;
                marqueeOffset += baseDrift + velBoost;

                if (marqueeOffset <= -halfWidth) marqueeOffset += halfWidth;
                if (marqueeOffset > 0) marqueeOffset -= halfWidth;

                var skewX = Math.max(-9, Math.min(9, -smoothVel * 0.22));
                velocityMarqueeTrack.style.transform = 'translate3d(' + marqueeOffset.toFixed(1) + 'px, 0, 0) skewX(' + skewX.toFixed(2) + 'deg)';
            }
        }

        // E. Velocity Scroll Skew on Editorial Spec Rows
        var clampedSkew = Math.max(-3.2, Math.min(3.2, smoothVel * 0.095));
        velocitySkewEls.forEach(function (el) {
            if (Math.abs(clampedSkew) < 0.04) {
                if (el.style.transform) el.style.transform = '';
            } else {
                el.style.transform = 'skewY(' + clampedSkew.toFixed(2) + 'deg)';
            }
        });

        requestAnimationFrame(physicsFrame);
    }

    if (!prefersReduced) {
        requestAnimationFrame(physicsFrame);
    } else {
        window.addEventListener('scroll', function () {
            updateNavAndProgress(window.scrollY || 0);
        }, { passive: true });
        updateNavAndProgress(window.scrollY || 0);
    }

    // 4. Scroll Reveal Observer
    var revealEls = Array.from(document.querySelectorAll('[data-reveal]'));
    if (revealEls.length && !prefersReduced && 'IntersectionObserver' in window) {
        document.documentElement.classList.add('js-scroll-ready');
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var delay = el.getAttribute('data-reveal-delay');
                    if (delay) el.style.setProperty('--reveal-delay', delay + 'ms');
                    el.classList.add('is-visible');
                    revealObserver.unobserve(el);
                }
            });
        }, { threshold: 0.06, rootMargin: '0px 0px -24px 0px' });

        revealEls.forEach(function (el) { revealObserver.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // 5. Interactive 3D Tilt on Custom 3D Editorial Hero Sculpture
    var heroFigure = document.getElementById('hero-3d-figure');
    if (heroFigure && !prefersReduced) {
        var stage = heroFigure.closest('.hero-3d-wrap') || heroFigure;
        stage.addEventListener('pointermove', function (e) {
            var rect = stage.getBoundingClientRect();
            var px = (e.clientX - rect.left) / rect.width;
            var py = (e.clientY - rect.top) / rect.height;
            var rotY = (px - 0.5) * 14;
            var rotX = (0.5 - py) * 14;
            heroFigure.style.transform = 'rotateX(' + rotX.toFixed(2) + 'deg) rotateY(' + rotY.toFixed(2) + 'deg) scale(1.025)';
        });

        stage.addEventListener('pointerleave', function () {
            heroFigure.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';
        });
    }

    // 6. Interactive Gemini AI Demo Console inside Slab 04
    var aiScenarios = {
        laba: {
            question: 'Analisis laba bersih & margin toko bulan ini. Apa 3 langkah prioritas?',
            context: 'Omzet Rp 19,8 jt · Margin 38,4% · 42 Transaksi',
            bullets: [
                'Laba bersih bulan ini surplus +Rp 7.620.000 dengan margin 38,4% — naik dibanding periode sebelumnya.',
                'Kopi Arabika 250g menyumbang 46% omzet (margin 45,5%); jadikan jangkar promo paket hemat.',
                'Pangkas biaya operasional rutin 10% agar net profit margin menembus 42% di bulan berikutnya.'
            ]
        },
        restock: {
            question: 'Produk mana yang paling mendesak di-restock dulu agar tidak kehabisan?',
            context: '2 SKU Kritis · Valuasi Gudang Rp 4,85 jt',
            bullets: [
                'Prioritas #1: Gula Pasir 1kg (sisa 3 kg dari batas min. 5 kg) — tambah 15 kg untuk stok 2 minggu.',
                'Prioritas #2: Teh Hijau 100g (sisa 8 pack dari min. 10 pack) — padukan promo bundle agar cepat berputar.',
                'Estimasi belanja restock optimal Rp 385.000, aman diambil dari kas surplus tanpa mengganggu operasional.'
            ]
        },
        kasbon: {
            question: 'Bagaimana strategi menagih piutang kasbon yang jatuh tempo dengan sopan?',
            context: 'Piutang Aktif Rp 750 rb · Kolektibilitas 68%',
            bullets: [
                'Prioritaskan pengingat WhatsApp sopan untuk 2 pelanggan dengan piutang melewati jatuh tempo.',
                'Tawarkan opsi cicilan 50% minggu ini agar arus kas cepat cair tanpa merusak hubungan pelanggan.',
                'Batasi plafon kasbon baru maks. Rp 300.000 sampai tagihan berjalan berstatus Lunas.'
            ]
        }
    };

    var aiTabs = Array.from(document.querySelectorAll('[data-ai-demo]'));
    var aiPromptEl = document.getElementById('ai-demo-question');
    var aiContextEl = document.getElementById('ai-demo-context');
    var aiTypingEl = document.getElementById('ai-demo-typing');
    var aiResponseEl = document.getElementById('ai-demo-response');
    var aiTimer = null;

    function playAiScenario(key) {
        var data = aiScenarios[key];
        if (!data || !aiPromptEl || !aiResponseEl) return;
        if (aiTimer) clearTimeout(aiTimer);

        aiTabs.forEach(function (btn) {
            var active = btn.getAttribute('data-ai-demo') === key;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        aiPromptEl.textContent = data.question;
        if (aiContextEl) aiContextEl.textContent = data.context;

        if (prefersReduced) {
            if (aiTypingEl) aiTypingEl.style.display = 'none';
            aiResponseEl.innerHTML = data.bullets.map(function (b, idx) {
                return '<div class="flex items-start gap-2.5"><span class="mt-0.5 shrink-0 font-mono text-[11px] font-bold text-[#C4FF0D]">0' + (idx + 1) + '</span><span>' + b + '</span></div>';
            }).join('');
            return;
        }

        aiResponseEl.innerHTML = '';
        if (aiTypingEl) aiTypingEl.style.display = 'inline-flex';

        aiTimer = setTimeout(function () {
            if (aiTypingEl) aiTypingEl.style.display = 'none';
            data.bullets.forEach(function (text, idx) {
                var row = document.createElement('div');
                row.className = 'flex items-start gap-2.5 opacity-0 translate-y-2 transition-all duration-300';
                row.innerHTML = '<span class="mt-0.5 shrink-0 font-mono text-[11px] font-bold text-[#C4FF0D]">0' + (idx + 1) + '</span><span>' + text + '</span>';
                aiResponseEl.appendChild(row);
                setTimeout(function () {
                    row.classList.remove('opacity-0', 'translate-y-2');
                }, idx * 120 + 20);
            });
        }, 340);
    }

    aiTabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            playAiScenario(btn.getAttribute('data-ai-demo'));
        });
    });

    if (aiTabs.length) {
        playAiScenario('laba');
    }

    // 7. Hairline Editorial FAQ Accordion
    document.querySelectorAll('.faq-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var row = trigger.closest('.faq-row');
            if (!row) return;
            var isOpen = row.classList.contains('open');

            document.querySelectorAll('.faq-row.open').forEach(function (other) {
                if (other !== row) {
                    other.classList.remove('open');
                    var otherBtn = other.querySelector('.faq-trigger');
                    if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
                }
            });

            row.classList.toggle('open', !isOpen);
            trigger.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
        });
    });
});
