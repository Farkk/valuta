<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;
use Theme\Currencies\CurrencyManager;

$args = wp_parse_args(
    Template::currentPartArgs(),
    [
        'currency' => 'usd',
        'amount' => '',
    ]
);

$fromOptions = array_values(array_filter(
    CurrencyManager::getCurrencies(),
    static fn(array $currency): bool => ($currency['code'] ?? '') !== 'rub'
));

$toOptions = [
    CurrencyManager::formatCurrency('RUB'),
];

$selectedIndex = 0;
$currentCurrency = strtolower((string) $args['currency']);

foreach ($fromOptions as $index => $option) {
    if (($option['code'] ?? '') === $currentCurrency) {
        $selectedIndex = $index;
        break;
    }
}
?>
<div class="converter__actions">
  <?php
  Template::part('template-parts/components/currency-amount-group', null, [
      'input_name'      => 'converter-amount',
      'input_value'     => (string) $args['amount'],
      'options'         => $fromOptions,
      'selected_index'  => $selectedIndex,
      'select_id'       => 'converter-currency-from',
  ]);
  Template::part('template-parts/components/currency-swap-button');
  Template::part('template-parts/components/currency-select', null, [
      'id'             => 'converter-currency-to',
      'layout'         => 'standalone',
      'options'        => $toOptions,
      'selected_index' => 0,
  ]);
  ?>
</div>
