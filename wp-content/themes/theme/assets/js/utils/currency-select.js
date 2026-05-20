export const getConverterActions = () => document.querySelector('.hero .converter__actions');

export const getConverterFromSelect = () => {
  const scope = getConverterActions() || document.querySelector('.hero');
  const selects = Array.from(scope?.querySelectorAll('[data-currency-select]') || []);

  return selects.find((item) => (item.dataset.selectedCurrency || '').toLowerCase() !== 'rub') || selects[0] || null;
};

export const getBanksTitleSelect = () => (
  document.querySelector('#banks-list-title-currency')
  || document.querySelector('.currency-select--layout-banks-title[data-currency-select]')
);

export const getActiveCurrencyCode = () => {
  const converterSelect = getConverterFromSelect();

  if (converterSelect) {
    return converterSelect.dataset.selectedCurrency || 'usd';
  }

  return getBanksTitleSelect()?.dataset.selectedCurrency || 'usd';
};

export const setCurrencySelectValue = (root, code) => {
  if (!root) {
    return;
  }

  const option = Array.from(root.querySelectorAll('[data-currency-select-option]'))
    .find((item) => (item.dataset.code || '').toLowerCase() === String(code).toLowerCase());

  if (!option) {
    return;
  }

  root.dataset.selectedCurrency = option.dataset.code || '';
  const valueEl = root.querySelector('[data-currency-select-value]');

  if (valueEl) {
    valueEl.textContent = option.dataset.label || option.textContent || '';
  }

  root.querySelectorAll('[data-currency-select-option]').forEach((item) => {
    item.setAttribute('aria-selected', item === option ? 'true' : 'false');
  });
};
