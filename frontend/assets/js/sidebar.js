import { routes } from './config.js';

export function applySidebarRoutes() {
    const currentPath = window.location.pathname;

    document.querySelectorAll('[data-route]').forEach((link) => {
        const routeName = link.dataset.route;
        const href = routes[routeName];

        if (!href) {
            return;
        }

        link.href = href;

        const linkPath = new URL(href).pathname;
        link.classList.toggle('active', linkPath === currentPath);
    });
}
