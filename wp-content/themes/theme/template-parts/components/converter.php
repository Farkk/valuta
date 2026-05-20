<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

$args = wp_parse_args(
    Template::currentPartArgs(),
    [
        'currency' => 'usd',
        'amount' => '',
        'default_tab' => 'buy',
    ]
);
?>
<div class="converter" data-default-tab="<?= esc_attr((string) $args['default_tab']) ?>">
  <?php Template::part('template-parts/components/tabs', null, ['default_tab' => $args['default_tab']]); ?>
  <h3 class="converter__title"><?php esc_html_e('У меня есть:', 'theme'); ?></h3>
  <?php Template::part('template-parts/components/converter-actions', null, $args); ?>
</div>
