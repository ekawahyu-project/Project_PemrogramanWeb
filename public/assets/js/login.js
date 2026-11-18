/* login.js — VaultoryHero & Unified Auth Modal Controller */
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
    if (panel) panel.classList.add('open');
}

function closeLogin() {
    var panel = document.getElementById('login-panel');
    if (panel) panel.classList.remove('open');
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
});
