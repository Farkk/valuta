/**
 * Кастомные селекты валют (выпадающий список).
 */
const closeSelect = (root) => {
  const toggle = root.querySelector('[data-currency-select-toggle]');
  const panel = root.querySelector('[data-currency-select-panel]');

  root.classList.remove('is-open');
  toggle?.setAttribute('aria-expanded', 'false');
  panel?.setAttribute('hidden', '');
};

const openSelect = (root) => {
  const toggle = root.querySelector('[data-currency-select-toggle]');
  const panel = root.querySelector('[data-currency-select-panel]');

  root.classList.add('is-open');
  toggle?.setAttribute('aria-expanded', 'true');
  panel?.removeAttribute('hidden');
};

const initCurrencySelect = () => {
  const roots = document.querySelectorAll('[data-currency-select]');

  if (roots.length === 0) {
    return;
  }

  roots.forEach((root) => {
    const toggle = root.querySelector('[data-currency-select-toggle]');
    const valueEl = root.querySelector('[data-currency-select-value]');
    const options = root.querySelectorAll('[data-currency-select-option]');

    toggle?.addEventListener('click', (event) => {
      event.stopPropagation();

      const isOpen = root.classList.contains('is-open');

      roots.forEach((other) => {
        if (other !== root) {
          closeSelect(other);
        }
      });

      if (isOpen) {
        closeSelect(root);
      } else {
        openSelect(root);
      }
    });

    options.forEach((option) => {
      option.addEventListener('click', () => {
        const label = option.getAttribute('data-label') || option.textContent || '';
        const code = option.getAttribute('data-code') || '';
        const url = option.getAttribute('data-url') || '';

        if (root.hasAttribute('data-currency-select-navigate') && url) {
          window.location.assign(url);
          return;
        }

        if (valueEl) {
          valueEl.textContent = label;
        }

        root.dataset.selectedCurrency = code;

        options.forEach((item) => {
          item.setAttribute('aria-selected', item === option ? 'true' : 'false');
        });

        root.dispatchEvent(new CustomEvent('currency-select:change', {
          bubbles: true,
          detail: { label, code },
        }));

        closeSelect(root);
      });
    });
  });

  document.addEventListener('click', () => {
    roots.forEach((root) => closeSelect(root));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      roots.forEach((root) => closeSelect(root));
    }
  });
};

export default initCurrencySelect;
