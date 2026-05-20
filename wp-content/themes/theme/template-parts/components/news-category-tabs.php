<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

/**
 * @var array<string, mixed> $args {
 *     @type string $active_slug Активная категория (пусто — «Все»).
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'active_slug' => '',
    ]
);

Template::part('template-parts/components/content-category-tabs', null, [
    'content_type' => 'news',
    'active_slug' => (string) $args['active_slug'],
]);
