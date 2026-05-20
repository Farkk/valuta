<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Banks\BankManager;
use Theme\Helpers\Template;
use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyManager;
use Theme\Currencies\CurrencyRouter;
use Theme\Filters\FilterRegistry;
use Theme\Helpers\Homepage;
use Theme\Rates\RatesManager;
use Theme\SEO\SeoPageManager;

$sortFieldId = 'banks-list-sort-' . uniqid('', false);
$sidebarChartMaskId = 'banks-list-chart-mask-' . uniqid('', false);
$currentCity = CityManager::getCurrentCity();
$currentCurrency = CurrencyRouter::getCurrentCurrency();
$currentCurrencyData = CurrencyManager::getCurrencyByCode($currentCurrency) ?? CurrencyManager::formatCurrency('USD');
$titleCurrencyOptions = [];
$titleCurrencySelectedIndex = 0;

foreach (CurrencyManager::getCurrencies() as $currency) {
  $code = (string) ($currency['code'] ?? '');

  if ($code === '' || $code === 'rub') {
    continue;
  }

  if ($code === $currentCurrency) {
    $titleCurrencySelectedIndex = count($titleCurrencyOptions);
  }

  $titleCurrencyOptions[] = [
    'label' => strtoupper($code),
    'code'  => $code,
    'url'   => Homepage::getCurrencyUrl($code, $currentCity),
  ];
}
$defaultTab = FilterRegistry::getDefaultTab();
$activeTab = BankManager::resolveTab();
$amount = 0.0;
$pairData = CurrencyRouter::getCurrencyPairData();

if ($pairData !== null) {
  $amount = (float) $pairData['amount'];
}

if (SeoPageManager::hasCurrentPage() && SeoPageManager::getCurrentAmount() > 0) {
  $amount = (float) SeoPageManager::getCurrentAmount();
}
$filters = FilterRegistry::getFilters();
$activeFilterSlugs = array_values(array_filter(array_map(
  static fn(array $filter): string => ! empty($filter['checked_by_default']) ? (string) $filter['slug'] : '',
  $filters
)));
$rates = RatesManager::getRates((string) $currentCity['city_name'], $currentCurrency);
$rawBanks = RatesManager::getRawBanks($rates);
$banks = RatesManager::sortAndGroupBanks($rawBanks, $defaultTab, $activeFilterSlugs);
$mapOffices = RatesManager::getMapOffices($rawBanks, $defaultTab, $activeFilterSlugs);
$timeUpdate = RatesManager::formatUpdateTime($rates);
$totalBanks = count($banks);
$firstPageCount = 8;
$perPage = 5;
$totalPages = $totalBanks <= $firstPageCount ? 1 : 1 + (int) ceil(($totalBanks - $firstPageCount) / $perPage);
$banksToShow = array_slice($banks, 0, $firstPageCount);
$seoH2 = SeoPageManager::hasCurrentPage()
  ? SeoPageManager::getCurrentValue('h2', __('Подобрали лучшие предложения', 'theme'))
  : __('Подобрали лучшие предложения', 'theme');

$sidebarConverterFromOptions = array_values(array_filter(
  CurrencyManager::getCurrencies(),
  static fn(array $currency): bool => ($currency['code'] ?? '') !== 'rub'
));
$sidebarDefaultCurrency = Homepage::getDefaultSidebarCurrency();
$sidebarSelectedIndex = 0;
foreach ($sidebarConverterFromOptions as $index => $currency) {
  if (($currency['code'] ?? '') === ($sidebarDefaultCurrency['code'] ?? 'usd')) {
    $sidebarSelectedIndex = $index;
    break;
  }
}
$sidebarConverterToOptions = [
  CurrencyManager::formatCurrency('RUB'),
];
$sidebarRatesResponse = RatesManager::getRates((string) $currentCity['city_name'], 'usd');
$sidebarCbrRates = [];
foreach (($sidebarRatesResponse['cbr_rates']['today'] ?? []) as $code => $rate) {
  if (is_numeric($rate)) {
    $sidebarCbrRates[strtolower((string) $code)] = (float) $rate;
  }
}
$sidebarCities = CityManager::getCities();
$sidebarChartUrl = Homepage::getChartUrl();

?>
<section
  class="banks-list-layout"
  id="search-result"
  aria-labelledby="banks-list-heading"
  data-banks-list
  data-bank-default-tab="<?php echo esc_attr($defaultTab); ?>"
  data-current-page="1"
  data-total-pages="<?= esc_attr((string) $totalPages) ?>"
  data-default-tab="<?= esc_attr($defaultTab) ?>"
  data-city-slug="<?= esc_attr((string) $currentCity['slug']) ?>"
  data-city-name="<?= esc_attr((string) $currentCity['city_name']) ?>">
  <div class="container">

    <header class="banks-list-layout__intro">
      <h1 id="banks-list-heading" class="banks-list-layout__title">
        <span class="banks-list-layout__title-text">
          <?= esc_html($seoH2) ?>
        </span>
        <span class="banks-list-layout__title-currency">
          <?php
          Template::part('template-parts/components/currency-select', null, [
            'id'             => 'banks-list-title-currency',
            'layout'         => 'banks-title',
            'options'        => $titleCurrencyOptions,
            'selected_index' => $titleCurrencySelectedIndex,
            'navigate'       => ! is_front_page(),
          ]);
          ?>
        </span>
      </h1>
    </header>

    <div class="banks-list-layout__tabs-bar">
      <div class="banks-list-layout__tabs" role="tablist" aria-label="<?php esc_attr_e('Режим отображения', 'theme'); ?>">
        <button
          type="button"
          class="banks-list-layout__tab banks-list-layout__tab--active"
          data-banks-view-tab="list"
          role="tab"
          aria-selected="true">
          <?php esc_html_e('Список', 'theme'); ?>
        </button>
        <button
          type="button"
          class="banks-list-layout__tab"
          data-banks-view-tab="map"
          role="tab"
          aria-selected="false">
          <?php esc_html_e('Карта', 'theme'); ?>
        </button>
      </div>
    </div>

    <div class="banks-list-layout__grid">
      <div class="banks-list-layout__main">
        <div class="banks-list-layout__filters-bar" role="toolbar" aria-label="<?php esc_attr_e('Фильтры и сортировка', 'theme'); ?>">
          <div class="banks-list-layout__filters">
            <div class="banks-list-layout__filters-checkboxes" data-banks-filter-container>
              <?php foreach ($filters as $filter) : ?>
                <label class="banks-list-layout__filter">
                  <input
                    class="banks-list-layout__checkbox-input"
                    type="checkbox"
                    data-banks-filter
                    data-filter-slug="<?= esc_attr((string) $filter['slug']) ?>"
                    data-show-in-buy="<?= ! empty($filter['show_in_buy']) ? '1' : '0' ?>"
                    data-show-in-sell="<?= ! empty($filter['show_in_sell']) ? '1' : '0' ?>"
                    data-currency-codes="<?= esc_attr(implode(',', $filter['currency_codes'] ?? [])) ?>"
                    <?= ! empty($filter['checked_by_default']) ? 'checked' : '' ?> />
                  <span class="banks-list-layout__checkbox-icon" aria-hidden="true">
                    <svg class="banks-list-layout__checkbox-svg banks-list-layout__checkbox-svg--checked" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M11 0C17.0741 0 22 4.92594 22 11C22 17.0741 17.0741 22 11 22C4.92594 22 0 17.0741 0 11C0 4.92594 4.92594 0 11 0ZM8.71571 14.5729L6.02264 11.8776C5.56384 11.4185 5.56374 10.6699 6.02264 10.2109C6.48164 9.752 7.23359 9.75487 7.68925 10.2109L9.58785 12.111L14.3109 7.38791C14.7699 6.92891 15.5186 6.92891 15.9775 7.38791C16.4365 7.84681 16.4359 8.59617 15.9775 9.05452L10.4198 14.6122C9.96147 15.0706 9.2121 15.0712 8.7532 14.6122C8.7403 14.5993 8.72786 14.5862 8.71571 14.5729Z" fill="#E30611" />
                    </svg>
                    <svg class="banks-list-layout__checkbox-svg banks-list-layout__checkbox-svg--unchecked" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <circle cx="11" cy="11" r="10.5" fill="#fff" stroke="#CCD2DF" />
                    </svg>
                  </span>
                  <span class="banks-list-layout__filter-label">
                    <?= esc_html((string) $filter['name']) ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="banks-list-layout__filters-sort">
            <label class="screen-reader-text" for="<?php echo esc_attr($sortFieldId); ?>">
              <?php esc_html_e('Сортировка', 'theme'); ?>
            </label>
            <select id="<?php echo esc_attr($sortFieldId); ?>" class="banks-list-layout__sort" data-banks-sort aria-label="<?php esc_attr_e('Сортировка списка', 'theme'); ?>">
              <option value=""><?php esc_html_e('Сортировка', 'theme'); ?></option>
              <option value="default"><?php esc_html_e('По выгодности курса', 'theme'); ?></option>
              <option value="buy_desc"><?php esc_html_e('Покупка: сначала выше', 'theme'); ?></option>
              <option value="sell_asc"><?php esc_html_e('Продажа: сначала ниже', 'theme'); ?></option>
            </select>
          </div>
        </div>

        <div class="banks-list-layout__results" data-banks-panel="list">
          <ul class="banks-list-layout__list" data-banks-results>
            <?php foreach ($banksToShow as $index => $bank) : ?>
              <?php
              $badges = $index === 0 ? [__('Лучший курс', 'theme')] : [];
              if (($bank['bank_code'] ?? '') === 'kamkombank') {
                foreach (FilterRegistry::getFiltersForTab($defaultTab, $currentCurrency) as $filter) {
                  $slug = (string) ($filter['slug'] ?? '');
                  if ($slug !== '' && ! empty($bank[$slug])) {
                    $badges[] = (string) $filter['name'];
                  }
                }
              }
              ?>
              <li class="banks-list-layout__list-item">
                <?php Template::part('template-parts/components/bank-rate-card', null, array_merge(
                  RatesManager::formatCardParams($bank, $activeTab, $amount),
                  [
                    'bank_code' => $bank['bank_code'] ?? '',
                    'bank_name' => $bank['bank_name'] ?? '',
                    'logo_url' => $bank['bank_logo'] ?? '',
                    'updated_at' => $timeUpdate,
                    'badges' => array_values(array_unique(array_filter($badges))),
                    'cta_variant' => 'details',
                    'cta_url' => ($bank['bank_url'] ?? '') !== '' ? (string) $bank['bank_url'] : '#',
                    'bank_url' => ($bank['bank_url'] ?? '') !== '' ? (string) $bank['bank_url'] : '#',
                    'link_amount' => $amount,
                    'link_currency' => $currentCurrency,
                    'link_tab' => $activeTab,
                    'show_disclaimer' => $activeTab === 'sell' || $activeTab === 'buy',
                  ]
                )); ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="banks-list-layout__empty" data-banks-empty <?= $banksToShow !== [] ? 'hidden' : '' ?>>
            <?php esc_html_e('По выбранным параметрам банков не найдено', 'theme'); ?>
          </p>
          <button type="button" class="banks-list-layout__load-more" data-banks-load-more <?= $totalPages > 1 ? '' : 'hidden' ?>>
            <?php esc_html_e('Показать еще 5', 'theme'); ?>
          </button>
        </div>

        <div class="banks-list-layout__map-panel" data-banks-panel="map" hidden>
          <div class="banks-list-layout__map" data-banks-map data-updated-at="<?= esc_attr($timeUpdate) ?>" data-offices="<?= esc_attr(wp_json_encode($mapOffices)) ?>">
            <button type="button" class="banks-list-layout__map-filters-button" data-banks-map-filters-open aria-label="<?php esc_attr_e('Фильтры', 'theme'); ?>">
              <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
                <rect width="48" height="48" rx="10" fill="#E30611" />
                <path d="M29 17C29 16.4067 29.1759 15.8266 29.5056 15.3333C29.8352 14.8399 30.3038 14.4554 30.8519 14.2284C31.4001 14.0013 32.0033 13.9419 32.5853 14.0576C33.1672 14.1734 33.7018 14.4591 34.1213 14.8787C34.5409 15.2982 34.8266 15.8328 34.9424 16.4147C35.0581 16.9967 34.9987 17.5999 34.7716 18.1481C34.5446 18.6962 34.1601 19.1648 33.6667 19.4944C33.1734 19.8241 32.5933 20 32 20C31.2043 20 30.4413 19.6839 29.8787 19.1213C29.3161 18.5587 29 17.7957 29 17ZM14 18H26C26.2652 18 26.5196 17.8946 26.7071 17.7071C26.8946 17.5196 27 17.2652 27 17C27 16.7348 26.8946 16.4804 26.7071 16.2929C26.5196 16.1054 26.2652 16 26 16H14C13.7348 16 13.4804 16.1054 13.2929 16.2929C13.1054 16.4804 13 16.7348 13 17C13 17.2652 13.1054 17.5196 13.2929 17.7071C13.4804 17.8946 13.7348 18 14 18ZM20 21C19.3811 21.0017 18.7778 21.1949 18.2729 21.5529C17.7681 21.911 17.3863 22.4165 17.18 23H14C13.7348 23 13.4804 23.1054 13.2929 23.2929C13.1054 23.4804 13 23.7348 13 24C13 24.2652 13.1054 24.5196 13.2929 24.7071C13.4804 24.8946 13.7348 25 14 25H17.18C17.3635 25.5189 17.6861 25.9773 18.1126 26.3251C18.5392 26.6729 19.0532 26.8966 19.5984 26.9718C20.1435 27.0471 20.6989 26.9709 21.2037 26.7516C21.7085 26.5323 22.1432 26.1784 22.4603 25.7286C22.7775 25.2788 22.9647 24.7504 23.0017 24.2013C23.0386 23.6522 22.9238 23.1035 22.6697 22.6153C22.4157 22.1271 22.0323 21.7181 21.5614 21.4332C21.0905 21.1483 20.5504 20.9985 20 21ZM34 23H26C25.7348 23 25.4804 23.1054 25.2929 23.2929C25.1054 23.4804 25 23.7348 25 24C25 24.2652 25.1054 24.5196 25.2929 24.7071C25.4804 24.8946 25.7348 25 26 25H34C34.2652 25 34.5196 24.8946 34.7071 24.7071C34.8946 24.5196 35 24.2652 35 24C35 23.7348 34.8946 23.4804 34.7071 23.2929C34.5196 23.1054 34.2652 23 34 23ZM22 30H14C13.7348 30 13.4804 30.1054 13.2929 30.2929C13.1054 30.4804 13 30.7348 13 31C13 31.2652 13.1054 31.5196 13.2929 31.7071C13.4804 31.8946 13.7348 32 14 32H22C22.2652 32 22.5196 31.8946 22.7071 31.7071C22.8946 31.5196 23 31.2652 23 31C23 30.7348 22.8946 30.4804 22.7071 30.2929C22.5196 30.1054 22.2652 30 22 30ZM34 30H30.82C30.5841 29.3328 30.1199 28.7704 29.5095 28.4124C28.8991 28.0543 28.1818 27.9235 27.4843 28.0432C26.7868 28.1629 26.154 28.5253 25.6979 29.0663C25.2418 29.6074 24.9916 30.2923 24.9916 31C24.9916 31.7077 25.2418 32.3926 25.6979 32.9337C26.154 33.4747 26.7868 33.8371 27.4843 33.9568C28.1818 34.0765 28.8991 33.9457 29.5095 33.5876C30.1199 33.2296 30.5841 32.6672 30.82 32H34C34.2652 32 34.5196 31.8946 34.7071 31.7071C34.8946 31.5196 35 31.2652 35 31C35 30.7348 34.8946 30.4804 34.7071 30.2929C34.5196 30.1054 34.2652 30 34 30Z" fill="white" />
              </svg>
            </button>
            <p class="banks-list-layout__map-placeholder">
              <?php esc_html_e('Карта офисов будет загружена.', 'theme'); ?>
            </p>
          </div>
        </div>
      </div>

      <aside class="banks-list-layout__sidebar" aria-label="<?php esc_attr_e('Дополнительная колонка', 'theme'); ?>">
        <div class="banks-list-layout__sidebar-inner">
          <section class="banks-list-layout__sidebar-card banks-list-layout__sidebar-card--cities" aria-labelledby="banks-list-sidebar-cities-heading">
            <header class="banks-list-layout__sidebar-head">
              <span class="banks-list-layout__sidebar-icon banks-list-layout__sidebar-icon--cities" aria-hidden="true">
                <svg width="38" height="41" viewBox="0 0 38 41" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                  <path d="M0 39.7261H38" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M27.9779 39.7261H34.2568L34.2568 10.1148H27.9779L27.9779 39.7261Z" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M9.98828 14.8718V6.37257L28.0128 1.31885V39.7262" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M16.301 14.8721H3.74316V39.7264H16.301V14.8721Z" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M10.0225 19.8936V24.7512" stroke="#22284B" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M10.0225 28.7651V33.6228" stroke="#22284B" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M21.6992 19.8936V24.7512" stroke="#22284B" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M21.6992 11.022V15.8796" stroke="#22284B" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M21.6992 28.7651V33.6228" stroke="#22284B" stroke-width="2" stroke-miterlimit="10" />
                </svg>
              </span>
              <h2 id="banks-list-sidebar-cities-heading" class="banks-list-layout__sidebar-title">
                <?php esc_html_e('Курсы валют в городах', 'theme'); ?>
              </h2>
            </header>
            <ul class="banks-list-layout__sidebar-cities">
              <?php foreach ($sidebarCities as $city) : ?>
                <li class="banks-list-layout__sidebar-cities-item">
                  <a class="banks-list-layout__sidebar-city-link" href="<?= esc_url(Homepage::getCityUrlForCurrency($city)) ?>">
                    <?= esc_html((string) ($city['city_name'] ?? '')) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>

          <a class="banks-list-layout__sidebar-card banks-list-layout__sidebar-card--chart" href="<?= esc_url($sidebarChartUrl) ?>">
            <span class="banks-list-layout__sidebar-icon banks-list-layout__sidebar-icon--chart" aria-hidden="true">
              <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                <path d="M33.3594 -4.32134e-07V15.6602" stroke="white" stroke-width="2" stroke-miterlimit="10" />
                <path d="M28.9062 3.13203V15.6602" stroke="white" stroke-width="2" stroke-miterlimit="10" />
                <path d="M24.4531 9.39609V15.6602" stroke="white" stroke-width="2" stroke-miterlimit="10" />
                <mask id="<?= esc_attr($sidebarChartMaskId) ?>" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="38" height="38">
                  <path d="M37 37V1H1V37H37Z" fill="white" stroke="white" stroke-width="2" />
                </mask>
                <g mask="url(#<?= esc_attr($sidebarChartMaskId) ?>)">
                  <path d="M36.8867 19.0002H19V1.11353C9.16245 1.11353 1.11328 9.1627 1.11328 19.0002C1.11328 28.8378 9.16245 36.887 19 36.887C28.8376 36.887 36.8867 28.8378 36.8867 19.0002Z" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M7.46529 32.574L19 19.0002H1.11328" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M1.74902 14.5469H19.0004" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M7.21777 5.64062H19" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M18.9998 10.0938H3.57031" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M11.4314 27.9065H3.57031" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                  <path d="M1.74902 23.4534H15.2162" stroke="#E30611" stroke-width="2" stroke-miterlimit="10" />
                </g>
              </svg>
            </span>
            <span class="banks-list-layout__sidebar-chart-text">
              <?php esc_html_e('График изменения курса валют', 'theme'); ?>
            </span>
          </a>

          <section class="banks-list-layout__sidebar-card banks-list-layout__sidebar-card--converter" aria-labelledby="banks-list-sidebar-converter-heading">
            <header class="banks-list-layout__sidebar-head banks-list-layout__sidebar-head--converter">
              <span class="banks-list-layout__sidebar-icon banks-list-layout__sidebar-icon--converter" aria-hidden="true">
                <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                  <path d="M19.0003 0C8.52267 0 0 8.52334 0 19.0003C0 29.4773 8.52267 38 19.0003 38C29.4773 38 37.9999 29.4773 38.0005 19.0003C38.0006 8.52334 29.4779 0 19.0003 0ZM19.0003 35.5687C9.8644 35.5687 2.43185 28.1362 2.43185 19.0003C2.43185 9.86447 9.8644 2.43185 19.0003 2.43185C28.1361 2.43185 35.5687 9.8644 35.5687 19.0003C35.5687 28.1361 28.1362 35.5687 19.0003 35.5687Z" fill="#E30611" />
                  <path d="M20.5223 9.44482H15.3109C14.639 9.44482 14.0949 9.98892 14.0949 10.6607V18.3957H12.5694C11.8976 18.3957 11.3535 18.9398 11.3535 19.6116C11.3535 20.2834 11.8976 20.8282 12.5694 20.8282H14.0949V22.5039H12.5694C11.8976 22.5039 11.3535 23.0486 11.3535 23.7205C11.3535 24.3923 11.8976 24.9371 12.5694 24.9371H14.0949V27.34C14.0949 28.0118 14.639 28.5566 15.3109 28.5566C15.9827 28.5566 16.5268 28.0112 16.5268 27.34V24.9371H23.1933C23.8645 24.9371 24.4092 24.3925 24.4092 23.7206C24.4092 23.0486 23.8651 22.504 23.1933 22.504H16.5268V20.8283H20.523C22.5544 20.8283 24.2676 20.2572 25.4783 19.177C26.6066 18.1705 27.2278 16.7893 27.2278 15.2881C27.2278 12.4557 24.8769 9.44482 20.5223 9.44482ZM23.8579 17.3622C23.1005 18.0387 21.9471 18.3957 20.5224 18.3957H16.5268V11.8761H20.5223C23.327 11.8761 24.7952 13.5919 24.7952 15.2874C24.7952 16.0857 24.4626 16.8227 23.8579 17.3622Z" fill="#22284B" />
                </svg>
              </span>
              <div class="banks-list-layout__sidebar-head-text">
                <h2 id="banks-list-sidebar-converter-heading" class="banks-list-layout__sidebar-title">
                  <?php esc_html_e('Конвертер валют', 'theme'); ?>
                </h2>
                <p class="banks-list-layout__sidebar-subtitle">
                  <?php esc_html_e('по курсу ЦБ РФ', 'theme'); ?>
                </p>
              </div>
            </header>

            <div class="banks-list-layout__sidebar-converter" data-banks-sidebar-converter data-rates="<?= esc_attr(wp_json_encode($sidebarCbrRates)) ?>">
              <?php
              Template::part('template-parts/components/currency-amount-group', null, [
                'input_name'      => 'banks-sidebar-amount-from',
                'options'         => $sidebarConverterFromOptions,
                'selected_index'  => $sidebarSelectedIndex,
                'select_id'       => 'banks-list-sidebar-currency-from',
              ]);
              ?>
              <div class="banks-list-layout__sidebar-converter-swap">
                <?php Template::part('template-parts/components/currency-swap-button', null, ['variant' => 'compact']); ?>
              </div>
              <?php
              Template::part('template-parts/components/currency-amount-group', null, [
                'input_name'      => 'banks-sidebar-amount-to',
                'input_readonly'  => true,
                'options'         => $sidebarConverterToOptions,
                'selected_index'  => 0,
                'select_id'       => 'banks-list-sidebar-currency-to',
              ]);
              ?>
            </div>
          </section>
        </div>
      </aside>
    </div>
  </div>
</section>
