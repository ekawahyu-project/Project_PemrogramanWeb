/**
 * animations.js — Web3Bento Interactive Widgets, Scroll Reveals, Spotlight Sheen & Micro-Interactions
 */

(function () {
    'use strict';

    const prefersReducedMotion = () =>
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let activeTileObserver = null;
    let activeRowObserver = null;

    // 1. Custom Bento Dropdown Enhancer (Replaces OS native select popup)
    function initCustomSelects() {
        document.querySelectorAll('select:not([data-bento-select-ready]):not(.swal2-select)').forEach(selectEl => {
            selectEl.setAttribute('data-bento-select-ready', 'true');

            const wrapper = document.createElement('div');
            wrapper.className = 'bento-select-wrapper';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'bento-select-trigger';

            const labelSpan = document.createElement('span');
            labelSpan.className = 'truncate';
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            labelSpan.textContent = selectedOpt ? selectedOpt.textContent : 'Pilih...';

            const chevron = document.createElement('span');
            chevron.innerHTML = '<svg class="bento-select-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"/></svg>';

            trigger.appendChild(labelSpan);
            trigger.appendChild(chevron.firstElementChild);

            const menu = document.createElement('div');
            menu.className = 'bento-select-menu';
            menu.setAttribute('role', 'listbox');

            function buildOptions() {
                menu.innerHTML = '';
                Array.from(selectEl.options).forEach((opt, idx) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.setAttribute('role', 'option');
                    const isSelected = idx === selectEl.selectedIndex;
                    item.className = 'bento-select-option' + (isSelected ? ' selected' : '');

                    const textSpan = document.createElement('span');
                    textSpan.className = 'truncate';
                    textSpan.textContent = opt.textContent;

                    item.appendChild(textSpan);

                    if (isSelected) {
                        const check = document.createElement('span');
                        check.innerHTML = '<svg class="w-4 h-4 shrink-0 text-[#C4FF0D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                        item.appendChild(check.firstElementChild);
                    }

                    item.addEventListener('click', function (e) {
                        e.stopPropagation();
                        selectEl.selectedIndex = idx;
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
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
                const isOpen = wrapper.classList.contains('open');
                document.querySelectorAll('.bento-select-wrapper.open').forEach(w => w.classList.remove('open'));
                if (!isOpen) {
                    wrapper.classList.add('open');
                }
            });

            selectEl.addEventListener('bento:sync', function () {
                const curOpt = selectEl.options[selectEl.selectedIndex];
                labelSpan.textContent = curOpt ? curOpt.textContent : 'Pilih...';
                buildOptions();
            });

            const parentForm = selectEl.closest('form');
            if (parentForm) {
                parentForm.addEventListener('reset', function () {
                    setTimeout(() => {
                        const curOpt = selectEl.options[selectEl.selectedIndex];
                        labelSpan.textContent = curOpt ? curOpt.textContent : '';
                        buildOptions();
                    }, 20);
                });
            }

            selectEl.style.display = 'none';
            selectEl.parentNode.insertBefore(wrapper, selectEl);
            wrapper.appendChild(trigger);
            wrapper.appendChild(menu);
            wrapper.appendChild(selectEl);
        });
    }

    document.addEventListener('click', function () {
        document.querySelectorAll('.bento-select-wrapper.open').forEach(w => w.classList.remove('open'));
    });

    // 2. Web3Bento Fanned Card Stack (CardsTile)
    function initBentoCardStack() {
        const stackRoot = document.getElementById('bento-cards-stack');
        if (!stackRoot) return;

        const cardButtons = Array.from(stackRoot.querySelectorAll('[data-card-id]'));
        if (!cardButtons.length) return;

        const PEEK = 42;
        const numberEl = document.getElementById('bento-card-front-last4');
        const holderEl = document.getElementById('bento-card-front-holder');
        const balanceEl = document.getElementById('bento-card-front-balance');

        let order = cardButtons.map(btn => btn.getAttribute('data-card-id'));

        function renderStack() {
            const depth = order.length - 1;
            order.forEach((id, pos) => {
                const btn = stackRoot.querySelector(`[data-card-id="${id}"]`);
                if (!btn) return;
                const fromFront = depth - pos;
                const isFront = fromFront === 0;
                const scale = (1 - fromFront * 0.055).toFixed(3);
                const topPx = (depth - fromFront) * PEEK;

                btn.style.top = `${topPx}px`;
                btn.style.zIndex = String(pos + 1);
                btn.style.transform = `translateX(-50%) scale(${scale})`;
                btn.style.cursor = isFront ? 'default' : 'pointer';
                btn.setAttribute('aria-pressed', isFront ? 'true' : 'false');

                if (isFront) {
                    if (numberEl) numberEl.textContent = '•••• •••• •••• ' + (btn.dataset.last4 || '0000');
                    if (holderEl) holderEl.textContent = (btn.dataset.holder || '') + (btn.dataset.sub ? ' · ' + btn.dataset.sub : '');
                    if (balanceEl) balanceEl.textContent = btn.dataset.balance || '';
                }
            });
        }

        cardButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-card-id');
                if (order[order.length - 1] === id) return;
                order = [...order.filter(x => x !== id), id];
                renderStack();
            });
        });

        const tileArea = stackRoot.closest('[data-card-glare-zone]');
        if (tileArea) {
            tileArea.addEventListener('pointermove', function (e) {
                const r = tileArea.getBoundingClientRect();
                const pct = Math.round(((e.clientX - r.left) / Math.max(r.width, 1)) * 100);
                stackRoot.querySelectorAll('[data-card-glare]').forEach((el, idx) => {
                    el.style.backgroundPosition = `${pct + idx * 16}% 0`;
                });
            });
        }

        renderStack();
    }

    // 3. Web3Bento Semicircle Gauge (Animated Entry Sweep)
    function initBentoGauge() {
        const gaugeRoot = document.getElementById('bento-gauge-tile');
        if (!gaugeRoot) return;

        const baseScore = parseFloat(gaugeRoot.dataset.baseScore || '84');
        const arcEl = document.getElementById('bento-gauge-arc');
        const needleEl = document.getElementById('bento-gauge-needle');
        const valueEl = document.getElementById('bento-gauge-value');
        const pillBtns = gaugeRoot.querySelectorAll('[data-gauge-tier]');
        const len = Math.PI * 62;

        function setGaugeVisual(val) {
            const clamped = Math.max(0, Math.min(100, val));
            const t = clamped / 100;
            const angle = -90 + t * 180;
            if (arcEl) {
                arcEl.style.strokeDasharray = String(len);
                arcEl.style.strokeDashoffset = String(len * (1 - t));
            }
            if (needleEl) {
                needleEl.style.transform = `rotate(${angle.toFixed(1)}deg)`;
            }
            if (valueEl) {
                valueEl.textContent = clamped.toFixed(1);
            }
        }

        function updateGauge(mult) {
            const val = Math.max(5, Math.min(100, baseScore * mult));
            setGaugeVisual(val);
        }

        pillBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                pillBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const tier = this.dataset.gaugeTier;
                const mult = tier === 'Slow' ? 0.92 : (tier === 'Fast' ? 1.06 : 1);
                updateGauge(mult);
            });
        });

        if (prefersReducedMotion()) {
            updateGauge(1);
        } else {
            setGaugeVisual(0);
            requestAnimationFrame(() => {
                requestAnimationFrame(() => updateGauge(1));
            });
        }
    }

    // 4. Web3Bento PnL Bar Strip Hover Readout + Wave Rise
    function initPnLStrip() {
        const strip = document.getElementById('bento-pnl-strip');
        const readout = document.getElementById('bento-pnl-readout');
        if (!strip || !readout) return;

        const defaultLabel = readout.dataset.default || 'Arus Kas Harian';
        const bars = strip.querySelectorAll('[data-pnl-label]');

        bars.forEach((bar, idx) => {
            if (!prefersReducedMotion()) {
                bar.style.setProperty('--bar-delay', `${idx * 18}ms`);
                bar.classList.add('pnl-bar-rise');
            }
            bar.addEventListener('pointerenter', function () {
                readout.textContent = this.dataset.pnlLabel || defaultLabel;
                bars.forEach(b => { b.style.opacity = b === this ? '1' : '0.25'; });
            });
        });

        strip.addEventListener('pointerleave', function () {
            readout.textContent = defaultLabel;
            bars.forEach(b => { b.style.opacity = b.dataset.baseOpacity || '0.75'; });
        });
    }

    // 5. Web3Bento Allocation Hover Highlight
    function initAllocationHover() {
        const root = document.getElementById('bento-allocation-tile');
        if (!root) return;

        const segments = root.querySelectorAll('[data-alloc-segment]');
        const items = root.querySelectorAll('[data-alloc-item]');

        function highlight(idx) {
            segments.forEach((s, i) => {
                s.style.opacity = (idx === null || idx === i) ? '1' : '0.28';
            });
        }

        segments.forEach((s, i) => {
            s.addEventListener('pointerenter', () => highlight(i));
        });
        items.forEach((item, i) => {
            item.addEventListener('pointerenter', () => highlight(i));
            item.addEventListener('pointerleave', () => highlight(null));
        });
        root.addEventListener('pointerleave', () => highlight(null));
    }

    // 6. Web3Bento Interactive SVG Curve Chart
    function initBentoChart() {
        const chartBox = document.getElementById('bento-svg-chart');
        if (!chartBox) return;

        const crosshair = document.getElementById('bento-chart-crosshair');
        const dotA = document.getElementById('bento-chart-dot-a');
        const dotB = document.getElementById('bento-chart-dot-b');
        const valA = document.getElementById('bento-chart-val-a');
        const valB = document.getElementById('bento-chart-val-b');

        let pointsA = [];
        let pointsB = [];
        try {
            pointsA = JSON.parse(chartBox.dataset.pointsA || '[]');
            pointsB = JSON.parse(chartBox.dataset.pointsB || '[]');
        } catch (e) {
            return;
        }
        if (!pointsA.length) return;

        const fmtRp = (v) => 'Rp ' + Math.round(v).toLocaleString('id-ID');

        chartBox.addEventListener('pointermove', function (e) {
            const r = chartBox.getBoundingClientRect();
            const ratio = Math.max(0, Math.min(1, (e.clientX - r.left) / Math.max(r.width, 1)));
            const idx = Math.round(ratio * (pointsA.length - 1));
            const ptA = pointsA[idx];
            const ptB = pointsB[idx] || ptA;
            if (!ptA) return;

            const leftPct = `${ptA.xPct}%`;
            if (crosshair) {
                crosshair.style.left = leftPct;
                crosshair.style.opacity = '1';
            }
            if (dotA) {
                dotA.style.left = leftPct;
                dotA.style.top = `${ptA.yPct}%`;
            }
            if (dotB && ptB) {
                dotB.style.left = leftPct;
                dotB.style.top = `${ptB.yPct}%`;
                dotB.style.opacity = '1';
            }
            if (valA) valA.textContent = fmtRp(ptA.val);
            if (valB && ptB) valB.textContent = fmtRp(ptB.val);
        });

        chartBox.addEventListener('pointerleave', function () {
            const lastIdx = pointsA.length - 1;
            const ptA = pointsA[lastIdx];
            const ptB = pointsB[lastIdx];
            if (crosshair) crosshair.style.opacity = '0';
            if (dotB) dotB.style.opacity = '0';
            if (dotA && ptA) {
                dotA.style.left = `${ptA.xPct}%`;
                dotA.style.top = `${ptA.yPct}%`;
            }
            if (valA) valA.textContent = valA.dataset.default || fmtRp(ptA ? ptA.val : 0);
            if (valB) valB.textContent = valB.dataset.default || fmtRp(ptB ? ptB.val : 0);
        });
    }

    // 7. Universal Bento Table / List Filter Pills & Live Search
    function initBentoFilters() {
        document.querySelectorAll('[data-bento-filter-group]').forEach(group => {
            const targetId = group.getAttribute('data-bento-filter-group');
            const container = document.getElementById(targetId);
            if (!container) return;

            const pills = group.querySelectorAll('[data-filter-val]');
            const searchInput = document.querySelector(`[data-bento-search="${targetId}"]`);

            function applyFilter() {
                const activePill = group.querySelector('.bento-pill.active');
                const filterVal = activePill ? (activePill.getAttribute('data-filter-val') || 'all') : 'all';
                const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

                container.querySelectorAll('[data-filter-item]').forEach(row => {
                    const itemTag = (row.getAttribute('data-filter-tag') || '').toLowerCase();
                    const itemText = (row.textContent || '').toLowerCase();
                    const matchTag = filterVal === 'all' || itemTag === filterVal.toLowerCase();
                    const matchQuery = !query || itemText.includes(query);
                    row.style.display = (matchTag && matchQuery) ? '' : 'none';
                });
            }

            pills.forEach(pill => {
                pill.addEventListener('click', function () {
                    pills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    applyFilter();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }
        });
    }

    // 8. Staggered Scroll-Reveal for Bento Tiles (Clears transform post-reveal to preserve stacking contexts)
    function initScrollReveal() {
        if (activeTileObserver) {
            activeTileObserver.disconnect();
            activeTileObserver = null;
        }
        if (prefersReducedMotion() || !('IntersectionObserver' in window)) return;

        const main = document.querySelector('main');
        if (!main) return;

        const tiles = Array.from(main.querySelectorAll('.bento-tile, .bento-tile-accent')).filter(el => {
            // Skip tiles inside fixed/modal overlays or explicitly marked data-no-reveal (e.g. sticky AI chat workspace)
            return !el.hasAttribute('data-no-reveal') && !el.closest('[data-bento-portal-modal]') && !el.closest('.fixed');
        });

        if (!tiles.length) return;

        function cleanupTile(el) {
            el.classList.remove('bento-reveal', 'bento-revealed');
            el.style.removeProperty('--stagger-delay');
        }

        function revealTile(el, delayMs) {
            el.style.setProperty('--stagger-delay', `${delayMs}ms`);
            requestAnimationFrame(() => {
                el.classList.add('bento-revealed');
            });
            setTimeout(() => cleanupTile(el), delayMs + 650);
        }

        let initialIdx = 0;
        const viewH = window.innerHeight || 900;

        activeTileObserver = new IntersectionObserver((entries) => {
            let batchIdx = 0;
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                activeTileObserver.unobserve(el);
                revealTile(el, Math.min(batchIdx * 65, 320));
                batchIdx++;
            });
        }, { threshold: 0.01, rootMargin: '0px 0px -12px 0px' });

        tiles.forEach(tile => {
            const rect = tile.getBoundingClientRect();
            tile.classList.add('bento-reveal');

            if (rect.top < viewH * 0.96) {
                revealTile(tile, Math.min(initialIdx * 55, 360));
                initialIdx++;
            } else {
                activeTileObserver.observe(tile);
            }
        });
    }

    // 9. Staggered Table Row Cascade
    function initTableRowReveal() {
        if (activeRowObserver) {
            activeRowObserver.disconnect();
            activeRowObserver = null;
        }
        if (prefersReducedMotion() || !('IntersectionObserver' in window)) return;

        const main = document.querySelector('main');
        if (!main) return;

        const tables = Array.from(main.querySelectorAll('.bento-table'));
        if (!tables.length) return;

        activeRowObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const table = entry.target;
                activeRowObserver.unobserve(table);

                const rows = Array.from(table.querySelectorAll('tbody tr')).slice(0, 14);
                rows.forEach((row, idx) => {
                    row.style.setProperty('--row-delay', `${idx * 30}ms`);
                    row.classList.add('bento-row-reveal');
                    requestAnimationFrame(() => {
                        row.classList.add('bento-row-shown');
                    });
                    setTimeout(() => {
                        row.classList.remove('bento-row-reveal', 'bento-row-shown');
                        row.style.removeProperty('--row-delay');
                    }, idx * 30 + 420);
                });
            });
        }, { threshold: 0.05 });

        tables.forEach(t => activeRowObserver.observe(t));
    }

    // 10. Smooth KPI Currency & Metric Count-Up
    function initKpiCountUp() {
        if (prefersReducedMotion()) return;

        const main = document.querySelector('main');
        if (!main) return;

        const candidates = Array.from(main.querySelectorAll('.bento-tile .tabular-nums, .bento-tile-accent .tabular-nums'));

        candidates.forEach(el => {
            if (el.children.length > 0 || el.dataset.kpiAnimated === 'true') return;
            if (el.closest('table') || el.closest('form') || el.id === 'bento-gauge-value') return;

            const raw = el.textContent.trim();
            // Match Rp formatted numbers like "Rp 1.995.000", "+Rp 765.000", "-Rp 50.000"
            const rpMatch = raw.match(/^([+\-−]?)\s*Rp\s+([\d.]+)$/);
            if (!rpMatch) return;

            const sign = rpMatch[1] || '';
            const target = parseInt(rpMatch[2].replace(/\./g, ''), 10);
            if (!Number.isFinite(target) || target <= 0) return;

            el.dataset.kpiAnimated = 'true';
            const duration = 640;
            const start = performance.now();

            function tick(now) {
                const progress = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - progress, 3);
                const val = Math.round(target * eased);
                el.textContent = `${sign}Rp ${val.toLocaleString('id-ID')}`;
                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    el.textContent = raw;
                }
            }

            requestAnimationFrame(tick);
        });
    }

    // 11. Cursor-Tracking Spotlight Sheen (Single lightweight delegated listener)
    let spotlightBound = false;
    function initBentoSpotlight() {
        if (spotlightBound || prefersReducedMotion()) return;
        spotlightBound = true;

        let rafId = null;
        let lastEvent = null;

        document.addEventListener('pointermove', function (e) {
            lastEvent = e;
            if (rafId) return;
            rafId = requestAnimationFrame(() => {
                rafId = null;
                if (!lastEvent) return;
                const tile = lastEvent.target && lastEvent.target.closest ? lastEvent.target.closest('.bento-tile') : null;
                if (!tile) return;
                const r = tile.getBoundingClientRect();
                tile.style.setProperty('--spot-x', `${(lastEvent.clientX - r.left).toFixed(0)}px`);
                tile.style.setProperty('--spot-y', `${(lastEvent.clientY - r.top).toFixed(0)}px`);
            });
        }, { passive: true });
    }

    // 12. Global Scroll Helpers: App Scroll Progress Bar, Velocity Scroll Inertia & Floating Scroll-to-Top
    let scrollHelpersBound = false;
    function initScrollHelpers() {
        if (scrollHelpersBound) return;
        scrollHelpersBound = true;

        const bar = document.createElement('div');
        bar.id = 'app-scroll-progress';
        bar.setAttribute('aria-hidden', 'true');
        document.body.appendChild(bar);

        const topBtn = document.createElement('button');
        topBtn.id = 'bento-scroll-top';
        topBtn.type = 'button';
        topBtn.setAttribute('aria-label', 'Kembali ke atas');
        topBtn.title = 'Kembali ke atas';
        topBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 15l7-7 7 7"/></svg>';
        topBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
        });
        document.body.appendChild(topBtn);

        let lastY = window.scrollY || 0;
        let smoothVel = 0;
        let rafActive = false;

        function velocityLoop() {
            const scrollY = window.scrollY || window.pageYOffset || 0;
            const maxScroll = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);

            if (maxScroll > 120 && scrollY > 24) {
                bar.style.opacity = '1';
                bar.style.width = `${Math.min(100, (scrollY / maxScroll) * 100).toFixed(2)}%`;
            } else {
                bar.style.opacity = '0';
                bar.style.width = '0%';
            }

            topBtn.classList.toggle('visible', scrollY > 360);

            // Subtle scroll-velocity physics on workspace header & non-sticky, non-form tiles
            if (!prefersReducedMotion()) {
                const rawVel = scrollY - lastY;
                lastY = scrollY;
                smoothVel += (rawVel - smoothVel) * 0.18;

                const headerNav = document.querySelector('main nav[aria-label]');
                if (headerNav) {
                    const hdrShift = Math.min(18, scrollY * 0.08);
                    headerNav.style.transform = hdrShift > 0.2 ? `translate3d(0, ${hdrShift.toFixed(1)}px, 0)` : '';
                }

                const skew = Math.max(-1.1, Math.min(1.1, smoothVel * 0.032));
                const main = document.querySelector('main');
                if (main) {
                    const tiles = main.querySelectorAll('.bento-tile:not([data-no-reveal]):not(.bento-reveal), .bento-tile-accent:not(.bento-reveal)');
                    tiles.forEach(t => {
                        if (t.classList.contains('xl:sticky') || t.querySelector('form')) return;
                        if (Math.abs(skew) < 0.04) {
                            if (t.style.transform && t.style.transform.includes('skewY')) {
                                t.style.transform = '';
                            }
                        } else {
                            t.style.transform = `skewY(${skew.toFixed(2)}deg)`;
                        }
                    });
                }

                if (Math.abs(smoothVel) > 0.06 || Math.abs(rawVel) > 0.06) {
                    requestAnimationFrame(velocityLoop);
                    return;
                }
            }

            rafActive = false;
        }

        window.addEventListener('scroll', function () {
            if (!rafActive) {
                rafActive = true;
                requestAnimationFrame(velocityLoop);
            }
        }, { passive: true });

        velocityLoop();
    }

    function bootAll() {
        initCustomSelects();
        initBentoCardStack();
        initBentoGauge();
        initPnLStrip();
        initAllocationHover();
        initBentoChart();
        initBentoFilters();
        initScrollReveal();
        initTableRowReveal();
        initKpiCountUp();
        initBentoSpotlight();
        initScrollHelpers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootAll);
    } else {
        bootAll();
    }

    window.reinitPageAnimations = function () {
        requestAnimationFrame(bootAll);
    };
})();
