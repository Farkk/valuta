<?php

declare(strict_types=1);

use Theme\Cities\CityManager;
use Theme\Helpers\Reviews;
use Theme\Helpers\Text;
use Theme\Helpers\Template;

$currentCity = CityManager::getCurrentCity();
$cityName = (string) ($currentCity['city_name'] ?? 'Казань');
$cityPrepositional = Text::cityPrepositional($cityName);

$reviewsQuery = new WP_Query([
    'post_type' => 'reviews',
    'posts_per_page' => 8,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>
<section class="reviews" aria-labelledby="reviews-heading">
  <div class="container">
    <div class="reviews__inner">
      <div class="reviews__header">
        <h2 id="reviews-heading" class="reviews__title">Отзывы о курсах валют <span>в <?php echo esc_html($cityPrepositional); ?></span></h2>
        <div class="reviews__nav">
          <button class="prev" type="button" data-reviews-prev aria-label="<?php esc_attr_e('Предыдущие отзывы', 'theme'); ?>">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_reviews_prev)">
                <path d="M15 0C6.72873 0 0 6.72873 0 15C0 23.2713 6.72873 30 15 30C23.2713 30 30 23.2713 30 15C30 6.72873 23.2713 0 15 0ZM19.6338 15.8838L13.3837 22.1337C13.14 22.3775 12.82 22.5 12.5 22.5C12.18 22.5 11.86 22.3775 11.6162 22.1337C11.1275 21.645 11.1275 20.855 11.6162 20.3663L16.9825 15L11.6163 9.63375C11.1275 9.14502 11.1275 8.355 11.6163 7.86627C12.105 7.37754 12.895 7.37754 13.3837 7.86627L19.6338 14.1163C20.1225 14.605 20.1225 15.395 19.6338 15.8838Z" fill="#22284B" />
              </g>
              <defs>
                <clipPath id="clip0_reviews_prev">
                  <rect width="30" height="30" fill="white" />
                </clipPath>
              </defs>
            </svg>
          </button>
          <button class="next" type="button" data-reviews-next aria-label="<?php esc_attr_e('Следующие отзывы', 'theme'); ?>">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_reviews_next)">
                <path d="M15 0C6.72873 0 0 6.72873 0 15C0 23.2713 6.72873 30 15 30C23.2713 30 30 23.2713 30 15C30 6.72873 23.2713 0 15 0ZM19.6338 15.8838L13.3837 22.1337C13.14 22.3775 12.82 22.5 12.5 22.5C12.18 22.5 11.86 22.3775 11.6162 22.1337C11.1275 21.645 11.1275 20.855 11.6162 20.3663L16.9825 15L11.6163 9.63375C11.1275 9.14502 11.1275 8.355 11.6163 7.86627C12.105 7.37754 12.895 7.37754 13.3837 7.86627L19.6338 14.1163C20.1225 14.605 20.1225 15.395 19.6338 15.8838Z" fill="#22284B" />
              </g>
              <defs>
                <clipPath id="clip0_reviews_next">
                  <rect width="30" height="30" fill="white" />
                </clipPath>
              </defs>
            </svg>
          </button>
        </div>
      </div>

      <?php if ($reviewsQuery->have_posts()) : ?>
        <div class="reviews__slider swiper" data-reviews-slider>
          <div class="reviews__slider-track swiper-wrapper" data-reviews-track>
            <?php while ($reviewsQuery->have_posts()) : $reviewsQuery->the_post(); ?>
              <div class="reviews__slide swiper-slide">
                <?php Template::part('template-parts/components/review-card', null, Reviews::getReviewCardData(get_post(), $cityName)); ?>
              </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
          </div>
        </div>
        <div class="reviews__pagination" data-reviews-pagination aria-label="<?php esc_attr_e('Навигация по отзывам', 'theme'); ?>"></div>
      <?php else : ?>
        <p class="reviews__empty"><?php esc_html_e('Отзывов пока нет.', 'theme'); ?></p>
      <?php endif; ?>

      <a class="reviews__all" href="<?php echo esc_url(get_post_type_archive_link('reviews') ?: home_url('/reviews/')); ?>">
        <?php esc_html_e('Все отзывы', 'theme'); ?>
      </a>
    </div>
  </div>
</section>
