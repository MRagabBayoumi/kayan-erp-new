/**
 * Kayan ERP — Pre-render Theme & Density Initializer
 * Prevents theme and density flickering on initial page paint
 */
(function () {
    try {
        const savedTheme = localStorage.getItem('kayan_theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        const savedDensity = localStorage.getItem('kayan_density');
        if (savedDensity) {
            document.documentElement.setAttribute('data-density', savedDensity);
        }
    } catch (e) {
        // LocalStorage might be disabled in private browsing
    }
})();
