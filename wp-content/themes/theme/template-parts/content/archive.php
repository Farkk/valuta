<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\PostTypeContent;
use Theme\Helpers\Template;

/**
 * @var array<string, mixed> $args {
 *     @type string $content_type Тип контента: news|articles.
 *     @type string $current_tag Slug активной категории (пусто — все записи).
 * }
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'content_type' => 'news',
        'current_tag' => '',
    ]
);

$contentType = (string) $args['content_type'];

if (! PostTypeContent::isSupported($contentType)) {
    $contentType = 'news';
}

$config = PostTypeContent::getConfig($contentType);
$currentTag = sanitize_title((string) $args['current_tag']);
$paged = max(1, (int) get_query_var('paged', 1));
$postsPerPage = 12;

$queryArgs = [
    'post_type' => $config['post_type'],
    'posts_per_page' => $postsPerPage,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

if ($currentTag !== '') {
    $queryArgs['tax_query'] = [
        [
            'taxonomy' => $config['taxonomy'],
            'field' => 'slug',
            'terms' => $currentTag,
        ],
    ];
}

$contentQuery = new WP_Query($queryArgs);
$gridId = $contentType . '-archive-grid';
?>
<main id="primary" class="site-main news-archive">
  <section class="news-archive__section">
    <div class="container">
      <?php Template::breadcrumbs('news-archive__breadcrumbs'); ?>

      <header class="news-archive__header">
        <h1 class="news-archive__title"><?php echo esc_html($config['archive_title']); ?></h1>
      </header>

      <?php
      Template::part('template-parts/components/content-category-tabs', null, [
          'content_type' => $contentType,
          'active_slug' => $currentTag,
      ]);
      ?>

      <?php if ($contentQuery->have_posts()) : ?>
        <ul class="news-archive__grid" id="<?php echo esc_attr($gridId); ?>">
          <?php while ($contentQuery->have_posts()) : $contentQuery->the_post(); ?>
            <li class="news-archive__item">
              <?php Template::part('template-parts/components/news-card', null, PostTypeContent::getCardData(get_post())); ?>
            </li>
          <?php endwhile; ?>
          <?php wp_reset_postdata(); ?>
        </ul>

        <?php if ((int) $contentQuery->max_num_pages > 1) : ?>
          <div class="news-archive__action">
            <button
              class="news-archive__load-more"
              type="button"
              data-content-load-more
              data-content-type="<?php echo esc_attr($contentType); ?>"
              data-current-page="<?php echo esc_attr((string) $paged); ?>"
              data-total-pages="<?php echo esc_attr((string) $contentQuery->max_num_pages); ?>"
              data-tag="<?php echo esc_attr($currentTag); ?>"
            >
              <?php esc_html_e('Показать ещё', 'theme'); ?>
            </button>
          </div>
        <?php endif; ?>
      <?php else : ?>
        <p class="news-archive__empty"><?php echo esc_html($config['empty_message']); ?></p>
      <?php endif; ?>
    </div>
  </section>
</main>
