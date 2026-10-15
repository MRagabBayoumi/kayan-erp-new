/**
 * Kayan ERP — Frontend Application Client
 * Odoo-Inspired Interactions, Theme & Density Engine
 */

import { initCommonUI } from './common.js';
import { initBarcodeScanner } from './components/barcode-scanner.js';
import { initProductForm } from './pages/products-form.js';
import { initProductsIndex } from './pages/products-index.js';
import { initProductShow } from './pages/products-show.js';
import { initInventoryPage } from './pages/inventory.js';
import { initWarehousesPage } from './pages/warehouses.js';
import { initSettingsPage } from './pages/settings.js';
import { initPrintLabelPage } from './pages/print-label.js';
import { initCustomersPage } from './pages/customers.js';
import { initSuppliersPage } from './pages/suppliers.js';
import { initShippingOfficesPage } from './pages/shipping-offices.js';
import { initPurchases } from './pages/purchases.js';
import { initSales } from './pages/sales.js';
import { initShifts } from './pages/shifts.js';
import { initPrintTemplatesPage } from './pages/print-templates.js';

document.addEventListener('DOMContentLoaded', () => {
    initThemeAndDensity();
    initAppLauncher();
    initSidebar();
    initDropdowns();
    initKeyboardShortcuts();
    initGlobalSearch();
    initCommonUI();
    initBarcodeScanner();
    initProductForm();
    initProductsIndex();
    initProductShow();
    initInventoryPage();
    initWarehousesPage();
    initSettingsPage();
    initPrintLabelPage();
    initCustomersPage();
    initSuppliersPage();
    initShippingOfficesPage();
    initPurchases();
    initSales();
    initShifts();
    initPrintTemplatesPage();
});

/**
 * Theme & Density Control
 */
function initThemeAndDensity() {
    const themeToggleBtn = document.getElementById('kayanThemeToggle');
    const densityToggleBtn = document.getElementById('kayanDensityToggle');

    // Theme Toggle
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('kayan_theme', newTheme);
            updateThemeIcons(newTheme);
        });
    }

    // Density Toggle (Comfortable vs Enterprise Table View)
    if (densityToggleBtn) {
        densityToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const currentDensity = document.documentElement.getAttribute('data-density') || 'comfortable';
            const newDensity = currentDensity === 'enterprise' ? 'comfortable' : 'enterprise';
            
            document.documentElement.setAttribute('data-density', newDensity);
            localStorage.setItem('kayan_density', newDensity);
            updateDensityButton(newDensity);
        });
    }

    // Set initial icon states
    const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeIcons(activeTheme);

    const activeDensity = document.documentElement.getAttribute('data-density') || 'comfortable';
    updateDensityButton(activeDensity);
}

function updateThemeIcons(theme) {
    const themeToggleBtn = document.getElementById('kayanThemeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.setAttribute('title', theme === 'dark' ? 'تبديل إلى الوضع النهاري' : 'تبديل إلى الوضع الليلي');
        themeToggleBtn.setAttribute('aria-label', theme === 'dark' ? 'تبديل إلى الوضع النهاري' : 'تبديل إلى الوضع الليلي');
    }
}

function updateDensityButton(density) {
    const densityLabel = document.getElementById('densityLabel');
    if (densityLabel) {
        densityLabel.textContent = density === 'enterprise' ? 'مؤسسي مكثف' : 'افتراضي مريح';
    }
}

/**
 * App Launcher (Odoo 3x3 App Drawer)
 */
function initAppLauncher() {
    const launcherBtns = document.querySelectorAll('[data-action="open-app-launcher"]');
    const backdrop = document.getElementById('appLauncherBackdrop');
    const closeBtn = document.getElementById('appLauncherClose');

    if (!backdrop) return;

    function openLauncher() {
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeLauncher() {
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    launcherBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openLauncher();
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeLauncher);
    }

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            closeLauncher();
        }
    });

    window.closeAppLauncher = closeLauncher;
}

/**
 * Sidebar Navigation & Responsive Drawer
 */
function initSidebar() {
    const sidebar = document.getElementById('erpSidebar');
    const desktopToggle = document.getElementById('sidebarDesktopToggle');
    const topbarToggle = document.getElementById('sidebarToggleBtn') || document.getElementById('sidebarMobileToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    function isMobile() {
        return window.innerWidth <= 900;
    }

    function openMobileSidebar() {
        sidebar.classList.add('open-mobile');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateSidebarAria();
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('open-mobile');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
        updateSidebarAria();
    }

    function toggleSidebar() {
        if (isMobile()) {
            if (sidebar.classList.contains('open-mobile')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
            if (isCurrentlyCollapsed) {
                sidebar.classList.remove('collapsed');
                localStorage.setItem('kayan_sidebar_collapsed', 'false');
            } else {
                sidebar.classList.add('collapsed');
                localStorage.setItem('kayan_sidebar_collapsed', 'true');
            }
            updateSidebarAria();
        }
    }

    function updateSidebarAria() {
        const isCollapsed = sidebar.classList.contains('collapsed');
        const isOpenMobile = sidebar.classList.contains('open-mobile');
        const isExpanded = isMobile() ? isOpenMobile : !isCollapsed;

        if (desktopToggle) {
            desktopToggle.setAttribute('aria-expanded', isExpanded.toString());
            desktopToggle.title = isCollapsed ? 'توسيع القائمة' : 'طي القائمة';
        }
        if (topbarToggle) {
            topbarToggle.setAttribute('aria-expanded', isExpanded.toString());
            topbarToggle.title = isExpanded ? 'طي القائمة الجانبية' : 'فتح القائمة الجانبية';
        }
    }

    // Load saved preference on desktop
    const savedCollapsed = localStorage.getItem('kayan_sidebar_collapsed');
    if (!isMobile()) {
        if (savedCollapsed === 'true') {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }
    } else {
        sidebar.classList.remove('collapsed');
    }
    updateSidebarAria();

    // Event listeners
    if (topbarToggle) {
        topbarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    if (desktopToggle) {
        desktopToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    // Close mobile sidebar on outside click
    document.addEventListener('click', (e) => {
        if (isMobile() && sidebar.classList.contains('open-mobile')) {
            if (!sidebar.contains(e.target) && !topbarToggle?.contains(e.target)) {
                closeMobileSidebar();
            }
        }
    });

    // Window resize handling
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            closeMobileSidebar();
            const pref = localStorage.getItem('kayan_sidebar_collapsed') === 'true';
            sidebar.classList.toggle('collapsed', pref);
        } else {
            sidebar.classList.remove('collapsed');
        }
        updateSidebarAria();
    });

    window.toggleSidebar = toggleSidebar;
    window.openMobileSidebar = openMobileSidebar;
    window.closeMobileSidebar = closeMobileSidebar;
}

/**
 * Generic Dropdown Handlers
 */
function initDropdowns() {
    const toggles = document.querySelectorAll('[data-dropdown-toggle]');

    toggles.forEach(toggle => {
        const targetId = toggle.getAttribute('data-dropdown-toggle');
        const menu = document.getElementById(targetId);

        if (!menu) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = menu.classList.contains('open');

            // Close other open menus
            document.querySelectorAll('.erp-dropdown-menu.open').forEach(el => {
                if (el !== menu) el.classList.remove('open');
            });

            if (isOpen) {
                menu.classList.remove('open');
            } else {
                menu.classList.add('open');
            }
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.erp-dropdown-menu.open').forEach(menu => {
            menu.classList.remove('open');
        });
    });
}

/**
 * Keyboard Shortcuts (Ctrl+K for Search, Esc to Close)
 */
function initKeyboardShortcuts() {
    window.addEventListener('keydown', (e) => {
        // Ctrl+K or Cmd+K: Focus global search
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            const searchInput = document.getElementById('globalSearchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        // Escape: Close modals, drawers, and dropdowns
        if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
            const launcherBackdrop = document.getElementById('appLauncherBackdrop');
            if (launcherBackdrop && launcherBackdrop.classList.contains('open')) {
                if (typeof window.closeAppLauncher === 'function') {
                    window.closeAppLauncher();
                } else {
                    launcherBackdrop.classList.remove('open');
                    document.body.style.overflow = '';
                }
            }

            document.querySelectorAll('.erp-dropdown-menu.open').forEach(menu => {
                menu.classList.remove('open');
            });

            const sidebar = document.getElementById('erpSidebar');
            if (sidebar && sidebar.classList.contains('open-mobile')) {
                sidebar.classList.remove('open-mobile');
            }
        }
    });
}

/**
 * Global Search in Topbar
 */
function initGlobalSearch() {
    const searchInput = document.getElementById('globalSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = searchInput.value.trim();
                if (q) {
                    window.location.href = '/products?search=' + encodeURIComponent(q);
                }
            }
        });
    }
}

