document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const searchToggle = document.querySelector('.search-toggle-btn');
    const searchBar = document.querySelector('.search-bar');
    const overlay = document.querySelector('[data-shell-overlay], .overlay');
    const sidebarBreakpoint = 992;
    const searchBreakpoint = 768;
    const COLLAPSE_KEY = 'lc_sidebar_collapsed';

    const isCompactSidebar = () => window.innerWidth <= sidebarBreakpoint;
    const isCompactSearch  = () => window.innerWidth <= searchBreakpoint;

    // ── Mobile: slide-in / slide-out ─────────────────────────
    function closeSidebar() {
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('sidebar-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
    }

    function openSidebar() {
        if (!sidebar || !overlay || !isCompactSidebar()) return;
        sidebar.classList.add('active');
        overlay.classList.add('active');
        body.classList.add('sidebar-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'true');
    }

    function toggleMobileSidebar() {
        sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
    }

    // ── Desktop: collapse to icon-only ───────────────────────
    function collapseDesktop() {
        if (!sidebar) return;
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-is-collapsed');
        localStorage.setItem(COLLAPSE_KEY, '1');
    }

    function expandDesktop() {
        if (!sidebar) return;
        sidebar.classList.remove('collapsed');
        body.classList.remove('sidebar-is-collapsed');
        localStorage.removeItem(COLLAPSE_KEY);
    }

    function toggleDesktopCollapse() {
        sidebar.classList.contains('collapsed') ? expandDesktop() : collapseDesktop();
    }

    // Restore saved collapse state on page load (desktop only)
    if (sidebar && !isCompactSidebar() && localStorage.getItem(COLLAPSE_KEY) === '1') {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-is-collapsed');
    }

    // ── Hamburger click ──────────────────────────────────────
    if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.addEventListener('click', () => {
            if (isCompactSidebar()) {
                toggleMobileSidebar();
            } else {
                toggleDesktopCollapse();
            }
        });
    }

    // ── Search ───────────────────────────────────────────────
    function closeSearch() {
        if (!searchBar) return;
        searchBar.classList.remove('active');
        body.classList.remove('search-open');
        if (searchToggle) searchToggle.setAttribute('aria-expanded', 'false');
    }

    function openSearch() {
        if (!searchBar || !isCompactSearch()) return;
        searchBar.classList.add('active');
        body.classList.add('search-open');
        if (searchToggle) searchToggle.setAttribute('aria-expanded', 'true');
    }

    function toggleSearch() {
        if (!searchBar || !isCompactSearch()) return;
        if (searchBar.classList.contains('active')) {
            closeSearch();
            return;
        }
        openSearch();
        const searchInput = searchBar.querySelector('input, textarea');
        if (searchInput) requestAnimationFrame(() => searchInput.focus());
    }

    if (overlay) {
        overlay.addEventListener('click', () => { closeSidebar(); closeSearch(); });
    }

    if (searchToggle) {
        searchToggle.setAttribute('aria-expanded', 'false');
        searchToggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            toggleSearch();
        });
    }

    document.addEventListener('click', (event) => {
        if (!searchBar || !searchToggle || !isCompactSearch()) return;
        if (searchBar.contains(event.target) || searchToggle.contains(event.target)) return;
        closeSearch();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeSidebar();
        closeSearch();
    });

    window.addEventListener('resize', () => {
        if (!isCompactSidebar()) closeSidebar();
        if (!isCompactSearch())  closeSearch();
    });
});
