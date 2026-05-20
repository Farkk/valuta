/**
 * FAQ: аккордеон с плавным открытием/закрытием (CSS grid 0fr → 1fr).
 */
const initFaqAccordion = () => {
  const root = document.querySelector('[data-faq]');

  if (!root) {
    return;
  }

  const items = root.querySelectorAll('[data-faq-item]');

  items.forEach((item) => {
    const trigger = item.querySelector('[data-faq-trigger]');

    if (!(trigger instanceof HTMLButtonElement)) {
      return;
    }

    trigger.addEventListener('click', () => {
      const isOpen = item.classList.contains('is-open');

      if (isOpen) {
        item.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        item.querySelector('[data-faq-panel]')?.setAttribute('aria-hidden', 'true');
        return;
      }

      items.forEach((other) => {
        if (other === item) {
          return;
        }

        other.classList.remove('is-open');
        const otherTrigger = other.querySelector('[data-faq-trigger]');

        if (otherTrigger instanceof HTMLButtonElement) {
          otherTrigger.setAttribute('aria-expanded', 'false');
        }

        other.querySelector('[data-faq-panel]')?.setAttribute('aria-hidden', 'true');
      });

      item.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      item.querySelector('[data-faq-panel]')?.setAttribute('aria-hidden', 'false');
    });
  });
};

export default initFaqAccordion;
