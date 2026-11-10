/** Mobile menu toggle; the menu remains visible without JavaScript. */
((Drupal, once) => {
  Drupal.behaviors.ancoraSidebar = {
    attach(context) {
      once('ancora-sidebar', '.ancora-sidebar', context).forEach((sidebar) => {
        const toggle = sidebar.querySelector('.ancora-sidebar__toggle');
        const content = sidebar.querySelector('.ancora-sidebar__content');
        toggle.hidden = false;
        const mobile = window.matchMedia('(max-width: 800px)');
        const setCollapsed = (collapsed) => {
          sidebar.classList.toggle('is-collapsed', collapsed);
          toggle.setAttribute('aria-expanded', String(!collapsed));
          if (collapsed && content.contains(document.activeElement)) toggle.focus();
        };
        setCollapsed(mobile.matches);
        toggle.addEventListener('click', () => setCollapsed(!sidebar.classList.contains('is-collapsed')));
        sidebar.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && mobile.matches && !sidebar.classList.contains('is-collapsed')) setCollapsed(true);
        });
        mobile.addEventListener('change', (event) => setCollapsed(event.matches));
      });
    },
  };
})(Drupal, once);
