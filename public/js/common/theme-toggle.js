document.addEventListener('DOMContentLoaded', () => {
    const THEME_KEY = 'localconnect_theme';
    const body = document.body;
    const themeBtn = Array.from(document.querySelectorAll('.header-actions .icon-btn')).find((btn) => {
        const icon = btn.querySelector('i');
        return icon && icon.classList.contains('fa-moon');
    });

    function applyTheme(mode) {
        if (mode === 'dark') {
            body.classList.add('dark-mode');
        } else {
            body.classList.remove('dark-mode');
        }
    }

    let saved = localStorage.getItem(THEME_KEY);
    if (!saved) {
        saved = 'dark';
        localStorage.setItem(THEME_KEY, saved);
    }
    applyTheme(saved === 'dark' ? 'dark' : 'light');

    if (!themeBtn) return;

    themeBtn.setAttribute('title', 'Toggle dark mode');
    themeBtn.addEventListener('click', () => {
        const next = body.classList.contains('dark-mode') ? 'light' : 'dark';
        applyTheme(next);
        localStorage.setItem(THEME_KEY, next);
    });
});

