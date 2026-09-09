// resources/js/spa-router.js
// High-performance Persistent Shell Router for SIM-GYM.
// Keeps Sidebar & Navbar mounted without tearing down the DOM, swapping only <main>.

let progressBar = null;
let progressTimer = null;
let isNavigating = false;

/**
 * Create and manage the sleek top progress bar.
 */
function getProgressBar() {
    if (!progressBar && typeof document !== 'undefined') {
        progressBar = document.createElement('div');
        progressBar.id = 'spa-progress-bar';
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #d4a757 0%, #eac176 50%, #f59e0b 100%);
            box-shadow: 0 0 10px rgba(212, 167, 87, 0.7);
            z-index: 99999;
            transition: width 200ms cubic-bezier(0.2, 0.8, 0.2, 1), opacity 150ms ease;
            pointer-events: none;
            opacity: 0;
        `;
        document.body.appendChild(progressBar);
    }
    return progressBar;
}

function startProgress() {
    const bar = getProgressBar();
    if (!bar) return;
    clearInterval(progressTimer);
    bar.style.opacity = '1';
    bar.style.width = '20%';

    let w = 20;
    progressTimer = setInterval(() => {
        if (w < 80) {
            w += Math.random() * 15;
            bar.style.width = `${Math.min(w, 80)}%`;
        }
    }, 150);
}

function finishProgress() {
    const bar = getProgressBar();
    if (!bar) return;
    clearInterval(progressTimer);
    bar.style.width = '100%';
    setTimeout(() => {
        bar.style.opacity = '0';
        setTimeout(() => {
            bar.style.width = '0%';
        }, 150);
    }, 120);
}

/**
 * Check whether a link click can be intercepted by SPA navigation.
 */
function isNavigableLink(a, e) {
    if (!a || !a.href) return false;
    if (e.button !== 0) return false; // Only primary click
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return false; // Allow new tab / modified clicks
    if (a.hasAttribute('download')) return false;
    if (a.getAttribute('target') && a.getAttribute('target') !== '_self') return false;
    if (a.hasAttribute('data-no-spa') || a.hasAttribute('data-native')) return false;
    if (a.closest('[data-no-spa]')) return false;

    // Check origin
    if (a.origin !== window.location.origin) return false;

    // Check for hash-only anchors on the same page
    if (a.pathname === window.location.pathname && a.search === window.location.search && a.hash) {
        return false;
    }

    // Ignore action routes that modify state or logout
    const p = a.pathname.toLowerCase();
    if (p.includes('/logout') || p.includes('/export') || p.includes('/download')) return false;

    // Only operate within the same app area (e.g. admin -> admin, member -> member)
    const currentIsAdmin = window.location.pathname.startsWith('/admin');
    const targetIsAdmin = a.pathname.startsWith('/admin');
    const currentIsMember = window.location.pathname.startsWith('/member');
    const targetIsMember = a.pathname.startsWith('/member');

    if (currentIsAdmin && !targetIsAdmin) return false;
    if (currentIsMember && !targetIsMember) return false;
    if (!currentIsAdmin && !currentIsMember) return false;

    return true;
}

/**
 * Synchronize sidebar active highlight based on target pathname.
 */
function updateSidebarActive(targetUrl) {
    const targetPath = new URL(targetUrl, window.location.origin).pathname.replace(/\/$/, '');
    const aside = document.querySelector('aside');
    if (!aside) return;

    const links = Array.from(aside.querySelectorAll('nav a[href]'));
    if (!links.length) return;

    // Find the link that best matches the current pathname
    let bestLink = null;
    let longestMatchLen = -1;

    for (const link of links) {
        const linkPath = new URL(link.href, window.location.origin).pathname.replace(/\/$/, '');
        if (targetPath === linkPath) {
            bestLink = link;
            longestMatchLen = linkPath.length + 1000; // Exact match priority
            break;
        }
        if (targetPath.startsWith(linkPath) && linkPath !== '/admin' && linkPath !== '/member') {
            if (linkPath.length > longestMatchLen) {
                bestLink = link;
                longestMatchLen = linkPath.length;
            }
        }
    }

    // Reset old active states
    links.forEach(link => {
        const isSubmenu = link.closest('#membership-submenu, #produk-submenu, #kehadiran-submenu, #laporan-submenu');
        
        link.setAttribute('aria-current', 'false');
        link.classList.remove(
            'bg-gradient-to-r', 'from-gold-500/20', 'to-transparent', 'text-gold-300',
            'shadow-lg', 'shadow-gold-500/20', 'font-medium', 'from-gold-500/10'
        );

        if (isSubmenu) {
            link.classList.add('text-brand-silver');
            const icon = link.querySelector('i[data-lucide]');
            if (icon) {
                icon.classList.remove('text-gold-300');
                icon.classList.add('text-brand-silver/70');
            }
        } else {
            link.classList.add('text-brand-silver');
            const icon = link.querySelector('i[data-lucide]');
            if (icon) icon.classList.remove('text-gold-300');
        }

        // Remove pulse indicators
        const indicator = link.querySelector('.sidebar-active-indicator, div.animate-pulse');
        if (indicator) indicator.remove();
    });

    // Apply active state to the matched link
    if (bestLink) {
        bestLink.setAttribute('aria-current', 'page');
        bestLink.classList.remove('text-brand-silver');
        bestLink.classList.add('text-gold-300');

        const isSubmenu = bestLink.closest('#membership-submenu, #produk-submenu, #kehadiran-submenu, #laporan-submenu');

        if (isSubmenu) {
            bestLink.classList.add('font-medium', 'bg-gradient-to-r', 'from-gold-500/10', 'to-transparent');
            const icon = bestLink.querySelector('i[data-lucide]');
            if (icon) {
                icon.classList.remove('text-brand-silver/70');
                icon.classList.add('text-gold-300');
            }

            // Ensure parent submenu is open in Alpine
            const parentButton = isSubmenu.previousElementSibling;
            if (parentButton && parentButton.tagName === 'BUTTON') {
                parentButton.classList.add('text-gold-300', 'bg-brand-gunmetal/40');
                parentButton.classList.remove('text-brand-silver');
                // Check if submenu is hidden
                if (isSubmenu.style.display === 'none' || isSubmenu.hasAttribute('hidden')) {
                    parentButton.click();
                }
            }
        } else {
            bestLink.classList.add('bg-gradient-to-r', 'from-gold-500/20', 'to-transparent', 'shadow-lg', 'shadow-gold-500/20');
            const icon = bestLink.querySelector('i[data-lucide]');
            if (icon) icon.classList.add('text-gold-300');

            // Add active pulse pill
            if (!bestLink.querySelector('.sidebar-active-indicator')) {
                const pill = document.createElement('div');
                pill.className = 'sidebar-active-indicator ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600 rounded-full animate-pulse';
                bestLink.appendChild(pill);
            }
        }
    }
}

/**
 * Synchronize navbar breadcrumb from new document.
 */
function updateNavbarBreadcrumb(newDoc) {
    const currentBreadcrumb = document.querySelector('header nav[aria-label="Breadcrumb"]');
    const newBreadcrumb = newDoc.querySelector('header nav[aria-label="Breadcrumb"]');
    if (currentBreadcrumb && newBreadcrumb) {
        currentBreadcrumb.innerHTML = newBreadcrumb.innerHTML;
    }
}

/**
 * Execute newly added script tags.
 */
function runScripts(container, newDoc) {
    // 1. Run inline scripts and page scripts inside <main>
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.textContent = oldScript.textContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });

    // 2. Run page-specific scripts injected in body (like @push('scripts'))
    if (newDoc) {
        const newBodyScripts = newDoc.querySelectorAll('body > script:not([src*="app.js"])');
        newBodyScripts.forEach(s => {
            const scriptEl = document.createElement('script');
            Array.from(s.attributes).forEach(attr => scriptEl.setAttribute(attr.name, attr.value));
            scriptEl.textContent = s.textContent;
            scriptEl.setAttribute('data-spa-script', 'true');
            document.body.appendChild(scriptEl);
        });
    }
}

let currentLoadedUrl = typeof window !== 'undefined' ? window.location.href : '';

/**
 * Core SPA Navigation function.
 */
export async function navigateTo(url, pushState = true) {
    if (isNavigating) return;

    // Check if url is already loaded
    const fullTarget = new URL(url, window.location.origin).href;
    if (pushState && fullTarget === currentLoadedUrl) {
        return;
    }

    const currentMain = document.querySelector('main');
    if (!currentMain) {
        window.location.href = url;
        return;
    }

    isNavigating = true;
    startProgress();

    // Smooth subtle cross-fade on opacity only (never apply transform to <main> to prevent trapping position:fixed modals)
    currentMain.style.transition = 'opacity 100ms ease';
    currentMain.style.opacity = '0.65';

    try {
        const res = await fetch(fullTarget, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SIM-GYM-SPA': '1',
            },
            credentials: 'same-origin',
        });

        // If response redirected to another section or login, follow standard navigation
        if (res.redirected) {
            window.location.href = res.url;
            return;
        }

        if (!res.ok) {
            window.location.href = fullTarget;
            return;
        }

        const html = await res.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const newMain = newDoc.querySelector('main');

        if (!newMain) {
            // Not a standard page with <main>, fallback
            window.location.href = fullTarget;
            return;
        }

        // Clean up previous dynamically injected SPA scripts
        document.querySelectorAll('script[data-spa-script]').forEach(el => el.remove());

        // Destroy Alpine bindings on the old tree to prevent memory leaks
        if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
            window.Alpine.destroyTree(currentMain);
        }

        // Swap main content
        currentMain.innerHTML = newMain.innerHTML;

        // Copy classes and attributes if any
        if (newMain.className) {
            currentMain.className = newMain.className;
        }

        // Update document title
        if (newDoc.title) {
            document.title = newDoc.title;
        }

        // Update breadcrumbs and sidebar
        updateNavbarBreadcrumb(newDoc);
        updateSidebarActive(fullTarget);

        // Update browser URL
        if (pushState) {
            window.history.pushState({ spa: true, url: fullTarget }, '', fullTarget);
        }
        currentLoadedUrl = fullTarget;

        // Scroll to top
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });

        // Run scripts and reinitialize Alpine & Lucide
        runScripts(currentMain, newDoc);

        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
            window.Alpine.initTree(currentMain);
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Dispatch custom event for React components or page listeners
        window.dispatchEvent(new CustomEvent('spa:navigated', {
            detail: { url: fullTarget, title: newDoc.title }
        }));

    } catch (err) {
        console.error('SPA Navigation error, falling back to full reload:', err);
        window.location.href = fullTarget;
    } finally {
        currentMain.style.opacity = '1';
        currentMain.style.transform = '';
        currentMain.style.transition = '';
        setTimeout(() => {
            currentMain.style.opacity = '';
        }, 120);
        finishProgress();
        isNavigating = false;
    }
}

/**
 * Initialize the SPA router listeners.
 */
export function initSpaRouter() {
    if (typeof window === 'undefined') return;

    // Clean up any leftover transform on <main> from previous states
    const mainEl = document.querySelector('main');
    if (mainEl) {
        mainEl.style.transform = '';
        mainEl.style.transition = '';
    }

    // Intercept internal link clicks
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (!a) return;

        if (isNavigableLink(a, e)) {
            e.preventDefault();
            navigateTo(a.href, true);
        }
    });

    // Handle browser Back / Forward buttons
    window.addEventListener('popstate', (e) => {
        navigateTo(window.location.href, false);
    });

    // Handle initial state
    if (!window.history.state) {
        window.history.replaceState({ spa: true, url: window.location.href }, '', window.location.href);
    }
}
