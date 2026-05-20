<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Primary button / link.
 *
 * @var array<string, mixed> $args {
 *     @type string $label       Visible text (required).
 *     @type string $url         If non-empty, renders as anchor.
 *     @type string $type        button|submit when $url is empty. Default 'button'.
 *     @type string $extra_class Additional classes (space-separated).
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'label'       => '',
        'url'         => '',
        'type'        => 'button',
        'extra_class' => '',
    ]
);

$label = (string) $args['label'];
if ($label === '') {
    return;
}

$url = trim((string) $args['url']);
$type = $args['type'] === 'submit' ? 'submit' : 'button';
$extra = trim((string) $args['extra_class']);
$classes = trim('btn btn--primary ' . $extra);

if ($url !== '') {
    printf(
        '<a class="%1$s" href="%2$s">%3$s</a>',
        esc_attr($classes),
        esc_url($url),
        esc_html($label)
    );

    return;
}

printf(
    '<button class="%1$s" type="%2$s">%3$s</button>',
    esc_attr($classes),
    esc_attr($type),
    esc_html($label)
);
