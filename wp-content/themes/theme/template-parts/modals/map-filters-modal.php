<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Currencies\CurrencyManager;
use Theme\Currencies\CurrencyRouter;
use Theme\Filters\FilterRegistry;
use Theme\Helpers\Template;

$currencies = array_values(array_filter(
    CurrencyManager::getCurrencies(),
    static fn(array $currency): bool => ($currency['code'] ?? '') !== 'rub'
));
$currentCurrency = CurrencyRouter::getCurrentCurrency();
$selectedIndex = 0;

foreach ($currencies as $index => $currency) {
    if (($currency['code'] ?? '') === $currentCurrency) {
        $selectedIndex = $index;
        break;
    }
}

$filters = FilterRegistry::getFilters();
$defaultTab = FilterRegistry::getDefaultTab();
?>
<div class="bank-map-filters-modal" id="bank-map-filters-modal" aria-hidden="true" data-map-filters-modal>
  <div class="bank-map-filters-modal__overlay" data-map-filters-close></div>
  <div class="bank-map-filters-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="bank-map-filters-title">
    <button type="button" class="bank-map-filters-modal__close" data-map-filters-close aria-label="<?php esc_attr_e('Закрыть', 'theme'); ?>">×</button>
    <h2 class="bank-map-filters-modal__title" id="bank-map-filters-title"><?php esc_html_e('Фильтры', 'theme'); ?></h2>

    <div class="bank-map-filters-modal__tabs" role="tablist" aria-label="<?php esc_attr_e('Режим обмена', 'theme'); ?>">
      <button type="button" class="bank-map-filters-modal__tab<?= $defaultTab === 'all' ? ' is-active' : '' ?>" data-map-filters-tab="all"><?php esc_html_e('Все курсы', 'theme'); ?></button>
      <button type="button" class="bank-map-filters-modal__tab<?= $defaultTab === 'sell' ? ' is-active' : '' ?>" data-map-filters-tab="sell"><?php esc_html_e('Я хочу продать', 'theme'); ?></button>
      <button type="button" class="bank-map-filters-modal__tab<?= $defaultTab === 'buy' ? ' is-active' : '' ?>" data-map-filters-tab="buy"><?php esc_html_e('Я хочу купить', 'theme'); ?></button>
    </div>

    <div class="bank-map-filters-modal__controls">
      <label class="bank-map-filters-modal__field">
        <span class="bank-map-filters-modal__label"><?php esc_html_e('Сумма', 'theme'); ?></span>
        <input type="text" class="bank-map-filters-modal__input" inputmode="decimal" placeholder="1 000" data-map-filters-amount>
      </label>
      <div class="bank-map-filters-modal__field">
        <span class="bank-map-filters-modal__label"><?php esc_html_e('Валюта', 'theme'); ?></span>
        <?php
        Template::part('template-parts/components/currency-select', null, [
            'id' => 'bank-map-filters-currency',
            'layout' => 'standalone',
            'options' => $currencies,
            'selected_index' => $selectedIndex,
        ]);
        ?>
      </div>
    </div>

    <div class="bank-map-filters-modal__filters" data-map-filters-list>
      <?php foreach ($filters as $filter) : ?>
        <label class="banks-list-layout__filter bank-map-filters-modal__filter">
          <input
            class="banks-list-layout__checkbox-input"
            type="checkbox"
            data-map-filter
            data-filter-slug="<?= esc_attr((string) $filter['slug']) ?>"
            data-show-in-buy="<?= ! empty($filter['show_in_buy']) ? '1' : '0' ?>"
            data-show-in-sell="<?= ! empty($filter['show_in_sell']) ? '1' : '0' ?>"
            data-currency-codes="<?= esc_attr(implode(',', $filter['currency_codes'] ?? [])) ?>"
            <?= ! empty($filter['checked_by_default']) ? 'checked' : '' ?>
          />
          <span class="banks-list-layout__checkbox-icon" aria-hidden="true">
            <svg class="banks-list-layout__checkbox-svg banks-list-layout__checkbox-svg--checked" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M11 0C17.0741 0 22 4.92594 22 11C22 17.0741 17.0741 22 11 22C4.92594 22 0 17.0741 0 11C0 4.92594 4.92594 0 11 0ZM8.71571 14.5729L6.02264 11.8776C5.56384 11.4185 5.56374 10.6699 6.02264 10.2109C6.48164 9.752 7.23359 9.75487 7.68925 10.2109L9.58785 12.111L14.3109 7.38791C14.7699 6.92891 15.5186 6.92891 15.9775 7.38791C16.4365 7.84681 16.4359 8.59617 15.9775 9.05452L10.4198 14.6122C9.96147 15.0706 9.2121 15.0712 8.7532 14.6122C8.7403 14.5993 8.72786 14.5862 8.71571 14.5729Z" fill="#E30611" />
            </svg>
            <svg class="banks-list-layout__checkbox-svg banks-list-layout__checkbox-svg--unchecked" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="11" cy="11" r="10.5" fill="#fff" stroke="#CCD2DF" />
            </svg>
          </span>
          <span class="banks-list-layout__filter-label"><?= esc_html((string) $filter['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>

    <div class="bank-map-filters-modal__actions">
      <button type="button" class="button-primary" data-map-filters-apply><?php esc_html_e('Найти', 'theme'); ?></button>
      <button type="button" class="bank-map-filters-modal__reset" data-map-filters-reset><?php esc_html_e('Очистить', 'theme'); ?></button>
    </div>
  </div>
</div>
