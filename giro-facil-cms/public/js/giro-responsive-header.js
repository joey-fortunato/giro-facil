(function () {
    'use strict';

    var header = document.querySelector('header.transparent.scroll-light');
    if (!header) return;

    var mobileHeader = window.matchMedia('(max-width: 992px)');
    var desktopBooking = header.querySelector('#header-booking');
    var menu = header.querySelector('#mainmenu');
    var menuButton = header.querySelector('#menu-btn');
    var framePending = false;

    function updateHeaderState() {
        framePending = false;
        var isMobile = mobileHeader.matches;
        header.classList.toggle('giro-header-scrolled', isMobile && window.scrollY > 80);

        if (desktopBooking) {
            if (isMobile) desktopBooking.style.setProperty('display', 'none', 'important');
            else desktopBooking.style.removeProperty('display');
        }
        if (menu) {
            if (isMobile) {
                var isOpen = header.classList.contains('menu-open');
                menu.style.setProperty('visibility', isOpen ? 'visible' : 'hidden', 'important');
                menu.style.setProperty('opacity', isOpen ? '1' : '0', 'important');
                menu.style.setProperty('pointer-events', isOpen ? 'auto' : 'none', 'important');
            } else {
                menu.style.removeProperty('visibility');
                menu.style.removeProperty('opacity');
                menu.style.removeProperty('pointer-events');
            }
        }
    }

    function scheduleHeaderUpdate() {
        if (framePending) return;
        framePending = true;
        window.requestAnimationFrame(updateHeaderState);
    }

    window.addEventListener('scroll', scheduleHeaderUpdate, { passive: true });
    window.addEventListener('resize', scheduleHeaderUpdate);
    mobileHeader.addEventListener('change', scheduleHeaderUpdate);
    if (menuButton) menuButton.addEventListener('click', function () { window.requestAnimationFrame(scheduleHeaderUpdate); });
    updateHeaderState();
}());
