import {
  getActiveCurrencyCode,
  getBanksTitleSelect,
  getConverterFromSelect,
  setCurrencySelectValue,
} from '../utils/currency-select';
import { syncBankDetailLinks } from '../utils/bank-page-url';

const NO_CLUSTER_BANKS = ['kamkombank'];
let yandexPackagesRegistered = false;

const escapeHtml = (value) => String(value ?? '')
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#039;');

const parseAmount = (value) => {
  const normalized = String(value || '').replace(/\s/g, '').replace(',', '.');
  const amount = Number.parseFloat(normalized);

  return Number.isFinite(amount) ? amount : 0;
};

const formatRate = (value) => Number(value || 0).toFixed(2).replace('.', ',');

const formatTotal = (value) => {
  const [integer, decimal = ''] = Number(value || 0).toFixed(2).split('.');
  const formattedInteger = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '\u00a0');
  const cleanDecimal = decimal.replace(/0+$/, '');

  return cleanDecimal ? `${formattedInteger},${cleanDecimal}` : formattedInteger;
};

const getMapUpdateTime = (mapContainer) => mapContainer?.dataset.updatedAt || '';

const getCurrentTab = () => {
  const activeTab = document.querySelector('[data-converter-tab].active');

  return activeTab?.dataset.converterTab || 'all';
};

const getCurrentCurrency = () => getActiveCurrencyCode();

const getCurrentAmount = () => {
  const input = document.querySelector('.hero input[name="converter-amount"]');

  return parseAmount(input?.value);
};

const getActiveFilters = (root) => {
  return Array.from(root.querySelectorAll('[data-banks-filter]:checked'))
    .map((input) => input.dataset.filterSlug)
    .filter(Boolean);
};

const updateVisibleFilters = (root) => {
  const tab = getCurrentTab();
  const currency = getCurrentCurrency().toLowerCase();
  let visibleCount = 0;

  root.querySelectorAll('[data-banks-filter]').forEach((input) => {
    const item = input.closest('.banks-list-layout__filter');
    const showInBuy = input.dataset.showInBuy === '1';
    const showInSell = input.dataset.showInSell === '1';
    const currencyCodes = input.dataset.currencyCodes || '';
    let visible = tab === 'all' || (!showInBuy && !showInSell);

    if (tab === 'buy' && showInBuy) visible = true;
    if (tab === 'sell' && showInSell) visible = true;

    if (currencyCodes) {
      const allowed = currencyCodes.split(',').map((code) => code.trim().toLowerCase());
      visible = visible && allowed.includes(currency);
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

  root.hidden = visibleCount === 0;
};

const requestBanks = async (root, action, page = 1) => {
  const formData = new FormData();
  const filterContainer = root.querySelector('[data-banks-filter-container]');

  formData.append('action', action);
  formData.append('currency', getCurrentCurrency());
  formData.append('tab', getCurrentTab());
  formData.append('amount', String(getCurrentAmount()));
  formData.append('filters', JSON.stringify(getActiveFilters(root)));
  formData.append('sort', root.querySelector('[data-banks-sort]')?.value || '');
  formData.append('city_slug', root.dataset.citySlug || '');
  formData.append('city_name', root.dataset.cityName || '');
  formData.append('page', String(page));

  if (filterContainer) {
    updateVisibleFilters(filterContainer);
  }

  const response = await fetch(window.themeSettings?.ajaxUrl || '/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: formData,
  });

  return response.json();
};

const setLoading = (root, isLoading) => {
  root.classList.toggle('is-loading', isLoading);
};

const refreshBanks = async (root) => {
  const results = root.querySelector('[data-banks-results]');
  const empty = root.querySelector('[data-banks-empty]');
  const loadMore = root.querySelector('[data-banks-load-more]');
  const map = root.querySelector('[data-banks-map]');

  if (!results) return;

  setLoading(root, true);

  try {
    const json = await requestBanks(root, 'filter_banks_by_currency');

    if (!json.success) return;

    results.innerHTML = json.data.html || '';
    syncBankDetailLinks();
    root.dataset.currentPage = '1';
    root.dataset.totalPages = String(json.data.total_pages || 1);

    if (empty) {
      empty.hidden = Boolean(json.data.html);
    }

    if (loadMore) {
      loadMore.hidden = Number(json.data.total_pages || 1) <= 1;
    }

    if (map) {
      map.dataset.offices = JSON.stringify(json.data.map_offices || []);
      map.dataset.updatedAt = json.data.updated_at || '';
      if (!root.querySelector('[data-banks-panel="map"]')?.hidden) {
        initMap(root, true);
      }
    }
  } finally {
    setLoading(root, false);
  }
};

const loadMoreBanks = async (root) => {
  const results = root.querySelector('[data-banks-results]');
  const loadMore = root.querySelector('[data-banks-load-more]');
  const currentPage = Number(root.dataset.currentPage || 1) + 1;

  if (!results || !loadMore) return;

  setLoading(root, true);

  try {
    const json = await requestBanks(root, 'load_more_banks', currentPage);
    if (!json.success) {
      loadMore.hidden = true;
      return;
    }

    results.insertAdjacentHTML('beforeend', json.data.html || '');
    root.dataset.currentPage = String(currentPage);
    loadMore.hidden = !json.data.has_more;
  } finally {
    setLoading(root, false);
  }
};

const initViewTabs = (root) => {
  const tabs = root.querySelectorAll('[data-banks-view-tab]');
  const panels = root.querySelectorAll('[data-banks-panel]');

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.banksViewTab;

      tabs.forEach((item) => {
        item.classList.toggle('banks-list-layout__tab--active', item === tab);
        item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
      });

      panels.forEach((panel) => {
        panel.hidden = panel.dataset.banksPanel !== target;
      });

      if (target === 'map') {
        initMap(root, Boolean(root.banksMapInitialized));
      }
    });
  });
};

const parseOffices = (mapEl) => {
  try {
    return JSON.parse(mapEl?.dataset.offices || '[]');
  } catch (_) {
    return [];
  }
};

const removeDuplicateOffices = (offices) => {
  const seen = new Set();

  return offices.filter((office) => {
    const longitude = Number(office.longitude || 0).toFixed(6);
    const latitude = Number(office.latitude || 0).toFixed(6);
    const key = `${office.bank_code || ''}_${longitude}_${latitude}`;

    if (seen.has(key)) {
      return false;
    }

    seen.add(key);

    return true;
  });
};

const getBounds = (coordinates) => {
  let minLat = Infinity;
  let minLng = Infinity;
  let maxLat = -Infinity;
  let maxLng = -Infinity;

  coordinates.forEach(([lng, lat]) => {
    if (lat < minLat) minLat = lat;
    if (lat > maxLat) maxLat = lat;
    if (lng < minLng) minLng = lng;
    if (lng > maxLng) maxLng = lng;
  });

  const latPadding = (maxLat - minLat) * 0.3 || 0.01;
  const lngPadding = (maxLng - minLng) * 0.3 || 0.01;

  return [
    [minLng - lngPadding, minLat - latPadding],
    [maxLng + lngPadding, maxLat + latPadding],
  ];
};

const createSearchMarker = (logoUrl) => {
  const element = document.createElement('div');
  element.className = 'sr-map-marker';

  if (logoUrl) {
    const image = document.createElement('img');
    image.src = logoUrl;
    image.alt = '';
    image.className = 'sr-map-marker__logo';
    image.loading = 'lazy';
    image.decoding = 'async';
    element.appendChild(image);
  }

  return element;
};

const createCluster = (count) => {
  const element = document.createElement('div');
  element.className = 'sr-map-cluster';
  element.innerHTML = `
    <div class="sr-map-cluster__content">
      <span class="sr-map-cluster__text">${count}</span>
    </div>
  `;

  return element;
};

const createInfoPanel = (mapContainer, state) => {
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
    mapContainer.classList.remove('map-locked');

    if (state.activeMarkerEl) {
      state.activeMarkerEl.classList.remove('active');
    }

    state.activeMarkerEl = null;
    state.activeOffice = null;
  });

  return panel;
};

const getAddressFromCoordinates = async (longitude, latitude) => {
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
};

const updateInfoPanel = (panel, office, amount, tab) => {
  const content = panel.querySelector('.bank-map-info-panel__content');

  if (!content) {
    return;
  }

  let firstLabel = 'Покупка';
  let secondLabel = 'Продажа';
  let firstValue = formatRate(office.buy);
  let secondValue = formatRate(office.sell);

  if (tab === 'sell') {
    firstLabel = 'Курс банка';
    secondLabel = 'Вы получите';
    firstValue = formatRate(office.buy);
    secondValue = formatTotal(office.buy * amount);
  } else if (tab === 'buy') {
    firstLabel = 'Курс банка';
    secondLabel = 'Вы заплатите';
    firstValue = formatRate(office.sell);
    secondValue = formatTotal(office.sell * amount);
  }

  const isKkb = office.bank_code === 'kamkombank';
  const reserveButton = isKkb
    ? office.bank_url
      ? `<a href="${escapeHtml(office.bank_url)}" target="_blank" rel="noopener noreferrer" class="bank-map-info-panel__reserve">Зарезервировать сумму</a>`
      : `<span class="bank-map-info-panel__reserve">Зарезервировать сумму</span>`
    : '';

  const routeUrl = office.latitude && office.longitude
    ? `https://yandex.ru/maps/?rtext=~${office.latitude},${office.longitude}`
    : '';
  const logoHtml = office.bank_logo
    ? `<img class="bank-map-info-panel__bank-logo" src="${escapeHtml(office.bank_logo)}" alt="${escapeHtml(office.bank_name || '')}">`
    : '<span class="bank-map-info-panel__bank-logo bank-map-info-panel__bank-logo--placeholder" aria-hidden="true"></span>';
  const updatedAt = office.updated_at || '';

  content.innerHTML = `
    <div class="bank-map-info-panel__header">
      ${logoHtml}
      <div class="bank-map-info-panel__bank-meta">
        <h4>${escapeHtml(office.bank_name || 'Банк')}</h4>
        ${updatedAt ? `<p class="bank-map-info-panel__updated">${escapeHtml(updatedAt)}</p>` : ''}
      </div>
    </div>
    <p class="bank-map-info-panel__address">${escapeHtml(office.address || 'Адрес не указан')}</p>
    <div class="bank-map-info-panel__rates">
      <div class="bank-map-info-panel__rate-row">
        <span class="bank-map-info-panel__rate-label">${escapeHtml(firstLabel)}</span>
        <span class="bank-map-info-panel__rate-value">${escapeHtml(firstValue)} ₽</span>
      </div>
      <div class="bank-map-info-panel__rate-row">
        <span class="bank-map-info-panel__rate-label">${escapeHtml(secondLabel)}</span>
        <span class="bank-map-info-panel__total-value">${escapeHtml(secondValue)} ₽</span>
      </div>
    </div>
    ${office.phone ? `
      <div class="bank-map-info-panel__phone">
        <a href="tel:${escapeHtml(String(office.phone).replace(/[^\d+]/g, ''))}">${escapeHtml(office.phone)}</a>
      </div>
    ` : ''}
    ${reserveButton}
    ${routeUrl ? `<a href="${escapeHtml(routeUrl)}" target="_blank" rel="noopener noreferrer" class="bank-map-info-panel__route">Построить маршрут</a>` : ''}
  `;
};

const handleMarkerClick = async (map, markerEl, office, state, panel, mapContainer) => {
  if (state.activeMarkerEl && state.activeMarkerEl !== markerEl) {
    state.activeMarkerEl.classList.remove('active');
  }

  state.activeMarkerEl = markerEl;
  state.activeOffice = office;
  markerEl.classList.add('active');

  map.setLocation({
    center: [Number(office.longitude), Number(office.latitude)],
    zoom: 16,
    duration: 500,
  });

  let address = office.address || '';

  if (!address) {
    address = await getAddressFromCoordinates(office.longitude, office.latitude);
  }

  updateInfoPanel(
    panel,
    {
      ...office,
      address,
      updated_at: getMapUpdateTime(mapContainer),
    },
    getCurrentAmount(),
    getCurrentTab(),
  );

  mapContainer.classList.add('map-locked');
  panel.classList.add('visible');
};

const ensureYandexPackages = async () => {
  if (!window.ymaps3 || yandexPackagesRegistered) {
    return;
  }

  await window.ymaps3.ready;

  if (window.ymaps3.import?.registerCdn) {
    window.ymaps3.import.registerCdn('https://cdn.jsdelivr.net/npm/{package}', [
      '@yandex/ymaps3-clusterer@0.0',
    ]);
  }

  yandexPackagesRegistered = true;
};

const initMap = async (root, force = false) => {
  const mapEl = root.querySelector('[data-banks-map]');
  if (!mapEl || !window.ymaps3) return;

  if (root.banksMapInitialized && !force) {
    return;
  }

  const offices = removeDuplicateOffices(
    parseOffices(mapEl).filter((office) => office.latitude && office.longitude),
  );

  mapEl.innerHTML = '';
  mapEl.classList.remove('map-locked');
  root.banksMapInitialized = false;
  root.banksMap = null;

  if (!offices.length) {
    mapEl.innerHTML = '<p class="banks-list-layout__map-placeholder">Офисы для карты не найдены</p>';
    return;
  }

  await ensureYandexPackages();
  await window.ymaps3.ready;

  const {
    YMap,
    YMapDefaultSchemeLayer,
    YMapDefaultFeaturesLayer,
    YMapMarker,
    YMapControls,
  } = window.ymaps3;

  const center = offices.reduce(
    (acc, office) => [acc[0] + Number(office.longitude), acc[1] + Number(office.latitude)],
    [0, 0],
  ).map((value) => value / offices.length);

  const map = new YMap(mapEl, {
    location: { center, zoom: offices.length > 1 ? 11 : 15 },
  });

  map.addChild(new YMapDefaultSchemeLayer());
  map.addChild(new YMapDefaultFeaturesLayer());

  try {
    const { YMapZoomControl } = await window.ymaps3.import('@yandex/ymaps3-controls@0.0.1');
    const controls = new YMapControls({ position: 'right' });
    controls.addChild(new YMapZoomControl({}));
    map.addChild(controls);
  } catch (_) {
    // optional controls package
  }

  const state = {
    activeMarkerEl: null,
    activeOffice: null,
  };
  const panel = createInfoPanel(mapEl, state);
  mapEl.appendChild(panel);

  const markerFactory = (office) => {
    const markerEl = createSearchMarker(office.bank_logo);
    markerEl.addEventListener('click', () => {
      handleMarkerClick(map, markerEl, office, state, panel, mapEl);
    });

    return markerEl;
  };

  const clusterFeatures = [];
  const vipFeatures = [];

  offices.forEach((office, index) => {
    const feature = {
      type: 'Feature',
      id: String(index),
      geometry: {
        type: 'Point',
        coordinates: [Number(office.longitude), Number(office.latitude)],
      },
      properties: { office },
    };

    if (NO_CLUSTER_BANKS.includes(office.bank_code)) {
      vipFeatures.push(feature);
    } else {
      clusterFeatures.push(feature);
    }
  });

  try {
    const { YMapClusterer, clusterByGrid } = await window.ymaps3.import('@yandex/ymaps3-clusterer');

    const marker = (feature) => {
      return new YMapMarker(
        { coordinates: feature.geometry.coordinates },
        markerFactory(feature.properties.office),
      );
    };

    const cluster = (coordinates, features) => {
      return new YMapMarker(
        {
          coordinates,
          onClick() {
            if (features.length === 1) {
              map.setLocation({
                center: coordinates,
                zoom: 16,
                duration: 300,
              });

              return;
            }

            map.setLocation({
              bounds: getBounds(features.map((item) => item.geometry.coordinates)),
              duration: 300,
            });
          },
        },
        createCluster(features.length),
      );
    };

    map.addChild(new YMapClusterer({
      method: clusterByGrid({ gridSize: 128 }),
      features: clusterFeatures,
      marker,
      cluster,
      maxZoom: 16,
    }));
  } catch (_) {
    clusterFeatures.forEach((feature) => {
      map.addChild(new YMapMarker(
        { coordinates: feature.geometry.coordinates },
        markerFactory(feature.properties.office),
      ));
    });
  }

  vipFeatures.forEach((feature) => {
    const markerEl = markerFactory(feature.properties.office);
    markerEl.classList.add('sr-map-marker--vip');

    map.addChild(new YMapMarker(
      { coordinates: feature.geometry.coordinates },
      markerEl,
    ));
  });

  root.banksMap = map;
  root.banksMapInitialized = true;
};

const initHeroControls = (root) => {
  const converterSelect = getConverterFromSelect();
  const titleSelect = getBanksTitleSelect();

  if (converterSelect && titleSelect) {
    setCurrencySelectValue(titleSelect, getCurrentCurrency());
  }

  document.querySelectorAll('[data-converter-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('[data-converter-tab]').forEach((item) => {
        item.classList.toggle('active', item === tab);
      });
      syncBankDetailLinks();
      refreshBanks(root);
    });
  });

  converterSelect?.addEventListener('currency-select:change', (event) => {
    const code = event.detail?.code || converterSelect.dataset.selectedCurrency || '';

    if (titleSelect && code) {
      setCurrencySelectValue(titleSelect, code);
    }

    syncBankDetailLinks();
    refreshBanks(root);
  });

  titleSelect?.addEventListener('currency-select:change', (event) => {
    const code = event.detail?.code || titleSelect.dataset.selectedCurrency || '';

    if (converterSelect && code) {
      setCurrencySelectValue(converterSelect, code);
    }

    syncBankDetailLinks();
    refreshBanks(root);
  });

  document.querySelector('.hero input[name="converter-amount"]')?.addEventListener('input', () => {
    syncBankDetailLinks();
    window.clearTimeout(root.banksAmountTimer);
    root.banksAmountTimer = window.setTimeout(() => refreshBanks(root), 350);
  });
};

const initBanksList = () => {
  document.querySelectorAll('[data-banks-list]').forEach((root) => {
    if (root.dataset.bankDefaultTab) {
      document.body.dataset.bankDefaultTab = root.dataset.bankDefaultTab;
    }

    syncBankDetailLinks();

    const filterContainer = root.querySelector('[data-banks-filter-container]');

    if (filterContainer) {
      updateVisibleFilters(filterContainer);
      filterContainer.addEventListener('change', () => refreshBanks(root));
    }

    root.querySelector('[data-banks-load-more]')?.addEventListener('click', () => loadMoreBanks(root));
    root.querySelector('[data-banks-sort]')?.addEventListener('change', () => refreshBanks(root));
    root.addEventListener('banks-list:refresh', () => refreshBanks(root));

    initViewTabs(root);
    initHeroControls(root);
  });
};

export default initBanksList;
