const parseAmount = (value) => {
  const normalized = String(value || '').replace(/\s/g, '').replace(',', '.');
  const amount = Number.parseFloat(normalized);

  return Number.isFinite(amount) ? amount : 0;
};

const formatAmount = (value) => Math.round(value)
  .toString()
  .replace(/\B(?=(\d{3})+(?!\d))/g, '\u00a0');

const initSidebarConverter = () => {
  document.querySelectorAll('[data-sidebar-converter], [data-banks-sidebar-converter]').forEach((root) => {
    let rates = {};

    try {
      rates = JSON.parse(root.dataset.rates || '{}');
    } catch (_) {
      rates = {};
    }

    const fromInput = root.querySelector('[data-sidebar-from-input]')
      || root.querySelector('input[name$="-amount-from"]');
    const toInput = root.querySelector('[data-sidebar-to-input]')
      || root.querySelector('input[name$="-amount-to"]');
    const fromSelect = root.querySelector('[data-currency-select]');

    if (!fromInput || !toInput || !fromSelect) {
      return;
    }

    const recalculate = () => {
      const amount = parseAmount(fromInput.value);
      const code = (fromSelect.dataset.selectedCurrency || '').toLowerCase();
      const rate = Number(rates[code] || 0);

      toInput.value = amount > 0 && rate > 0 ? formatAmount(amount * rate) : '';
    };

    fromInput.addEventListener('input', () => {
      const amount = parseAmount(fromInput.value);
      fromInput.value = amount > 0 ? formatAmount(amount) : '';
      recalculate();
    });

    fromSelect.addEventListener('currency-select:change', recalculate);
    root.querySelector('[data-currency-swap]')?.addEventListener('click', (event) => {
      event.preventDefault();
      recalculate();
    });

    recalculate();
  });
};

export default initSidebarConverter;
