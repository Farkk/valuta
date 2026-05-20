const initHeaderMenu = () => {
  const menu = document.querySelector('[data-header-mobile-menu]');
  const toggle = document.querySelector('[data-header-menu-toggle]');
  const closeButton = document.querySelector('[data-header-menu-close]');
  const overlay = document.querySelector('[data-header-mobile-menu-overlay]');

  if (!menu || !toggle) {
    return;
  }

  let isOpen = false;
  let lastFocusedElement = null;

  const setOpen = (nextOpen) => {
    isOpen = nextOpen;

    menu.classList.toggle('is-open', isOpen);
    menu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    toggle.classList.toggle('is-active', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.body.classList.toggle('is-header-menu-open', isOpen);

    if (overlay) {
      overlay.classList.toggle('is-open', isOpen);
      overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    if (isOpen) {
      lastFocusedElement = document.activeElement;
      closeButton?.focus();
      return;
    }

    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  };

  const openMenu = () => setOpen(true);
  const closeMenu = () => setOpen(false);
  const toggleMenu = () => (isOpen ? closeMenu() : openMenu());

  toggle.addEventListener('click', toggleMenu);
  closeButton?.addEventListener('click', closeMenu);
  overlay?.addEventListener('click', closeMenu);

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  menu.querySelectorAll('[data-modal-target]').forEach((button) => {
    button.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isOpen) {
      closeMenu();
    }
  });
};

export default initHeaderMenu;
