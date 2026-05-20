import { getActiveCurrencyCode } from './currency-select';

const parseAmount = (value) => {
  const normalized = String(value ?? '').replace(/\s/g, '').replace(',', '.');
  const amount = Number.parseFloat(normalized);

  return Number.isFinite(amount) ? amount : 0;
};

const getHeroTab = () => {
  const activeTab = document.querySelector('.hero [data-converter-tab].active')
    || document.querySelector('[data-converter-tab].active');

  return activeTab?.dataset.converterTab || '';
};

const getDefaultTab = () => document.body.dataset.bankDefaultTab || 'buy';

export const getBankPageContext = () => {
  const amount = parseAmount(document.querySelector('.hero input[name="converter-amount"]')?.value);
  const currency = (getActiveCurrencyCode() || 'usd').toLowerCase();
  const tab = getHeroTab() || getDefaultTab();

  return { amount, currency, tab };
};

export const buildBankPageUrl = (bankCode, context = null) => {
  const code = String(bankCode ?? '').trim().toLowerCase();

  if (code === '') {
    return '';
  }

  const { amount, currency, tab } = context ?? getBankPageContext();
  const amountInt = Math.round(amount);
  let path = `/bank/${encodeURIComponent(code)}/`;

  if (amountInt > 0 && currency && currency !== 'rub') {
    path += `${amountInt}${currency}_rub/`;
  }

  const url = new URL(path, window.location.origin);
  const defaultTab = getDefaultTab();

  if (tab && tab !== defaultTab) {
    url.searchParams.set('tab', tab);
  }

  return `${url.pathname}${url.search}`;
};

export const syncBankDetailLinks = () => {
  const context = getBankPageContext();

  document.querySelectorAll('[data-bank-code]').forEach((card) => {
    const bankCode = card.dataset.bankCode || '';
    const url = buildBankPageUrl(bankCode, context);
    const overlay = card.querySelector('.bank-rate-card__overlay');

    if (overlay && url) {
      overlay.href = url;
    }
  });
};
