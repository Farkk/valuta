<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Breadcrumbs;

/**
 * @var array<string, mixed> $args {
 *     @type list<array{title: string, url: string}>|null $items Элементы хлебных крошек.
 *     @type string $class Дополнительный CSS-класс.
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'items' => null,
        'class' => '',
    ]
);

if (! Breadcrumbs::shouldRender()) {
    return;
}

$items = is_array($args['items']) ? $args['items'] : Breadcrumbs::get();

if ($items === []) {
    return;
}

$classNames = trim('breadcrumbs ' . (string) $args['class']);
$lastIndex = count($items) - 1;
?>
<nav class="<?php echo esc_attr($classNames); ?>" aria-label="<?php esc_attr_e('Хлебные крошки', 'theme'); ?>">
  <?php foreach ($items as $index => $item) : ?>
    <?php
    $title = trim((string) ($item['title'] ?? ''));
    $url = trim((string) ($item['url'] ?? ''));
    $isLast = $index === $lastIndex;

    if ($title === '') {
        continue;
    }
    ?>
    <?php if ($isLast || $url === '') : ?>
      <span class="breadcrumbs__item breadcrumbs__item--current"><?php echo esc_html($title); ?></span>
    <?php else : ?>
      <a class="breadcrumbs__item" href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
      <span class="breadcrumbs__separator" aria-hidden="true">/</span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
