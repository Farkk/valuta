import { getActiveCurrencyCode } from '../utils/currency-select';

const escapeHtml = (value) => String(value ?? '')
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#039;');

const formatRate = (value) => Number(value || 0).toFixed(2).replace('.', ',');

const formatTotal = (value) => {
  const [integer, decimal = ''] = Number(value || 0).toFixed(2).split('.');
  const formattedInteger = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '\u00a0');
  const cleanDecimal = decimal.replace(/0+$/, '');

  return cleanDecimal ? `${formattedInteger},${cleanDecimal}` : formattedInteger;
};

const parseAmount = (value) => {
  const amount = Number.parseFloat(String(value || '').replace(/\s/g, '').replace(',', '.'));

  return Number.isFinite(amount) && amount > 0 ? amount : 0;
};

const getCurrentTab = () => document.querySelector('[data-converter-tab].active')?.dataset.converterTab || 'all';

const getCurrentCurrency = () => getActiveCurrencyCode();

const getCurrentAmount = () => parseAmount(document.querySelector('.hero input[name="converter-amount"]')?.value);

const getActiveFilters = () => Array.from(document.querySelectorAll('[data-banks-filter]:checked'))
  .map((input) => input.dataset.filterSlug)
  .filter(Boolean);

const getBankData = (card) => ({
  bankCode: card?.dataset.bankCode || '',
  bankName: card?.dataset.bankName || '',
  bankLogo: card?.dataset.bankLogo || '',
  bankUrl: card?.dataset.bankUrl || '',
  updateTime: card?.querySelector('.bank-rate-card__updated')?.textContent?.trim() || '',
});

const appendContext = (formData) => {
  const section = document.querySelector('[data-banks-list]');

  formData.append('currency', getCurrentCurrency());
  formData.append('tab', getCurrentTab());
  formData.append('filters', JSON.stringify(getActiveFilters()));
  formData.append('city_slug', section?.dataset.citySlug || '');
  formData.append('city_name', section?.dataset.cityName || '');
};

const fetchOffices = async (action, bankCode) => {
  const formData = new FormData();
  formData.append('action', action);
  formData.append('bank_code', bankCode);
  appendContext(formData);

  const response = await fetch(window.themeSettings?.ajaxUrl || '/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: formData,
  });

  return response.json();
};

const getRatesHtml = (office) => {
  const tab = getCurrentTab();
  const amount = getCurrentAmount();

  if (tab === 'sell') {
    return [
      ['Курс банка', `${formatRate(office.buy)} ₽`],
      ['Вы получите', `${amount > 0 ? formatTotal(office.buy * amount) : '0'} ₽`],
    ];
  }

  if (tab === 'buy') {
    return [
      ['Курс банка', `${formatRate(office.sell)} ₽`],
      ['Расчёт суммы', `${amount > 0 ? formatTotal(office.sell * amount) : '0'} ₽`],
    ];
  }

  return [
    ['Покупка', `${formatRate(office.buy)} ₽`],
    ['Продажа', `${formatRate(office.sell)} ₽`],
  ];
};

const getBankLogoHtml = (bankData) => {
  if (bankData.bankLogo) {
    return `<img class="bank-map-info-panel__bank-logo" src="${escapeHtml(bankData.bankLogo)}" alt="${escapeHtml(bankData.bankName || '')}">`;
  }

  return '<span class="bank-map-info-panel__bank-logo bank-map-info-panel__bank-logo--placeholder" aria-hidden="true"></span>';
};

const getOfficeBankLogoHtml = (bankData) => {
  if (bankData.bankLogo) {
    return `<img class="modal-bank-offices__office-bank-logo" src="${escapeHtml(bankData.bankLogo)}" alt="${escapeHtml(bankData.bankName || '')}">`;
  }

  return '<span class="modal-bank-offices__office-bank-logo modal-bank-offices__office-bank-logo--placeholder" aria-hidden="true"></span>';
};

class BankMapView {
  constructor(container) {
    this.container = container;
    this.map = null;
    this.markers = [];
    this.panel = null;
    this.activeMarkerEl = null;
  }

  removeDuplicateOffices(offices) {
    const seen = new Set();

    return offices.filter((office) => {
      const key = `${Number(office.longitude || 0).toFixed(6)}_${Number(office.latitude || 0).toFixed(6)}`;

      if (seen.has(key)) {
        return false;
      }

      seen.add(key);

      return true;
    });
  }

  async render(offices, bankData) {
    if (!this.container || !window.ymaps3 || !offices.length) return;

    await window.ymaps3.ready;
    const {
      YMap,
      YMapDefaultSchemeLayer,
      YMapDefaultFeaturesLayer,
      YMapMarker,
      YMapControls,
    } = window.ymaps3;

    this.container.innerHTML = '';
    this.markers = [];
    this.activeMarkerEl = null;

    const uniqueOffices = this.removeDuplicateOffices(offices);
    const center = uniqueOffices.reduce(
      (acc, office) => [acc[0] + Number(office.longitude), acc[1] + Number(office.latitude)],
      [0, 0],
    ).map((value) => value / uniqueOffices.length);

    this.map = new YMap(this.container, {
      location: { center, zoom: uniqueOffices.length > 1 ? 12 : 15 },
    });
    this.map.addChild(new YMapDefaultSchemeLayer());
    this.map.addChild(new YMapDefaultFeaturesLayer());

    try {
      const { YMapZoomControl } = await window.ymaps3.import('@yandex/ymaps3-controls@0.0.1');
      const controls = new YMapControls({ position: 'right' });
      controls.addChild(new YMapZoomControl({}));
      this.map.addChild(controls);
    } catch (_) {
      // optional controls package
    }

    this.panel = this.createInfoPanel();
    this.container.appendChild(this.panel);

    uniqueOffices.forEach((office) => {
      const markerEl = this.createMarker(office, bankData);
      const marker = new YMapMarker(
        { coordinates: [Number(office.longitude), Number(office.latitude)], anchor: [0.5, 1] },
        markerEl,
      );

      this.map.addChild(marker);
      this.markers.push(marker);
    });
  }

  createMarker(office, bankData) {
    const marker = document.createElement('div');
    const [firstRate, secondRate] = getRatesHtml(office);
    marker.className = 'bank-map-marker';
    marker.innerHTML = `
      <div class="bank-map-marker__bubble">
        ${bankData.bankLogo ? `<img class="bank-map-marker__logo" src="${escapeHtml(bankData.bankLogo)}" alt="">` : ''}
        <div class="bank-map-marker__rates">
          <span class="bank-map-marker__rate">${escapeHtml(firstRate[1])}</span>
          <span class="bank-map-marker__sep">|</span>
          <span class="bank-map-marker__total">${escapeHtml(secondRate[1])}</span>
        </div>
      </div>
    `;

    marker.addEventListener('click', () => this.showInfoPanel(office, bankData, marker));

    return marker;
  }

  createInfoPanel() {
    const panel = document.createElement('div');
    panel.className = 'bank-map-info-panel';
    panel.innerHTML = `
      <button class="bank-map-info-panel__close" type="button" aria-label="Закрыть">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.947957 9.99482C0.702783 10.0092 0.461665 9.92679 0.275446 9.76484C-0.0918152 9.3911 -0.0918152 8.78748 0.275446 8.41374L8.31726 0.278346C8.69924 -0.0832476 9.29864 -0.0631469 9.65607 0.323281C9.97929 0.672725 9.99813 1.20983 9.70018 1.58155L1.61099 9.76484C1.42717 9.92445 1.18992 10.0067 0.947957 9.99482Z" fill="currentColor" />
          <path d="M8.98051 9.99483C8.73203 9.99375 8.49388 9.89398 8.31746 9.71695L0.275613 1.58153C-0.064635 1.17957 -0.0183765 0.574655 0.378954 0.230418C0.733582 -0.0768058 1.25659 -0.0768058 1.61118 0.230418L9.70038 8.36581C10.0823 8.7275 10.102 9.3339 9.74448 9.72023C9.73026 9.7356 9.71557 9.75046 9.70038 9.76485C9.5023 9.9391 9.24163 10.0224 8.98051 9.99483Z" fill="currentColor" />
        </svg>
      </button>
      <div class="bank-map-info-panel__content"></div>
    `;

    panel.querySelector('.bank-map-info-panel__close')?.addEventListener('click', () => {
      panel.classList.remove('visible');
      this.container.classList.remove('map-locked');

      if (this.activeMarkerEl) {
        this.activeMarkerEl.classList.remove('active', 'compact');
      }

      this.activeMarkerEl = null;
    });

    return panel;
  }

  async getAddressFromCoordinates(longitude, latitude) {
    const apiKey = window.themeSettings?.yandexMapsKey || '';
    const url = `https://geocode-maps.yandex.ru/1.x/?apikey=${apiKey}&geocode=${longitude},${latitude}&format=json&lang=ru_RU&results=1`;

    try {
      const response = await fetch(url);
      const data = await response.json();
      const fullAddress = data.response?.GeoObjectCollection?.featureMember?.[0]?.GeoObject
        ?.metaDataProperty?.GeocoderMetaData?.text;

      if (!fullAddress) {
        return '';
      }

      const addressParts = fullAddress.split(',');

      return addressParts.length > 1
        ? addressParts.slice(1).join(',').trim()
        : fullAddress;
    } catch (error) {
      console.error('Geocoding error:', error);

      return '';
    }
  }

  async showInfoPanel(office, bankData, marker) {
    if (!this.panel) {
      return;
    }

    if (this.activeMarkerEl && this.activeMarkerEl !== marker) {
      this.activeMarkerEl.classList.remove('active', 'compact');
    }

    this.activeMarkerEl = marker;
    marker.classList.add('active', 'compact');

    if (this.map) {
      this.map.setLocation({
        center: [Number(office.longitude), Number(office.latitude)],
        zoom: 15,
        duration: 500,
      });
    }

    const address = office.address || await this.getAddressFromCoordinates(office.longitude, office.latitude);
    const rates = getRatesHtml(office);
    const isKkb = bankData.bankCode === 'kamkombank';
    const reserveButton = isKkb && bankData.bankUrl
      ? `<a href="${escapeHtml(bankData.bankUrl)}" target="_blank" rel="noopener noreferrer" class="bank-map-info-panel__reserve">Зарезервировать сумму</a>`
      : '';

    const content = this.panel.querySelector('.bank-map-info-panel__content');
    if (!content) {
      return;
    }

    content.innerHTML = `
      <div class="bank-map-info-panel__header">
        ${getBankLogoHtml(bankData)}
        <div class="bank-map-info-panel__bank-meta">
          <h4>${escapeHtml(bankData.bankName || 'Банк')}</h4>
          ${bankData.updateTime ? `<p class="bank-map-info-panel__updated">${escapeHtml(bankData.updateTime)}</p>` : ''}
        </div>
      </div>
      <p class="bank-map-info-panel__address">${escapeHtml(address || 'Адрес не указан')}</p>
      <div class="bank-map-info-panel__rates">
        ${rates.map(([label, value], index) => `
          <div class="bank-map-info-panel__rate">
            <span class="bank-map-info-panel__rate-label">${escapeHtml(label)}</span>
            <span class="${index === 1 ? 'bank-map-info-panel__total-value' : 'bank-map-info-panel__rate-value'}">${escapeHtml(value)}</span>
          </div>
        `).join('')}
      </div>
      ${office.phone ? `
        <div class="bank-map-info-panel__phone">
          <a href="tel:${escapeHtml(String(office.phone).replace(/[^\d+]/g, ''))}">${escapeHtml(office.phone)}</a>
        </div>
      ` : ''}
      ${reserveButton}
    `;

    this.container.classList.add('map-locked');
    this.panel.classList.add('visible');
  }
}

class BankMapModal {
  constructor() {
    this.modal = document.getElementById('modal-bank-map');
    this.mapView = null;
    this.bankData = null;
    this.bind();
  }

  bind() {
    this.modal?.querySelectorAll('[data-bank-modal-close]').forEach((item) => {
      item.addEventListener('click', () => this.close());
    });
  }

  async open(bankData) {
    if (!this.modal) return;

    this.bankData = bankData;
    this.fillHeader();
    this.modal.classList.add('active');
    this.modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    await this.load();
  }

  close() {
    this.modal?.classList.remove('active');
    this.modal?.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  isOpen() {
    return Boolean(this.modal?.classList.contains('active'));
  }

  fillHeader() {
    const logo = this.modal?.querySelector('[data-bank-modal-logo]');
    const name = this.modal?.querySelector('[data-bank-modal-name]');
    const update = this.modal?.querySelector('[data-bank-modal-update]');

    if (logo) {
      logo.innerHTML = this.bankData.bankLogo ? `<img src="${escapeHtml(this.bankData.bankLogo)}" alt="">` : '';
    }

    if (name) {
      name.textContent = this.bankData.bankName;
    }

    if (update) {
      update.textContent = this.bankData.updateTime;
    }
  }

  async load() {
    const container = this.modal?.querySelector('[data-bank-modal-map]');
    if (!container || !this.bankData) {
      return;
    }

    container.innerHTML = '<div class="bank-modal-loading">Загрузка...</div>';
    const result = await fetchOffices('get_bank_map_offices', this.bankData.bankCode);

    if (!result.success || !result.data.offices?.length) {
      container.innerHTML = '<div class="bank-modal-loading">Офисы не найдены</div>';
      return;
    }

    this.bankData.bankUrl = result.data.bank_url || this.bankData.bankUrl;
    this.mapView = new BankMapView(container);
    await this.mapView.render(result.data.offices, this.bankData);
  }
}

class BankOfficesModal {
  constructor() {
    this.modal = document.getElementById('modal-bank-offices');
    this.bankData = null;
    this.offices = [];
    this.bind();
  }

  bind() {
    this.modal?.querySelectorAll('[data-bank-offices-close]').forEach((item) => {
      item.addEventListener('click', () => this.close());
    });
  }

  async open(bankData) {
    if (!this.modal) return;

    this.bankData = bankData;
    this.fillHeader();
    this.modal.classList.add('active');
    this.modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    await this.load();
  }

  close() {
    this.modal?.classList.remove('active');
    this.modal?.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  isOpen() {
    return Boolean(this.modal?.classList.contains('active'));
  }

  fillHeader() {
    const name = this.modal?.querySelector('[data-bank-offices-name]');
    const update = this.modal?.querySelector('[data-bank-offices-update]');

    if (name) {
      name.textContent = this.bankData.bankName;
    }

    if (update) {
      update.textContent = this.bankData.updateTime;
    }
  }

  async load() {
    const list = this.modal?.querySelector('[data-bank-offices-list]');
    if (!list || !this.bankData) {
      return;
    }

    list.innerHTML = '<div class="modal-bank-offices__loading">Загрузка...</div>';
    const result = await fetchOffices('get_bank_offices', this.bankData.bankCode);

    if (!result.success || !result.data.offices?.length) {
      list.innerHTML = '<div class="modal-bank-offices__loading">Офисы не найдены</div>';
      return;
    }

    this.bankData.bankUrl = result.data.bank_url || this.bankData.bankUrl;
    this.offices = result.data.offices;
    this.render();
  }

  render() {
    const list = this.modal?.querySelector('[data-bank-offices-list]');
    if (!list) {
      return;
    }

    const showDisclaimer = getCurrentTab() === 'sell' || getCurrentTab() === 'buy';
    const logoHtml = getOfficeBankLogoHtml(this.bankData);

    list.innerHTML = this.offices.map((office) => {
      const rates = getRatesHtml(office);
      const mapUrl = office.latitude && office.longitude
        ? `https://yandex.ru/maps/?pt=${office.longitude},${office.latitude}&z=17&l=map`
        : '';
      const reserveHtml = this.bankData.bankUrl
        ? `<a href="${escapeHtml(this.bankData.bankUrl)}" target="_blank" rel="noopener noreferrer" class="modal-bank-offices__reserve-btn">Забронировать</a>`
        : '';

      return `
        <article class="modal-bank-offices__office">
          <div class="modal-bank-offices__office-main">
            <div class="modal-bank-offices__office-left">
              <div class="modal-bank-offices__office-bank-logo-wrap">
                ${logoHtml}
              </div>
              <div class="modal-bank-offices__office-address-content">
                <p class="modal-bank-offices__office-address">${escapeHtml(office.address || 'Адрес не указан')}</p>
                ${office.work_status ? `<div class="modal-bank-offices__office-work-status">${escapeHtml(office.work_status)}</div>` : ''}
              </div>
            </div>
            <div class="modal-bank-offices__office-right">
              <div class="modal-bank-offices__office-rates">
                ${rates.map(([label, value], index) => `
                  <div class="modal-bank-offices__office-rate">
                    <p class="modal-bank-offices__office-rate-label">${escapeHtml(label)}</p>
                    <p class="modal-bank-offices__office-rate-value ${index === 1 ? 'modal-bank-offices__office-rate-value--primary' : ''}">${escapeHtml(value)}</p>
                  </div>
                `).join('')}
              </div>
              <div class="modal-bank-offices__office-actions">
                ${mapUrl ? `<a class="modal-bank-offices__map-link" href="${escapeHtml(mapUrl)}" target="_blank" rel="noopener noreferrer" aria-label="Показать на карте">
                  <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="33" height="33" rx="16.5" fill="#F2F3F7" />
                    <path d="M17.1811 11.8533C15.3461 11.8533 13.8533 13.3461 13.8533 15.1811C13.8533 17.016 15.3461 18.5089 17.1811 18.5089C19.016 18.5089 20.5089 17.016 20.5089 15.1811C20.5089 13.3461 19.016 11.8533 17.1811 11.8533ZM17.1811 17.1077C16.1187 17.1077 15.2544 16.2434 15.2544 15.1811C15.2544 14.1187 16.1187 13.2544 17.1811 13.2544C18.2434 13.2544 19.1077 14.1187 19.1077 15.1811C19.1077 16.2434 18.2434 17.1077 17.1811 17.1077Z" fill="#22284B" />
                    <path d="M17.181 8C13.2214 8 10 11.2214 10 15.181V15.3796C10 17.3821 11.1481 19.7157 13.4126 22.3155C15.0541 24.2001 16.6727 25.5142 16.7408 25.5693L17.181 25.925L17.6213 25.5693C17.6894 25.5143 19.308 24.2001 20.9495 22.3155C23.2139 19.7157 24.3621 17.3822 24.3621 15.3796V15.1811C24.3621 11.2214 21.1407 8 17.181 8ZM22.9609 15.3796C22.9609 18.7638 18.6004 22.8605 17.181 24.1041C15.7613 22.8601 11.4012 18.7636 11.4012 15.3796V15.1811C11.4012 11.9941 13.994 9.4012 17.181 9.4012C20.3681 9.4012 22.9609 11.9941 22.9609 15.1811V15.3796Z" fill="#22284B" />
                  </svg>
                </a>` : ''}
                ${reserveHtml}
              </div>
            </div>
          </div>
          ${showDisclaimer ? '<p class="modal-bank-offices__office-rate-disclaimer">Расчет суммы носит ориентировочный характер основываясь на курсах Банка и не учитывает дополнительные комиссии.</p>' : ''}
        </article>
      `;
    }).join('');
  }
}

const initBankModals = () => {
  const mapModal = new BankMapModal();
  const officesModal = new BankOfficesModal();

  document.addEventListener('click', (event) => {
    const mapButton = event.target.closest('[data-bank-map-trigger]');
    if (mapButton) {
      event.preventDefault();
      const card = mapButton.closest('[data-bank-card]');
      const bankData = getBankData(card);
      card?.classList.add('map-loading');
      mapModal.open(bankData).finally(() => {
        card?.classList.remove('map-loading');
      });

      return;
    }

    const officesButton = event.target.closest('[data-bank-offices-trigger]');
    if (officesButton) {
      event.preventDefault();
      officesModal.open(getBankData(officesButton.closest('[data-bank-card]')));
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      mapModal.close();
      officesModal.close();
    }
  });

  document.querySelectorAll('[data-converter-tab]').forEach((control) => {
    control.addEventListener('click', () => {
      if (mapModal.isOpen()) {
        mapModal.load();
      }

      if (officesModal.isOpen()) {
        officesModal.load();
      }
    });
  });

  document.querySelectorAll('.hero [data-currency-select]').forEach((control) => {
    control.addEventListener('currency-select:change', () => {
      if (mapModal.isOpen()) {
        mapModal.load();
      }

      if (officesModal.isOpen()) {
        officesModal.load();
      }
    });
  });

  document.querySelector('.hero input[name="converter-amount"]')?.addEventListener('input', () => {
    if (mapModal.isOpen()) {
      window.clearTimeout(mapModal.amountTimer);
      mapModal.amountTimer = window.setTimeout(() => mapModal.load(), 250);
    }

    if (officesModal.isOpen() && officesModal.offices.length && getCurrentTab() !== 'all') {
      window.clearTimeout(officesModal.amountTimer);
      officesModal.amountTimer = window.setTimeout(() => officesModal.render(), 250);
    }
  });
};

export default initBankModals;
