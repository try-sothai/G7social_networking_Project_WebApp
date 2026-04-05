document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const searchToggle = document.querySelector('.search-toggle-btn');
    const searchBar = document.querySelector('.search-bar');
    const overlay = document.querySelector('[data-shell-overlay], .overlay');
    const sidebarBreakpoint = 992;
    const searchBreakpoint = 768;

    const isCompactSidebar = () => window.innerWidth <= sidebarBreakpoint;
    const isCompactSearch = () => window.innerWidth <= searchBreakpoint;

    function closeSidebar() {
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('sidebar-open');
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    }

    function openSidebar() {
        if (!sidebar || !overlay || !isCompactSidebar()) return;
        sidebar.classList.add('active');
        overlay.classList.add('active');
        body.classList.add('sidebar-open');
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'true');
        }
    }

    function toggleSidebar() {
        if (!sidebar || !overlay || !isCompactSidebar()) return;
        if (sidebar.classList.contains('active')) {
            closeSidebar();
            return;
        }
        openSidebar();
    }

    function closeSearch() {
        if (!searchBar) return;
        searchBar.classList.remove('active');
        body.classList.remove('search-open');
        if (searchToggle) {
            searchToggle.setAttribute('aria-expanded', 'false');
        }
    }

    function openSearch() {
        if (!searchBar || !isCompactSearch()) return;
        searchBar.classList.add('active');
        body.classList.add('search-open');
        if (searchToggle) {
            searchToggle.setAttribute('aria-expanded', 'true');
        }
    }

    function toggleSearch() {
        if (!searchBar || !isCompactSearch()) return;
        if (searchBar.classList.contains('active')) {
            closeSearch();
            return;
        }
        openSearch();
        const searchInput = searchBar.querySelector('input, textarea');
        if (searchInput) {
            requestAnimationFrame(() => searchInput.focus());
        }
    }

    if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.addEventListener('click', toggleSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            closeSidebar();
            closeSearch();
        });
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
        if (!isCompactSidebar()) {
            closeSidebar();
        }
        if (!isCompactSearch()) {
            closeSearch();
        }
    });
});
