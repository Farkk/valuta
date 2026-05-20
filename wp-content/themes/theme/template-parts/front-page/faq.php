<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Homepage;

$faq = Homepage::getFaq();
$faqTitle = trim((string) ($faq['title'] ?? ''));
$faqItems = $faq['items'] ?? [];

if ($faqTitle === '') {
    $faqTitle = __('Ответы на часто задаваемые вопросы', 'theme');
}
?>
<section class="faq" data-faq>
  <div class="container">
    <div class="faq__inner">
      <h2 class="faq__title"><?php echo esc_html($faqTitle); ?></h2>
      <div class="faq__list">
        <?php foreach ($faqItems as $index => $item) : ?>
          <?php
          $clipId = 'faq-clip-' . $index;
          $panelId = 'faq-panel-' . $index;
          $buttonId = 'faq-trigger-' . $index;
          $isOpen = $index === 0;
          ?>
          <div class="faq__item<?php echo $isOpen ? ' is-open' : ''; ?>" data-faq-item>
            <button
              type="button"
              class="faq__summary"
              id="<?php echo esc_attr($buttonId); ?>"
              aria-expanded="<?php echo $isOpen ? 'true' : 'false'; ?>"
              aria-controls="<?php echo esc_attr($panelId); ?>"
              data-faq-trigger
            >
              <span class="faq__question"><?php echo esc_html($item['question']); ?></span>
              <span class="faq__icon" aria-hidden="true">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                  <g clip-path="url(#<?php echo esc_attr($clipId); ?>)">
                    <path d="M25 12.5C25 5.60728 19.3927 6.68661e-08 12.5 1.49061e-07C5.60728 2.31256e-07 6.68661e-08 5.60728 1.49061e-07 12.5C2.31256e-07 19.3927 5.60728 25 12.5 25C19.3927 25 25 19.3927 25 12.5ZM11.7635 16.3615L6.55523 11.1531C6.3521 10.95 6.25 10.6833 6.25 10.4166C6.25 10.15 6.3521 9.8833 6.55523 9.68018C6.9625 9.2729 7.62085 9.2729 8.02813 9.68018L12.5 14.1521L16.9719 9.68022C17.3792 9.27295 18.0375 9.27295 18.4448 9.68022C18.8521 10.0875 18.8521 10.7458 18.4448 11.1531L13.2364 16.3615C12.8292 16.7687 12.1709 16.7687 11.7635 16.3615Z" fill="currentColor" />
                  </g>
                  <defs>
                    <clipPath id="<?php echo esc_attr($clipId); ?>">
                      <rect width="25" height="25" fill="white" transform="translate(25) rotate(90)" />
                    </clipPath>
                  </defs>
                </svg>
              </span>
            </button>
            <div
              class="faq__panel"
              id="<?php echo esc_attr($panelId); ?>"
              role="region"
              aria-labelledby="<?php echo esc_attr($buttonId); ?>"
              aria-hidden="<?php echo $isOpen ? 'false' : 'true'; ?>"
              data-faq-panel
            >
              <div class="faq__panel-inner">
                <div class="faq__body"><?php echo wp_kses_post(wpautop((string) ($item['answer'] ?? ''))); ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
