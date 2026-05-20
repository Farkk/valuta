<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\PostTypeContent;

/**
 * @var array<string, mixed> $args {
 *     @type string $content_type Тип контента: news|articles.
 *     @type string $active_slug Активная категория (пусто — «Все»).
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'content_type' => 'news',
        'active_slug' => '',
    ]
);

$contentType = (string) $args['content_type'];

if (! PostTypeContent::isSupported($contentType)) {
    $contentType = 'news';
}

$config = PostTypeContent::getConfig($contentType);
$activeSlug = sanitize_title((string) $args['active_slug']);
$archiveUrl = PostTypeContent::getArchiveUrl($contentType);
$tabs = PostTypeContent::getCategoryTabs($contentType);

if ($tabs === [] && $activeSlug === '') {
    return;
}
?>
<nav class="tabs news-archive__tabs" aria-label="<?php echo esc_attr($config['tabs_aria_label']); ?>">
  <a
    class="tabs__item<?= $activeSlug === '' ? ' active' : '' ?>"
    href="<?php echo esc_url($archiveUrl); ?>"
    <?php if ($activeSlug === '') : ?>
      aria-current="page"
    <?php endif; ?>
  >
    <?php esc_html_e('Все', 'theme'); ?>
  </a>
  <?php foreach ($tabs as $tab) : ?>
    <?php $isActive = $activeSlug === $tab['slug']; ?>
    <a
      class="tabs__item<?= $isActive ? ' active' : '' ?>"
      href="<?php echo esc_url($tab['url']); ?>"
      <?php if ($isActive) : ?>
        aria-current="page"
      <?php endif; ?>
    >
      <?php echo esc_html($tab['name']); ?>
    </a>
  <?php endforeach; ?>
</nav>
