<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

/**
 * Составное поле: сумма + селект валюты «от».
 *
 * @var array<string, mixed> $args {
 *     @type string               $input_name   Имя поля ввода.
 *     @type string               $input_value  Значение.
 *     @type string               $input_placeholder Подсказка в пустом поле.
 *     @type bool                 $input_readonly Делает поле только для чтения.
 *     @type list<array{label:string}> $options Варианты селекта.
 *     @type int                  $selected_index Индекс валюты.
 *     @type string               $select_id      Id корневого узла кастомного селекта валюты.
 * }
 */
$args = wp_parse_args(
    Template::currentPartArgs(),
    [
        'input_name'      => 'amount',
        'input_value'     => '',
        'input_placeholder' => '1 000',
        'input_readonly'  => false,
        'options'         => [],
        'selected_index'  => 0,
        'select_id'       => 'converter-currency-from',
    ]
);

$inputName = (string) $args['input_name'];
$inputValue = (string) $args['input_value'];
$inputPlaceholder = (string) $args['input_placeholder'];
$inputReadonly = ! empty($args['input_readonly']);
$selectOptions = is_array($args['options']) ? $args['options'] : [];
$selectedIndex = (int) $args['selected_index'];
$selectId = (string) $args['select_id'];

?>
<div class="currency-amount-group">
  <label class="screen-reader-text" for="<?= esc_attr($inputName) ?>"><?php esc_html_e('Сумма', 'theme'); ?></label>
  <input
    class="currency-amount-group__input"
    type="text"
    inputmode="decimal"
    name="<?= esc_attr($inputName) ?>"
    id="<?= esc_attr($inputName) ?>"
    <?php if ($inputValue !== '') : ?>
    value="<?= esc_attr($inputValue) ?>"
    <?php endif; ?>
    <?php if ($inputPlaceholder !== '' && ! $inputReadonly) : ?>
    placeholder="<?= esc_attr($inputPlaceholder) ?>"
    <?php endif; ?>
    autocomplete="off"
    <?= $inputReadonly ? 'readonly' : '' ?>
  />
  <div class="currency-amount-group__select">
    <?php
    Template::part('template-parts/components/currency-select', null, [
        'id'             => $selectId,
        'layout'         => 'embedded',
        'options'        => $selectOptions,
        'selected_index' => $selectedIndex,
    ]);
    ?>
  </div>
</div>
