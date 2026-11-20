/**
 * spa.js — Single Page Application navigation for AlpetBizz (Vaultory × Web3Bento)
 */

(function () {
    const progressBar = document.createElement('div');
    progressBar.id = 'spa-progress-bar';
    progressBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0%;background:#C4FF0D;box-shadow:0 0 12px rgba(196,255,13,0.6);z-index:9999;transition:width 0.2s ease, opacity 0.3s ease;pointer-events:none;opacity:0;';
    document.body.appendChild(progressBar);

    function startProgress() {
        progressBar.style.opacity = '1';
        progressBar.style.width = '35%';
        setTimeout(() => { if (progressBar.style.width === '35%') progressBar.style.width = '75%'; }, 150);
    }

    function endProgress() {
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => { progressBar.style.width = '0%'; }, 300);
        }, 150);
    }

    function openSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (backdrop) backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    }

    function closeSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (backdrop) backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#sidebar-toggle')) {
            openSidebar();
        } else if (e.target.closest('#sidebar-close') || e.target.closest('#sidebar-backdrop')) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    const ACTIVE_CLASS = 'flex items-center justify-between gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition bg-[#C4FF0D] text-[#152208] shadow-[0_6px_20px_-4px_rgba(196,255,13,0.35)]';
    const INACTIVE_CLASS = 'flex items-center justify-between gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition text-[#9AA68E] hover:bg-white/[0.05] hover:text-white';

    function updateSidebarActive(currentUrl) {
        const urlObj = new URL(currentUrl, window.location.origin);
        const path = '/' + (urlObj.pathname.split('/').filter(Boolean).pop() || 'dashboard');

        document.querySelectorAll('aside nav a').forEach(link => {
            const linkHref = link.getAttribute('href');
            if (!linkHref) return;
            const linkPath = linkHref.split('?')[0];

            if (linkPath === path) {
                link.className = ACTIVE_CLASS;
            } else {
                link.className = INACTIVE_CLASS;
            }
        });
    }

    function executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        if (typeof window.initProfilePage === 'function' && document.getElementById('profileForm')) {
            window.initProfilePage();
        }
    }

    let currentPageKey = window.location.pathname + window.location.search;

    function getHashTarget(hash) {
        if (!hash || hash === '#') return null;

        let id;
        try {
            id = decodeURIComponent(hash.slice(1));
        } catch (_) {
            id = hash.slice(1);
        }

        return id ? document.getElementById(id) : null;
    }

    function scrollToHash(hash, shouldFocus = false) {
        const targetEl = getHashTarget(hash);
        if (!targetEl) return false;

        const mobileHeader = document.querySelector('body > header.sticky');
        const offset = (mobileHeader && mobileHeader.offsetHeight > 0 ? mobileHeader.offsetHeight : 0) + 20;
        const targetTop = Math.max(0, targetEl.getBoundingClientRect().top + window.scrollY - offset);

        window.scrollTo({ top: targetTop, behavior: 'smooth' });

        targetEl.classList.remove('bento-target-flash');
        void targetEl.offsetWidth;
        targetEl.classList.add('bento-target-flash');
        targetEl.addEventListener('animationend', () => {
            targetEl.classList.remove('bento-target-flash');
        }, { once: true });

        if (shouldFocus) {
            const focusable = targetEl.querySelector(
                'input:not([type="hidden"]):not([disabled]):not([readonly]), textarea:not([disabled]):not([readonly]), button.bento-select-trigger:not([disabled])'
            );
            if (focusable) {
                setTimeout(() => {
                    try {
                        focusable.focus({ preventScroll: true });
                    } catch (_) {
                        // Abaikan jika elemen tidak dapat difokuskan
                    }
                }, 320);
            }
        }

        return true;
    }

    async function loadPage(url, pushState = true) {
        startProgress();
        closeSidebar();

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'SPA-Fetch' }
            });

            if (res.redirected && (res.url.includes('/login') || res.url.includes('login.php'))) {
                window.location.href = res.url;
                return;
            }

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newMain = doc.querySelector('main');
            const currentMain = document.querySelector('main');

            if (newMain && currentMain) {
                if (doc.title) document.title = doc.title;

                currentMain.style.transition = 'opacity 0.12s ease, transform 0.12s ease';
                currentMain.style.opacity = '0';
                currentMain.style.transform = 'translateY(-6px)';
                await new Promise(r => setTimeout(r, 120));

                currentMain.innerHTML = newMain.innerHTML;

                currentMain.style.opacity = '0';
                currentMain.style.transform = 'translateY(10px)';
                void currentMain.offsetHeight;
                currentMain.style.transition = 'opacity 0.28s cubic-bezier(0.22, 1, 0.36, 1), transform 0.28s cubic-bezier(0.22, 1, 0.36, 1)';
                currentMain.style.opacity = '1';
                currentMain.style.transform = 'translateY(0)';
                setTimeout(() => {
                    currentMain.style.transform = '';
                    currentMain.style.transition = '';
                }, 300);

                // Bersihkan modal portaled dari halaman sebelumnya jika ada
                document.querySelectorAll('body > [data-bento-portal-modal]').forEach(el => el.remove());
                document.body.classList.remove('overflow-hidden');

                const requestedUrlObj = new URL(url, window.location.href);
                const resolvedUrlObj = res.redirected ? new URL(res.url, window.location.origin) : requestedUrlObj;
                const finalUrl = res.redirected ? resolvedUrlObj.href : requestedUrlObj.href;
                const targetHash = res.redirected ? resolvedUrlObj.hash : requestedUrlObj.hash;

                currentPageKey = resolvedUrlObj.pathname + resolvedUrlObj.search;

                if (pushState) {
                    window.history.pushState({ url: finalUrl }, '', finalUrl);
                }

                updateSidebarActive(finalUrl);
                executeScripts(currentMain);

                if (typeof window.reinitPageAnimations === 'function') {
                    window.reinitPageAnimations();
                }

                if (targetHash && getHashTarget(targetHash)) {
                    setTimeout(() => scrollToHash(targetHash, true), 80);
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } else {
                window.location.href = url;
            }
        } catch (err) {
            console.error('SPA load error, fallback to normal navigation:', err);
            window.location.href = url;
        } finally {
            endProgress();
        }
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;

        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('javascript:') || link.target === '_blank' || link.hasAttribute('download')) return;

        if (href.startsWith('#')) {
            if (href === '#') return;
            if (getHashTarget(href)) {
                e.preventDefault();
                if (window.location.hash !== href) {
                    window.history.replaceState(window.history.state, '', href);
                }
                scrollToHash(href, true);
            }
            return;
        }

        const targetUrl = new URL(href, window.location.href);
        if (targetUrl.origin !== window.location.origin) return;

        if (href.includes('login') || href.includes('logout')) return;

        const targetPageKey = targetUrl.pathname + targetUrl.search;
        if (targetPageKey === currentPageKey && targetUrl.hash && getHashTarget(targetUrl.hash)) {
            e.preventDefault();
            if (window.location.hash !== targetUrl.hash) {
                window.history.replaceState(window.history.state, '', targetUrl.hash);
            }
            scrollToHash(targetUrl.hash, true);
            return;
        }

        e.preventDefault();
        loadPage(targetUrl.href, true);
    });

    document.addEventListener('submit', async function (e) {
        if (e.defaultPrevented) return;
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        if (form.hasAttribute('data-spa-ignore')) return;
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && actionInput.value === 'logout') return;

        const actionUrl = form.getAttribute('action') || window.location.href;
        const targetUrl = new URL(actionUrl, window.location.href);

        if (targetUrl.origin !== window.location.origin) return;
        if (actionUrl.includes('login') || actionUrl.includes('logout') || actionUrl.includes('register')) return;

        e.preventDefault();
        startProgress();

        try {
            const formData = new FormData(form);
            const method = (form.getAttribute('method') || 'POST').toUpperCase();

            const res = await fetch(targetUrl.href, {
                method: method,
                body: formData,
                headers: { 'X-Requested-With': 'SPA-Fetch' }
            });

            if (res.redirected && (res.url.includes('login') || res.url.includes('login.php'))) {
                window.location.href = res.url;
                return;
            }

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newMain = doc.querySelector('main');
            const currentMain = document.querySelector('main');

            if (newMain && currentMain) {
                if (doc.title) document.title = doc.title;
                document.querySelectorAll('body > [data-bento-portal-modal]').forEach(el => el.remove());
                document.body.classList.remove('overflow-hidden');
                currentMain.innerHTML = newMain.innerHTML;

                const finalUrl = res.url || targetUrl.href;
                const finalUrlObj = new URL(finalUrl, window.location.origin);
                currentPageKey = finalUrlObj.pathname + finalUrlObj.search;
                window.history.pushState({ url: finalUrl }, '', finalUrl);
                updateSidebarActive(finalUrl);
                executeScripts(currentMain);

                if (typeof window.reinitPageAnimations === 'function') {
                    window.reinitPageAnimations();
                }
            } else {
                window.location.reload();
            }
        } catch (err) {
            console.error('SPA form submit error:', err);
            form.submit();
        } finally {
            endProgress();
        }
    });

    window.addEventListener('popstate', function () {
        const nextPageKey = window.location.pathname + window.location.search;
        if (nextPageKey === currentPageKey) {
            if (window.location.hash) {
                scrollToHash(window.location.hash, false);
            }
            return;
        }

        loadPage(window.location.href, false);
    });
})();
