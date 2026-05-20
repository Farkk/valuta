<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

/**
 * @var array<string, mixed> $args {
 *     @type string $current_tag Slug активной категории (пусто — все новости).
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'current_tag' => '',
    ]
);

Template::part('template-parts/content/archive', null, [
    'content_type' => 'news',
    'current_tag' => (string) $args['current_tag'],
]);
