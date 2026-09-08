import "./bootstrap";
// Pastikan baris ini mengimpor CSS
import Alpine from "alpinejs";
import Swal from "sweetalert2";
import "../css/app.css";
import collapse from '@alpinejs/collapse';
import { createIcons, icons } from './icons';
import { initSpaRouter, navigateTo } from './spa-router';

window.Alpine = Alpine;
window.Swal = Swal;
window.navigateTo = navigateTo;

// Bundled Lucide icons (offline & high performance)
window.lucide = {
    createIcons: (options = {}) => createIcons({ icons, ...options }),
    icons,
};

// Register Alpine plugins & components
Alpine.plugin(collapse);

Alpine.data('sidebarNav', (defaultOpen) => ({
    openKehadiran: JSON.parse(localStorage.getItem('sidebar-openKehadiran') ?? (defaultOpen ? 'true' : 'false')),
    toggleKehadiran() {
        this.openKehadiran = !this.openKehadiran;
        localStorage.setItem('sidebar-openKehadiran', JSON.stringify(this.openKehadiran));
    },
}));

Alpine.start();

// Initialize Persistent Shell SPA router
initSpaRouter();

// Auto-run lucide icons on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.lucide.createIcons());
} else {
    window.lucide.createIcons();
}

// Instant link prefetching on hover / touchstart for sub-second page transitions
if (typeof window !== 'undefined') {
    const prefetched = new Set();
    let hoverTimeout = null;

    const prefetchUrl = (url) => {
        if (!url || prefetched.has(url)) return;
        prefetched.add(url);

        try {
            // Modern fetch prefetch with low priority
            fetch(url, {
                credentials: 'same-origin',
                priority: 'low',
                headers: { 'Purpose': 'prefetch', 'Sec-Purpose': 'prefetch' }
            }).catch(() => {});
        } catch (_) {}
    };

    const isPrefetchable = (a) => {
        if (!a || !a.href) return false;
        if (a.origin !== window.location.origin) return false;
        if (a.pathname === window.location.pathname) return false;
        if (a.pathname.includes('/logout') || a.pathname.includes('/delete')) return false;
        if (a.hasAttribute('download') || a.getAttribute('target') === '_blank') return false;
        return true;
    };

    document.addEventListener('mouseover', (e) => {
        const a = e.target.closest('a');
        if (!isPrefetchable(a)) return;
        clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(() => prefetchUrl(a.href), 65);
    }, { passive: true });

    document.addEventListener('mouseout', () => {
        clearTimeout(hoverTimeout);
    }, { passive: true });

    document.addEventListener('touchstart', (e) => {
        const a = e.target.closest('a');
        if (isPrefetchable(a)) prefetchUrl(a.href);
    }, { passive: true });
}

