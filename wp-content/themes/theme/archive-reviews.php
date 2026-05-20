<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Cities\CityManager;
use Theme\Helpers\Reviews;
use Theme\Helpers\Template;
use Theme\Helpers\Text;

get_header();

$currentCity = CityManager::getCurrentCity();
$cityName = (string) ($currentCity['city_name'] ?? 'Казань');
$cityPrepositional = Text::cityPrepositional($cityName);
$paged = max(1, (int) get_query_var('paged', 1));

$reviewsQuery = new WP_Query([
    'post_type' => 'reviews',
    'posts_per_page' => 12,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
]);

$archiveUrl = get_post_type_archive_link('reviews') ?: home_url('/reviews/');
?>
<main id="primary" class="site-main reviews-archive">
  <section class="reviews-archive__section">
    <div class="container">
      <?php Template::breadcrumbs('reviews-archive__breadcrumbs'); ?>

      <header class="reviews-archive__header">
        <h1 class="reviews-archive__title">Отзывы об обмене валют <span>в <?php echo esc_html($cityPrepositional); ?></span></h1>
      </header>

      <?php if ($reviewsQuery->have_posts()) : ?>
        <div class="reviews-archive__grid" id="reviews-archive-grid">
          <?php while ($reviewsQuery->have_posts()) : $reviewsQuery->the_post(); ?>
            <?php Template::part('template-parts/components/review-card', null, array_merge(Reviews::getReviewCardData(get_post(), $cityName), ['modifier' => 'archive'])); ?>
          <?php endwhile; ?>
          <?php wp_reset_postdata(); ?>
        </div>

        <?php if ((int) $reviewsQuery->max_num_pages > 1) : ?>
          <div class="reviews-archive__action">
            <button
              class="reviews-archive__load-more"
              type="button"
              data-reviews-load-more
              data-current-page="<?php echo esc_attr((string) $paged); ?>"
              data-total-pages="<?php echo esc_attr((string) $reviewsQuery->max_num_pages); ?>"
              data-archive-url="<?php echo esc_url($archiveUrl); ?>"
            >
              <?php esc_html_e('Показать ещё', 'theme'); ?>
            </button>
          </div>
        <?php endif; ?>
      <?php else : ?>
        <p class="reviews-archive__empty"><?php esc_html_e('Отзывов пока нет.', 'theme'); ?></p>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php
get_footer();
