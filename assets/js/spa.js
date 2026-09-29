/**
 * spa.js — Single Page Application navigation for UMKM Manager / AlpetBizz
 * Menghilangkan reload/kedip saat berpindah halaman dan submit CRUD,
 * serta menangani drawer sidebar responsif untuk Mobile S, M, L & Tablet.
 */

(function () {
    // 1. Loading bar di bagian atas layar
    const progressBar = document.createElement('div');
    progressBar.id = 'spa-progress-bar';
    progressBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0%;background:#38bdf8;z-index:9999;transition:width 0.2s ease, opacity 0.3s ease;pointer-events:none;opacity:0;';
    document.body.appendChild(progressBar);

    function startProgress() {
        progressBar.style.opacity = '1';
        progressBar.style.width = '30%';
        setTimeout(() => { if (progressBar.style.width === '30%') progressBar.style.width = '70%'; }, 150);
    }

    function endProgress() {
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => { progressBar.style.width = '0%'; }, 300);
        }, 150);
    }

    // 2. Kontrol Mobile Sidebar Drawer (Responsif)
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

    // Event listener untuk tombol toggle, close, dan backdrop
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

    // 3. Update active style pada sidebar
    function updateSidebarActive(currentUrl) {
        const urlObj = new URL(currentUrl, window.location.origin);
        const path = urlObj.pathname.split('/').pop() || 'dashboard.php';

        document.querySelectorAll('aside nav a').forEach(link => {
            const linkHref = link.getAttribute('href');
            if (!linkHref) return;
            const linkPath = linkHref.split('?')[0];

            if (linkPath === path) {
                link.className = 'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition bg-white/15 text-white';
            } else {
                link.className = 'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition text-white/65 hover:bg-white/10 hover:text-white';
            }
        });
    }

    // 4. Eksekusi script yang dibawa oleh halaman baru
    function executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        // Trigger khusus jika ada helper function di window (misal profil.js)
        if (typeof window.initProfilePage === 'function' && document.getElementById('profileForm')) {
            window.initProfilePage();
        }
    }

    // 5. Core Loader Halaman
    async function loadPage(url, pushState = true) {
        startProgress();
        // Tutup drawer mobile jika sedang terbuka
        closeSidebar();

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'SPA-Fetch' }
            });

            // Jika diarahkan ke login karena session habis
            if (res.redirected && res.url.includes('login.php')) {
                window.location.href = res.url;
                return;
            }

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newMain = doc.querySelector('main');
            const currentMain = document.querySelector('main');

            if (newMain && currentMain) {
                // Update Title
                if (doc.title) document.title = doc.title;

                // Ganti konten area main
                currentMain.innerHTML = newMain.innerHTML;

                // Update URL browser
                if (pushState) {
                    window.history.pushState({ url }, '', url);
                }

                // Update active link sidebar
                updateSidebarActive(url);

                // Jalankan script yang ada di dalam main
                executeScripts(currentMain);

                // Scroll ke atas
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Fallback jika struktur bukan layout utama
                window.location.href = url;
            }
        } catch (err) {
            console.error('SPA load error, fallback to normal navigation:', err);
            window.location.href = url;
        } finally {
            endProgress();
        }
    }

    // 6. Intercept klik pada link
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;

        // Jangan intercept jika link download atau link eksternal
        const targetUrl = new URL(href, window.location.href);
        if (targetUrl.origin !== window.location.origin) return;

        // Biarkan login.php atau logout reload biasa
        if (href.includes('login.php') || href.includes('logout')) return;

        e.preventDefault();
        loadPage(targetUrl.href, true);
    });

    // 7. Intercept form submit (CRUD tanpa reload)
    document.addEventListener('submit', async function (e) {
        if (e.defaultPrevented) return;
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // Abaikan form logout di sidebar agar keluar secara bersih
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && actionInput.value === 'logout') return;

        const actionUrl = form.getAttribute('action') || window.location.href;
        const targetUrl = new URL(actionUrl, window.location.href);

        // Hanya intercept form internal
        if (targetUrl.origin !== window.location.origin) return;
        if (actionUrl.includes('login.php') || actionUrl.includes('register.php')) return;

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

            if (res.redirected && res.url.includes('login.php')) {
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
                currentMain.innerHTML = newMain.innerHTML;

                // URL hasil redirect PHP (atau URL action)
                const finalUrl = res.url || targetUrl.href;
                window.history.pushState({ url: finalUrl }, '', finalUrl);
                updateSidebarActive(finalUrl);

                executeScripts(currentMain);
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

    // 8. Handle tombol Back / Forward browser
    window.addEventListener('popstate', function () {
        loadPage(window.location.href, false);
    });
})();
