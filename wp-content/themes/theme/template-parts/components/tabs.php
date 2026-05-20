<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

$args = wp_parse_args(Template::currentPartArgs(), ['default_tab' => 'all']);
$defaultTab = in_array($args['default_tab'], ['all', 'sell', 'buy'], true) ? (string) $args['default_tab'] : 'all';
?>
<div class="tabs">
  <button type="button" class="tabs__item <?= $defaultTab === 'all' ? 'active' : '' ?>" data-converter-tab="all"><?php esc_html_e('Все курсы', 'theme'); ?></button>
  <button type="button" class="tabs__item <?= $defaultTab === 'sell' ? 'active' : '' ?>" data-converter-tab="sell"><?php esc_html_e('Я хочу продать', 'theme'); ?></button>
  <button type="button" class="tabs__item <?= $defaultTab === 'buy' ? 'active' : '' ?>" data-converter-tab="buy"><?php esc_html_e('Я хочу купить', 'theme'); ?></button>
</div>
