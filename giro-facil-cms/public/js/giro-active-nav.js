(function () {
    'use strict';

    function markCurrentPage() {
        var links = document.querySelectorAll('header #mainmenu a.menu-item');
        var currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
        if (currentPath === '/index.html' || currentPath === '/index-ship-freight.html') currentPath = '/';

        links.forEach(function (link) {
            var linkPath;
            try {
                linkPath = new URL(link.getAttribute('href'), window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (error) {
                return;
            }

            if (linkPath === '/index.html' || linkPath === '/index-ship-freight.html') linkPath = '/';
            var isCurrent = linkPath === currentPath;
            link.classList.toggle('active', isCurrent);

            if (isCurrent) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', markCurrentPage, { once: true });
    } else {
        markCurrentPage();
    }
}());
