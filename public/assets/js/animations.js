/**
 * animations.js — Web3Bento Interactive Widgets, Custom Dropdowns & Micro-Interactions
 */

(function () {
    'use strict';

    // 1. Custom Bento Dropdown Enhancer (Replaces ugly OS native select popup)
    function initCustomSelects() {
        document.querySelectorAll('select:not([data-bento-select-ready])').forEach(selectEl => {
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

            // Sync if form is reset
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

    // 3. Web3Bento Semicircle Gauge
    function initBentoGauge() {
        const gaugeRoot = document.getElementById('bento-gauge-tile');
        if (!gaugeRoot) return;

        const baseScore = parseFloat(gaugeRoot.dataset.baseScore || '84');
        const arcEl = document.getElementById('bento-gauge-arc');
        const needleEl = document.getElementById('bento-gauge-needle');
        const valueEl = document.getElementById('bento-gauge-value');
        const pillBtns = gaugeRoot.querySelectorAll('[data-gauge-tier]');
        const len = Math.PI * 62;

        function updateGauge(mult) {
            const val = Math.max(5, Math.min(100, baseScore * mult));
            const t = val / 100;
            const angle = -90 + t * 180;
            if (arcEl) {
                arcEl.style.strokeDasharray = String(len);
                arcEl.style.strokeDashoffset = String(len * (1 - t));
            }
            if (needleEl) {
                needleEl.style.transform = `rotate(${angle.toFixed(1)}deg)`;
            }
            if (valueEl) {
                valueEl.textContent = val.toFixed(1);
            }
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

        updateGauge(1);
    }

    // 4. Web3Bento PnL Bar Strip Hover Readout
    function initPnLStrip() {
        const strip = document.getElementById('bento-pnl-strip');
        const readout = document.getElementById('bento-pnl-readout');
        if (!strip || !readout) return;

        const defaultLabel = readout.dataset.default || 'Arus Kas Harian';
        const bars = strip.querySelectorAll('[data-pnl-label]');

        bars.forEach(bar => {
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

    function bootAll() {
        initCustomSelects();
        initBentoCardStack();
        initBentoGauge();
        initPnLStrip();
        initAllocationHover();
        initBentoChart();
        initBentoFilters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootAll);
    } else {
        bootAll();
    }

    window.reinitPageAnimations = function () {
        setTimeout(bootAll, 40);
    };
})();
