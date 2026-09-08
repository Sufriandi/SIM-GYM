import "./bootstrap";
// Pastikan baris ini mengimpor CSS
import Alpine from "alpinejs";
import Swal from "sweetalert2";
import "../css/app.css";
import collapse from '@alpinejs/collapse';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
window.Swal = Swal;

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

// Auto-run lucide icons on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.lucide.createIcons());
} else {
    window.lucide.createIcons();
}

// Instant link prefetching on hover / touchstart for sub-second page transitions
if (typeof window !== 'undefined' && 'IntersectionObserver' in window) {
    const prefetched = new Set();
    const prefetchUrl = (url) => {
        if (!url || prefetched.has(url)) return;
        prefetched.add(url);
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    };

    const handleHover = (e) => {
        const a = e.target.closest('a');
        if (!a || !a.href) return;
        if (a.origin !== window.location.origin) return;
        if (a.pathname.startsWith('/logout')) return;
        if (a.hasAttribute('download') || a.getAttribute('target') === '_blank') return;
        prefetchUrl(a.href);
    };

    document.addEventListener('mouseover', handleHover, { passive: true });
    document.addEventListener('touchstart', handleHover, { passive: true });
}

