<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

/**
 * @var array<string, mixed> $args {
 *     @type int $exclude_post_id ID текущей новости.
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'exclude_post_id' => 0,
    ]
);

Template::part('template-parts/content/sidebar', null, [
    'content_type' => 'news',
    'exclude_post_id' => (int) $args['exclude_post_id'],
]);
