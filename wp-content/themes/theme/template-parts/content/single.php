<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\PostTypeContent;
use Theme\Helpers\Template;

$post = get_post();

if ($post === null) {
    return;
}

$contentType = $post->post_type;

if (! PostTypeContent::isSupported($contentType)) {
    $contentType = 'news';
}

$postId = $post->ID;
$views = PostTypeContent::incrementViews($postId);
$readingMinutes = PostTypeContent::getReadingTimeMinutes($post);
$dateDisplay = get_the_date('d.m.Y', $post);
$dateIso = get_the_date('Y-m-d', $post);
$imageUrl = get_the_post_thumbnail_url($post, 'large') ?: '';
$imageAlt = get_the_title($post);
$excerpt = wp_trim_words(get_the_excerpt($post), 20);
?>
<main id="primary" class="site-main news-single">
  <div class="container">
    <?php Template::breadcrumbs('news-single__breadcrumbs'); ?>

    <div class="news-single__grid">
      <article class="news-single__content">
        <header class="news-single__header">
          <div class="news-single__meta">
            <?php if ($dateDisplay !== '') : ?>
              <time class="news-single__date" datetime="<?php echo esc_attr($dateIso); ?>">
                <?php echo esc_html($dateDisplay); ?>
              </time>
            <?php endif; ?>

            <div class="news-single__meta-stats">
              <span class="news-single__views" aria-label="<?php echo esc_attr(sprintf(__('Просмотров: %d', 'theme'), $views)); ?>">
                <span class="news-single__views-icon" aria-hidden="true">
                  <svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <path d="M9 0C5.56091 0 2.44216 1.92896 0.140841 5.06211C-0.0469469 5.31881 -0.0469469 5.67742 0.140841 5.93411C2.44216 9.07104 5.56091 11 9 11C12.4391 11 15.5578 9.07104 17.8592 5.93789C18.0469 5.68119 18.0469 5.32258 17.8592 5.06589C15.5578 1.92896 12.4391 0 9 0ZM9.2467 9.37303C6.96379 9.52025 5.07855 7.59128 5.22215 5.24708C5.33998 3.31434 6.86806 1.74777 8.7533 1.62697C11.0362 1.47975 12.9214 3.40872 12.7778 5.75292C12.6563 7.68188 11.1283 9.24846 9.2467 9.37303ZM9.13256 7.58373C7.90273 7.66301 6.88647 6.62491 6.96747 5.3641C7.03007 4.32224 7.85486 3.48044 8.87113 3.41249C10.101 3.33322 11.1172 4.37131 11.0362 5.63212C10.9699 6.67776 10.1451 7.51956 9.13256 7.58373Z" fill="currentColor" />
                  </svg>
                </span>
                <span class="news-single__views-count"><?php echo esc_html((string) $views); ?></span>
              </span>

              <span class="news-single__reading" aria-label="<?php echo esc_attr(sprintf(__('Время чтения: %d минут', 'theme'), $readingMinutes)); ?>">
                <span class="news-single__reading-icon" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5" />
                    <path d="M8 4.5V8.25L10.5 9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
                <span class="news-single__reading-count">
                  <?php
                  echo esc_html(
                      sprintf(
                          _n('%d мин', '%d мин', $readingMinutes, 'theme'),
                          $readingMinutes
                      )
                  );
                  ?>
                </span>
              </span>
            </div>
          </div>

          <h1 class="news-single__title"><?php the_title(); ?></h1>

          <?php if ($imageUrl !== '') : ?>
            <figure class="news-single__figure">
              <img
                class="news-single__image"
                src="<?php echo esc_url($imageUrl); ?>"
                alt="<?php echo esc_attr($imageAlt); ?>"
                width="848"
                height="477"
                decoding="async"
              >
            </figure>
          <?php endif; ?>
        </header>

        <div class="news-single__body entry-content">
          <?php the_content(); ?>
        </div>

        <button
          class="news-single__share"
          type="button"
          data-news-share
          data-url="<?php echo esc_url(get_permalink($post)); ?>"
          data-title="<?php echo esc_attr(get_the_title($post)); ?>"
          data-text="<?php echo esc_attr($excerpt); ?>"
        >
          <span class="news-single__share-icon" aria-hidden="true">
            <svg width="13" height="11" viewBox="0 0 13 11" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
              <path d="M12.5565 3.34451L9.61375 0.369893C9.37094 0.124453 9.12465 0 8.88177 0C8.54826 0 8.15885 0.256399 8.15885 0.97904V1.99173C6.02085 2.08592 4.02426 2.97223 2.50143 4.51148C0.88852 6.14176 0.000152344 8.30935 0 10.615C0 10.7806 0.104812 10.9278 0.260254 10.9802C0.299939 10.9936 0.340564 11 0.380809 11C0.498291 11 0.612016 10.9449 0.685344 10.8462C2.48348 8.42684 5.19063 6.99704 8.15885 6.88416V7.88127C8.15885 8.60386 8.54826 8.86031 8.88174 8.86031H8.88182C9.12468 8.86031 9.37097 8.73586 9.61375 8.49044L12.5565 5.51577C12.8425 5.22675 13 4.8412 13 4.43014C13 4.01916 12.8425 3.63358 12.5565 3.34451Z" fill="currentColor" />
            </svg>
          </span>
          <?php esc_html_e('Поделиться', 'theme'); ?>
        </button>
      </article>

      <?php
      Template::part('template-parts/content/sidebar', null, [
          'content_type' => $contentType,
          'exclude_post_id' => $postId,
      ]);
      ?>
    </div>
  </div>
</main>
