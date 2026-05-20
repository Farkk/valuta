<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyManager;
use Theme\Helpers\Homepage;
use Theme\Helpers\Template;
use Theme\Rates\RatesManager;

$currentCity = CityManager::getCurrentCity();
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
$sidebarRatesResponse = RatesManager::getRates((string) ($currentCity['city_name'] ?? ''), 'usd');
$sidebarCbrRates = [];

foreach (($sidebarRatesResponse['cbr_rates']['today'] ?? []) as $code => $rate) {
    if (is_numeric($rate)) {
        $sidebarCbrRates[strtolower((string) $code)] = (float) $rate;
    }
}

$sidebarCities = CityManager::getCities();
?>
<aside class="bank-single__sidebar banks-list-layout__sidebar" aria-label="<?php esc_attr_e('Дополнительная колонка', 'theme'); ?>">
  <div class="banks-list-layout__sidebar-inner">
    <section class="banks-list-layout__sidebar-card banks-list-layout__sidebar-card--converter" aria-labelledby="bank-single-sidebar-converter-heading">
      <header class="banks-list-layout__sidebar-head banks-list-layout__sidebar-head--converter">
        <span class="banks-list-layout__sidebar-icon banks-list-layout__sidebar-icon--converter" aria-hidden="true">
          <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
            <path d="M19.0003 0C8.52267 0 0 8.52334 0 19.0003C0 29.4773 8.52267 38 19.0003 38C29.4773 38 37.9999 29.4773 38.0005 19.0003C38.0006 8.52334 29.4779 0 19.0003 0ZM19.0003 35.5687C9.8644 35.5687 2.43185 28.1362 2.43185 19.0003C2.43185 9.86447 9.8644 2.43185 19.0003 2.43185C28.1361 2.43185 35.5687 9.8644 35.5687 19.0003C35.5687 28.1361 28.1362 35.5687 19.0003 35.5687Z" fill="#E30611" />
            <path d="M20.5223 9.44482H15.3109C14.639 9.44482 14.0949 9.98892 14.0949 10.6607V18.3957H12.5694C11.8976 18.3957 11.3535 18.9398 11.3535 19.6116C11.3535 20.2834 11.8976 20.8282 12.5694 20.8282H14.0949V22.5039H12.5694C11.8976 22.5039 11.3535 23.0486 11.3535 23.7205C11.3535 24.3923 11.8976 24.9371 12.5694 24.9371H14.0949V27.34C14.0949 28.0118 14.639 28.5566 15.3109 28.5566C15.9827 28.5566 16.5268 28.0112 16.5268 27.34V24.9371H23.1933C23.8645 24.9371 24.4092 24.3925 24.4092 23.7206C24.4092 23.0486 23.8651 22.504 23.1933 22.504H16.5268V20.8283H20.523C22.5544 20.8283 24.2676 20.2572 25.4783 19.177C26.6066 18.1705 27.2278 16.7893 27.2278 15.2881C27.2278 12.4557 24.8769 9.44482 20.5223 9.44482ZM23.8579 17.3622C23.1005 18.0387 21.9471 18.3957 20.5224 18.3957H16.5268V11.8761H20.5223C23.327 11.8761 24.7952 13.5919 24.7952 15.2874C24.7952 16.0857 24.4626 16.8227 23.8579 17.3622Z" fill="#22284B" />
          </svg>
        </span>
        <div class="banks-list-layout__sidebar-head-text">
          <h2 id="bank-single-sidebar-converter-heading" class="banks-list-layout__sidebar-title">
            <?php esc_html_e('Конвертер валют', 'theme'); ?>
          </h2>
          <p class="banks-list-layout__sidebar-subtitle">
            <?php esc_html_e('по курсу ЦБ РФ', 'theme'); ?>
          </p>
        </div>
      </header>

      <div class="banks-list-layout__sidebar-converter" data-sidebar-converter data-rates="<?php echo esc_attr(wp_json_encode($sidebarCbrRates)); ?>">
        <?php
        Template::part('template-parts/components/currency-amount-group', null, [
            'input_name' => 'bank-sidebar-amount-from',
            'options' => $sidebarConverterFromOptions,
            'selected_index' => $sidebarSelectedIndex,
            'select_id' => 'bank-single-sidebar-currency-from',
        ]);
        ?>
        <div class="banks-list-layout__sidebar-converter-swap">
          <?php Template::part('template-parts/components/currency-swap-button', null, ['variant' => 'compact']); ?>
        </div>
        <?php
        Template::part('template-parts/components/currency-amount-group', null, [
            'input_name' => 'bank-sidebar-amount-to',
            'input_readonly' => true,
            'options' => $sidebarConverterToOptions,
            'selected_index' => 0,
            'select_id' => 'bank-single-sidebar-currency-to',
        ]);
        ?>
      </div>
    </section>

    <section class="banks-list-layout__sidebar-card banks-list-layout__sidebar-card--cities" aria-labelledby="bank-single-sidebar-cities-heading">
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
        <h2 id="bank-single-sidebar-cities-heading" class="banks-list-layout__sidebar-title">
          <?php esc_html_e('Курсы валют в городах', 'theme'); ?>
        </h2>
      </header>
      <ul class="banks-list-layout__sidebar-cities">
        <?php foreach ($sidebarCities as $city) : ?>
          <li class="banks-list-layout__sidebar-cities-item">
            <a class="banks-list-layout__sidebar-city-link" href="<?php echo esc_url(Homepage::getCityUrlForCurrency($city)); ?>">
              <?php echo esc_html((string) ($city['city_name'] ?? '')); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </div>
</aside>
