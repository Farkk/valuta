<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

/**
 * Кастомный селект валюты (кнопка + выпадающий список).
 *
 * @var array<string, mixed> $args {
 *     @type string               $id       Уникальный id для aria (обязательно).
 *     @type string               $layout   embedded|standalone|banks-title.
 *     @type list<array{label:string}> $options Варианты.
 *     @type int                  $selected_index Индекс выбранного варианта.
 *     @type bool                 $navigate  Переход по data-url при выборе.
 * }
 */
$args = wp_parse_args(
    Template::currentPartArgs(),
    [
        'id'              => 'currency-select',
        'layout'          => 'standalone',
        'options'         => [],
        'selected_index'  => 0,
        'navigate'        => false,
    ]
);

$id = (string) $args['id'];
$layout = (string) $args['layout'];
$navigate = (bool) $args['navigate'];

/** @var list<array{label: string}> $options */
$options = is_array($args['options']) ? $args['options'] : [];
$selectedIndex = max(0, (int) $args['selected_index']);

if ($options === []) {
    return;
}

if ($selectedIndex >= count($options)) {
    $selectedIndex = 0;
}

$currentLabel = (string) ($options[$selectedIndex]['label'] ?? $options[0]['label']);
$currentCode = (string) ($options[$selectedIndex]['code'] ?? '');

$toggleId = $id . '-toggle';
$listId = $id . '-list';
$layoutClass = match ($layout) {
    'embedded'    => 'currency-select--layout-embedded',
    'banks-title' => 'currency-select--layout-banks-title',
    default       => 'currency-select--layout-standalone',
};
?>
<div
  class="currency-select <?= esc_attr($layoutClass) ?>"
  data-currency-select
  data-selected-currency="<?= esc_attr($currentCode) ?>"
  <?php if ($navigate) : ?>
    data-currency-select-navigate
  <?php endif; ?>
  id="<?= esc_attr($id) ?>"
>
  <button
    type="button"
    class="currency-select__toggle"
    id="<?= esc_attr($toggleId) ?>"
    data-currency-select-toggle
    aria-expanded="false"
    aria-haspopup="listbox"
    aria-controls="<?= esc_attr($listId) ?>"
  >
    <span class="currency-select__value" data-currency-select-value><?= esc_html($currentLabel) ?></span>
    <span class="currency-select__caret" aria-hidden="true">
      <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
        <path d="M6.42246 6.80994L11.7247 1.50758C11.8475 1.38495 11.9151 1.22124 11.9151 1.04668C11.9151 0.872119 11.8475 0.708409 11.7247 0.585772L11.3343 0.19529C11.08 -0.0587993 10.6665 -0.0587993 10.4125 0.19529L5.96001 4.64781L1.50255 0.190349C1.37981 0.067712 1.2162 6.21323e-08 1.04174 5.32195e-08C0.867081 4.42968e-08 0.703468 0.067712 0.580636 0.190349L0.190349 0.580831C0.0676148 0.703565 2.29371e-07 0.867178 2.22842e-07 1.04174C2.16313e-07 1.2163 0.0676148 1.38001 0.190349 1.50264L5.49745 6.80994C5.62057 6.93287 5.78496 7.00039 5.95971 7C6.13515 7.00039 6.29944 6.93287 6.42246 6.80994Z" fill="currentColor" />
      </svg>
    </span>
  </button>
  <ul
    class="currency-select__list"
    id="<?= esc_attr($listId) ?>"
    data-currency-select-panel
    role="listbox"
    aria-labelledby="<?= esc_attr($toggleId) ?>"
    hidden
  >
    <?php foreach ($options as $index => $option) : ?>
      <?php
      $label = (string) ($option['label'] ?? '');
      $code = (string) ($option['code'] ?? '');
      $url = (string) ($option['url'] ?? '');
      if ($label === '') {
          continue;
      }
      $isSelected = $index === $selectedIndex;
      ?>
      <li
        role="option"
        class="currency-select__option"
        tabindex="-1"
        data-currency-select-option
        data-label="<?= esc_attr($label) ?>"
        data-code="<?= esc_attr($code) ?>"
        <?php if ($url !== '') : ?>
          data-url="<?= esc_attr($url) ?>"
        <?php endif; ?>
        aria-selected="<?= $isSelected ? 'true' : 'false' ?>"
      ><?= esc_html($label) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
