const STAR_PATHS = [
  'M16.8234 6.111H10.3974L8.41142 0L6.44043 8.083L8.41142 12.223L13.6114 16L11.6254 9.889L16.8234 6.111Z',
  'M6.426 6.111H0L5.2 9.888L3.213 16L8.413 12.223V0L6.426 6.111Z',
];

let modalElements = null;
let lastFocusedElement = null;

const getModalElements = () => {
  if (modalElements) {
    return modalElements;
  }

  const modal = document.querySelector('[data-review-text-modal]');
  if (!modal) {
    return null;
  }

  modalElements = {
    modal,
    name: modal.querySelector('[data-review-modal-name]'),
    bank: modal.querySelector('[data-review-modal-bank]'),
    text: modal.querySelector('[data-review-modal-text]'),
    meta: modal.querySelector('[data-review-modal-meta]'),
    rating: modal.querySelector('[data-review-modal-rating]'),
    closeButtons: modal.querySelectorAll('[data-review-text-modal-close]'),
  };

  return modalElements;
};

const renderStars = (container, rating) => {
  if (!container) {
    return;
  }

  container.innerHTML = '';
  const safeRating = Math.max(0, Math.min(5, Number(rating) || 0));

  if (safeRating === 0) {
    container.setAttribute('aria-hidden', 'true');
    return;
  }

  container.setAttribute('aria-hidden', 'false');
  container.setAttribute('aria-label', `Рейтинг: ${safeRating} из 5`);

  for (let index = 0; index < 5; index += 1) {
    const active = index < safeRating;
    const fill = active ? '#E30611' : '#B9D0E2';
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('width', '17');
    svg.setAttribute('height', '16');
    svg.setAttribute('viewBox', '0 0 17 16');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('aria-hidden', 'true');

    STAR_PATHS.forEach((pathData) => {
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', pathData);
      path.setAttribute('fill', fill);
      svg.appendChild(path);
    });

    container.appendChild(svg);
  }
};

const renderMeta = (container, city, date) => {
  if (!container) {
    return;
  }

  container.innerHTML = '';

  if (city) {
    const cityEl = document.createElement('span');
    cityEl.className = 'review-text-modal__meta-city';

    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    icon.setAttribute('width', '11');
    icon.setAttribute('height', '14');
    icon.setAttribute('viewBox', '0 0 11 14');
    icon.setAttribute('fill', 'none');
    icon.setAttribute('aria-hidden', 'true');

    [
      'M5.49994 2.95105C4.09456 2.95105 2.95117 4.09444 2.95117 5.49982C2.95117 6.90521 4.09456 8.0486 5.49994 8.0486C6.90533 8.0486 8.04872 6.90521 8.04872 5.49982C8.04872 4.09444 6.90536 2.95105 5.49994 2.95105ZM5.49994 6.97544C4.68629 6.97544 4.02433 6.31347 4.02433 5.49982C4.02433 4.68617 4.68629 4.02421 5.49994 4.02421C6.3136 4.02421 6.97556 4.68617 6.97556 5.49982C6.97556 6.31347 6.3136 6.97544 5.49994 6.97544Z',
      'M5.5 0C2.46728 0 0 2.46731 0 5.5V5.65204C0 7.18581 0.879346 8.97314 2.61369 10.9643C3.87096 12.4077 5.11066 13.4142 5.16278 13.4564L5.5 13.7289L5.83722 13.4564C5.88937 13.4143 7.12907 12.4078 8.38631 10.9643C10.1206 8.97314 11 7.18584 11 5.65206V5.50003C11 2.46731 8.53272 0 5.5 0ZM9.92684 5.65206C9.92684 8.24406 6.5871 11.3817 5.5 12.3342C4.4126 11.3814 1.07316 8.24387 1.07316 5.65206V5.50003C1.07316 3.05907 3.05905 1.07319 5.5 1.07319C7.94095 1.07319 9.92684 3.05907 9.92684 5.50003V5.65206Z',
    ].forEach((pathData) => {
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', pathData);
      path.setAttribute('fill', '#22284B');
      icon.appendChild(path);
    });

    cityEl.append(icon, document.createTextNode(city));
    container.appendChild(cityEl);
  }

  if (date) {
    const dateEl = document.createElement('span');
    dateEl.className = 'review-text-modal__meta-date';
    dateEl.textContent = date;
    container.appendChild(dateEl);
  }
};

const closeModal = () => {
  const elements = getModalElements();
  if (!elements) {
    return;
  }

  elements.modal.classList.remove('is-active');
  elements.modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('review-text-modal-open');

  if (lastFocusedElement instanceof HTMLElement) {
    lastFocusedElement.focus();
    lastFocusedElement = null;
  }
};

const openModal = (card) => {
  const elements = getModalElements();
  if (!elements) {
    return;
  }

  const name = card.dataset.reviewName || '';
  const fullText = card.dataset.reviewFullText || '';
  const bank = card.dataset.reviewBank || '';
  const city = card.dataset.reviewCity || '';
  const date = card.dataset.reviewDate || '';
  const rating = card.dataset.reviewRating || '0';

  if (elements.name) {
    elements.name.textContent = name;
  }

  if (elements.bank) {
    if (bank) {
      elements.bank.textContent = bank;
      elements.bank.hidden = false;
    } else {
      elements.bank.textContent = '';
      elements.bank.hidden = true;
    }
  }

  if (elements.text) {
    elements.text.textContent = fullText;
  }

  renderMeta(elements.meta, city, date);
  renderStars(elements.rating, rating);

  lastFocusedElement = document.activeElement;
  elements.modal.classList.add('is-active');
  elements.modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('review-text-modal-open');
  elements.closeButtons[0]?.focus();
};

const initModal = () => {
  const elements = getModalElements();
  if (!elements || elements.modal.dataset.reviewModalBound === 'true') {
    return;
  }

  elements.closeButtons.forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && elements.modal.classList.contains('is-active')) {
      closeModal();
    }
  });

  elements.modal.dataset.reviewModalBound = 'true';
};

const bindCard = (card) => {
  if (!card || card.dataset.reviewCardBound === 'true') {
    return;
  }

  const text = card.querySelector('[data-review-text]');
  const toggle = card.querySelector('[data-review-toggle]');

  if (!text || !toggle) {
    return;
  }

  toggle.addEventListener('click', () => {
    openModal(card);
  });

  card.dataset.reviewCardBound = 'true';
};

const updateCard = (card) => {
  const text = card.querySelector('[data-review-text]');
  const toggle = card.querySelector('[data-review-toggle]');

  if (!text || !toggle) {
    return;
  }

  toggle.hidden = text.scrollHeight <= text.clientHeight + 1;
};

const processCards = () => {
  initModal();
  document.querySelectorAll('.reviews__card').forEach((card) => {
    bindCard(card);
    updateCard(card);
  });
};

export default function initReviewsCards() {
  const run = () => {
    if (document.fonts?.ready) {
      document.fonts.ready.then(processCards);
      return;
    }

    window.setTimeout(processCards, 100);
  };

  run();
  window.addEventListener('resize', run);
  document.addEventListener('reviews:updated', run);
}
