<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

$posts = get_posts([
    'post_type' => ['news', 'articles'],
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
]);

$newsItems = [];

foreach ($posts as $post) {
    $imageId = get_post_thumbnail_id($post);
    $image = $imageId ? wp_get_attachment_image_src($imageId, 'medium') : null;

    $newsItems[] = [
        'title' => get_the_title($post),
        'url' => get_permalink($post),
        'date' => get_the_date('d.m.Y', $post),
        'date_iso' => get_the_date('Y-m-d', $post),
        'views' => (int) get_post_meta($post->ID, 'views', true),
        'image_url' => $image[0] ?? '',
        'image_alt' => get_the_title($post),
    ];
}
?>
<section class="news-preview" aria-labelledby="news-preview-heading">
  <div class="container">
    <div class="news-preview__inner">
      <h2 id="news-preview-heading" class="news-preview__title">
        <?php esc_html_e('Новости и статьи о курсах валют', 'theme'); ?>
      </h2>
      <?php if ($newsItems !== []) : ?>
        <div class="news-preview__slider swiper" data-news-preview-slider>
          <ul class="news-preview__list swiper-wrapper">
            <?php foreach ($newsItems as $item) : ?>
              <li class="news-preview__item swiper-slide">
                <?php Template::part('template-parts/components/news-card', null, $item); ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="news-preview__pagination" data-news-preview-pagination aria-label="<?php esc_attr_e('Навигация по новостям', 'theme'); ?>"></div>
        <div class="news-preview__action">
          <a class="news-preview__cta" href="<?php echo esc_url(get_post_type_archive_link('news') ?: home_url('/news/')); ?>">
            <?php esc_html_e('Все новости и статьи', 'theme'); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
