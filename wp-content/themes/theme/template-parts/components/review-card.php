<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$name = trim((string) ($args['name'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$fullText = trim((string) ($args['full_text'] ?? $text));
$date = trim((string) ($args['date'] ?? ''));
$bank = trim((string) ($args['bank'] ?? ''));
$city = trim((string) ($args['city'] ?? ''));
$rating = max(0, min(5, (int) ($args['rating'] ?? 0)));
$modifier = trim((string) ($args['modifier'] ?? ''));

$classes = ['reviews__card'];
if ($modifier !== '') {
    $classes[] = 'reviews__card--' . sanitize_html_class($modifier);
}
?>
<article
  class="<?php echo esc_attr(implode(' ', $classes)); ?>"
  data-review-name="<?php echo esc_attr($name); ?>"
  data-review-full-text="<?php echo esc_attr($fullText); ?>"
  data-review-date="<?php echo esc_attr($date); ?>"
  data-review-bank="<?php echo esc_attr($bank); ?>"
  data-review-city="<?php echo esc_attr($city); ?>"
  data-review-rating="<?php echo esc_attr((string) $rating); ?>"
>
  <div class="reviews__card-header">
    <div class="person"><?php echo esc_html($name); ?></div>
    <?php if ($bank !== '') : ?>
      <div class="bank"><?php echo esc_html($bank); ?></div>
    <?php endif; ?>
  </div>

  <div class="reviews__card-body">
    <p data-review-text><?php echo esc_html($text); ?></p>
    <button class="reviews__card-read-more" type="button" hidden data-review-toggle>
      <?php esc_html_e('Читать полностью', 'theme'); ?>
    </button>
  </div>

  <div class="reviews__card-footer">
    <div class="left">
      <?php if ($city !== '') : ?>
        <div class="city">
          <svg width="11" height="14" viewBox="0 0 11 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M5.49994 2.95105C4.09456 2.95105 2.95117 4.09444 2.95117 5.49982C2.95117 6.90521 4.09456 8.0486 5.49994 8.0486C6.90533 8.0486 8.04872 6.90521 8.04872 5.49982C8.04872 4.09444 6.90536 2.95105 5.49994 2.95105ZM5.49994 6.97544C4.68629 6.97544 4.02433 6.31347 4.02433 5.49982C4.02433 4.68617 4.68629 4.02421 5.49994 4.02421C6.3136 4.02421 6.97556 4.68617 6.97556 5.49982C6.97556 6.31347 6.3136 6.97544 5.49994 6.97544Z" fill="#22284B" />
            <path d="M5.5 0C2.46728 0 0 2.46731 0 5.5V5.65204C0 7.18581 0.879346 8.97314 2.61369 10.9643C3.87096 12.4077 5.11066 13.4142 5.16278 13.4564L5.5 13.7289L5.83722 13.4564C5.88937 13.4143 7.12907 12.4078 8.38631 10.9643C10.1206 8.97314 11 7.18584 11 5.65206V5.50003C11 2.46731 8.53272 0 5.5 0ZM9.92684 5.65206C9.92684 8.24406 6.5871 11.3817 5.5 12.3342C4.4126 11.3814 1.07316 8.24387 1.07316 5.65206V5.50003C1.07316 3.05907 3.05905 1.07319 5.5 1.07319C7.94095 1.07319 9.92684 3.05907 9.92684 5.50003V5.65206Z" fill="#22284B" />
          </svg>
          <?php echo esc_html($city); ?>
        </div>
      <?php endif; ?>
      <?php if ($date !== '') : ?>
        <div class="date"><?php echo esc_html($date); ?></div>
      <?php endif; ?>
    </div>
    <div class="right" aria-label="<?php echo esc_attr(sprintf(__('Рейтинг: %d из 5', 'theme'), $rating)); ?>">
      <?php for ($i = 0; $i < 5; $i++) : ?>
        <?php $active = $i < $rating; ?>
        <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <g clip-path="url(#clip_review_<?php echo esc_attr((string) $i); ?>_<?php echo esc_attr(md5($name . $date . $i)); ?>)">
            <path d="M16.8234 6.111H10.3974L8.41142 0L6.44043 8.083L8.41142 12.223L13.6114 16L11.6254 9.889L16.8234 6.111Z" fill="<?php echo $active ? '#E30611' : '#B9D0E2'; ?>" />
            <path d="M6.426 6.111H0L5.2 9.888L3.213 16L8.413 12.223V0L6.426 6.111Z" fill="<?php echo $active ? '#E30611' : '#B9D0E2'; ?>" />
          </g>
          <defs>
            <clipPath id="clip_review_<?php echo esc_attr((string) $i); ?>_<?php echo esc_attr(md5($name . $date . $i)); ?>">
              <rect width="16.823" height="16" fill="white" />
            </clipPath>
          </defs>
        </svg>
      <?php endfor; ?>
    </div>
  </div>
</article>
