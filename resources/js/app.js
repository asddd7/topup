import './pages/theme';
import './loading';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import './pages/dashboard';

/* Keep fixed navigation and full-screen overlays aligned with the
   real mobile viewport when the browser chrome or keyboard changes. */
function syncViewportHeight() {
    const viewportHeight = window.visualViewport?.height || window.innerHeight;

    document.documentElement.style.setProperty(
        '--app-viewport-height',
        `${Math.round(viewportHeight)}px`
    );
}

syncViewportHeight();

window.addEventListener('resize', syncViewportHeight, { passive: true });
window.visualViewport?.addEventListener('resize', syncViewportHeight, {
    passive: true,
});

window.addEventListener('pageshow', () => {
    window.hideGlobalLoading?.();
});
