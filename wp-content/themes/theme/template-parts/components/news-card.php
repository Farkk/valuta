<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

/**
 * Карточка новости (превью).
 *
 * @var array<string, mixed> $args {
 *     @type string $title      Заголовок (обязательно).
 *     @type string $url        Ссылка на материал.
 *     @type string $date       Дата для отображения (например 10.01.2026).
 *     @type string $date_iso   Атрибут datetime для <time> (например 2026-01-10).
 *     @type int    $views      Число просмотров.
 *     @type string $image_url  URL превью.
 *     @type string $image_alt  Alt изображения.
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'title'     => '',
        'url'       => '#',
        'date'      => '',
        'date_iso'  => '',
        'views'     => 0,
        'image_url' => '',
        'image_alt' => '',
    ]
);

$title = trim((string) $args['title']);
if ($title === '') {
    return;
}

$url = trim((string) $args['url']);
if ($url === '') {
    $url = '#';
}

$dateDisplay = trim((string) $args['date']);
$dateIso = trim((string) $args['date_iso']);
$views = max(0, (int) $args['views']);

$imageUrl = trim((string) $args['image_url']);
if ($imageUrl === '') {
    $imageUrl = Template::asset('assets/images/news-card.jpg');
}

$imageAlt = trim((string) $args['image_alt']);
?>
<article class="news-card">
  <a class="news-card__link" href="<?php echo esc_url($url); ?>">
    <span class="news-card__media">
      <img
        class="news-card__image"
        src="<?php echo esc_url($imageUrl); ?>"
        alt="<?php echo esc_attr($imageAlt); ?>"
        width="144"
        height="81"
        loading="lazy"
        decoding="async"
      >
    </span>
    <span class="news-card__body">
      <h3 class="news-card__title"><?php echo esc_html($title); ?></h3>
      <span class="news-card__meta">
        <?php if ($dateDisplay !== '') : ?>
          <?php if ($dateIso !== '') : ?>
            <time class="news-card__date" datetime="<?php echo esc_attr($dateIso); ?>"><?php echo esc_html($dateDisplay); ?></time>
          <?php else : ?>
            <span class="news-card__date"><?php echo esc_html($dateDisplay); ?></span>
          <?php endif; ?>
        <?php endif; ?>
        <span class="news-card__views" aria-label="<?php echo esc_attr(sprintf(__('Просмотров: %d', 'theme'), $views)); ?>">
          <span class="news-card__views-icon" aria-hidden="true">
            <svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
              <path d="M9 0C5.56091 0 2.44216 1.92896 0.140841 5.06211C-0.0469469 5.31881 -0.0469469 5.67742 0.140841 5.93411C2.44216 9.07104 5.56091 11 9 11C12.4391 11 15.5578 9.07104 17.8592 5.93789C18.0469 5.68119 18.0469 5.32258 17.8592 5.06589C15.5578 1.92896 12.4391 0 9 0ZM9.2467 9.37303C6.96379 9.52025 5.07855 7.59128 5.22215 5.24708C5.33998 3.31434 6.86806 1.74777 8.7533 1.62697C11.0362 1.47975 12.9214 3.40872 12.7778 5.75292C12.6563 7.68188 11.1283 9.24846 9.2467 9.37303ZM9.13256 7.58373C7.90273 7.66301 6.88647 6.62491 6.96747 5.3641C7.03007 4.32224 7.85486 3.48044 8.87113 3.41249C10.101 3.33322 11.1172 4.37131 11.0362 5.63212C10.9699 6.67776 10.1451 7.51956 9.13256 7.58373Z" fill="currentColor" />
            </svg>
          </span>
          <span class="news-card__views-count"><?php echo esc_html((string) $views); ?></span>
        </span>
      </span>
    </span>
  </a>
</article>
