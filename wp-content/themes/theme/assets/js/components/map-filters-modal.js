import {
  getActiveCurrencyCode,
  getConverterFromSelect,
  setCurrencySelectValue,
} from '../utils/currency-select';

const formatAmount = (value) => {
  const digits = String(value || '').replace(/[^\d]/g, '');

  return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '\u00a0');
};

const getHeroTab = () => document.querySelector('.hero [data-converter-tab].active')?.dataset.converterTab || 'all';

const updateFilterVisibility = (modal) => {
  const currentTab = modal.querySelector('[data-map-filters-tab].is-active')?.dataset.mapFiltersTab || 'all';
  const currentCurrency = (modal.querySelector('[data-currency-select]')?.dataset.selectedCurrency || 'usd').toLowerCase();
  let visibleCount = 0;

  modal.querySelectorAll('[data-map-filter]').forEach((input) => {
    const item = input.closest('.bank-map-filters-modal__filter');
    const showInBuy = input.dataset.showInBuy === '1';
    const showInSell = input.dataset.showInSell === '1';
    const currencyCodes = input.dataset.currencyCodes || '';
    let visible = currentTab === 'all' || (!showInBuy && !showInSell);

    if (currentTab === 'buy' && showInBuy) visible = true;
    if (currentTab === 'sell' && showInSell) visible = true;

    if (currencyCodes) {
      const allowed = currencyCodes.split(',').map((value) => value.trim().toLowerCase()).filter(Boolean);
      visible = visible && allowed.includes(currentCurrency);
    }

    if (!visible) {
      input.checked = false;
    } else {
      visibleCount += 1;
    }

    if (item) {
      item.hidden = !visible;
    }
  });

  const list = modal.querySelector('[data-map-filters-list]');
  if (list) {
    list.hidden = visibleCount === 0;
  }
};

const initMapFiltersModal = () => {
  const modal = document.querySelector('[data-map-filters-modal]');
  const root = document.querySelector('[data-banks-list]');

  if (!modal || !root) {
    return;
  }

  const amountInput = modal.querySelector('[data-map-filters-amount]');
  const modalSelect = modal.querySelector('[data-currency-select]');

  const syncFromPage = () => {
    modal.querySelectorAll('[data-map-filters-tab]').forEach((tab) => {
      tab.classList.toggle('is-active', tab.dataset.mapFiltersTab === getHeroTab());
    });

    if (amountInput) {
      amountInput.value = formatAmount(document.querySelector('.hero input[name="converter-amount"]')?.value || '');
    }

    setCurrencySelectValue(modalSelect, getActiveCurrencyCode());

    modal.querySelectorAll('[data-map-filter]').forEach((input) => {
      const match = root.querySelector(`[data-banks-filter][data-filter-slug="${input.dataset.filterSlug || ''}"]`);
      input.checked = Boolean(match?.checked);
    });

    updateFilterVisibility(modal);
  };

  const reset = () => {
    modal.querySelectorAll('[data-map-filters-tab]').forEach((tab) => {
      tab.classList.toggle('is-active', tab.dataset.mapFiltersTab === 'all');
    });

    if (amountInput) {
      amountInput.value = '';
    }

    setCurrencySelectValue(modalSelect, 'usd');
    modal.querySelectorAll('[data-map-filter]').forEach((input) => {
      input.checked = false;
    });
    updateFilterVisibility(modal);
  };

  const close = () => {
    modal.classList.remove('is-active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  const open = () => {
    syncFromPage();
    modal.classList.add('is-active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const apply = () => {
    const selectedTab = modal.querySelector('[data-map-filters-tab].is-active')?.dataset.mapFiltersTab || 'all';
    const selectedCurrency = modalSelect?.dataset.selectedCurrency || 'usd';
    const selectedAmount = formatAmount(amountInput?.value || '');

    document.querySelectorAll('.hero [data-converter-tab]').forEach((button) => {
      button.classList.toggle('active', button.dataset.converterTab === selectedTab);
    });

    const heroAmount = document.querySelector('.hero input[name="converter-amount"]');
    if (heroAmount) {
      heroAmount.value = selectedAmount;
    }

    const heroSelect = getConverterFromSelect();
    setCurrencySelectValue(heroSelect, selectedCurrency);
    setCurrencySelectValue(document.querySelector('#banks-list-title-currency'), selectedCurrency);

    modal.querySelectorAll('[data-map-filter]').forEach((input) => {
      const match = root.querySelector(`[data-banks-filter][data-filter-slug="${input.dataset.filterSlug || ''}"]`);
      if (match) {
        match.checked = input.checked;
      }
    });

    root.dispatchEvent(new CustomEvent('banks-list:refresh'));
    close();
  };

  document.querySelectorAll('[data-banks-map-filters-open]').forEach((button) => {
    button.addEventListener('click', open);
  });

  modal.querySelectorAll('[data-map-filters-close]').forEach((button) => {
    button.addEventListener('click', close);
  });

  modal.querySelectorAll('[data-map-filters-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
      modal.querySelectorAll('[data-map-filters-tab]').forEach((button) => {
        button.classList.toggle('is-active', button === tab);
      });
      updateFilterVisibility(modal);
    });
  });

  modalSelect?.addEventListener('currency-select:change', () => updateFilterVisibility(modal));
  amountInput?.addEventListener('input', () => {
    amountInput.value = formatAmount(amountInput.value);
  });
  modal.querySelector('[data-map-filters-reset]')?.addEventListener('click', reset);
  modal.querySelector('[data-map-filters-apply]')?.addEventListener('click', apply);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-active')) {
      close();
    }
  });
};

export default initMapFiltersModal;
