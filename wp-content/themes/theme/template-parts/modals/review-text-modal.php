<?php

declare(strict_types=1);

defined('ABSPATH') || exit;
?>
<div class="review-text-modal" id="review-text-modal" aria-hidden="true" data-review-text-modal>
  <div class="review-text-modal__overlay" data-review-text-modal-close></div>
  <div
    class="review-text-modal__dialog"
    role="dialog"
    aria-modal="true"
    aria-labelledby="review-text-modal-title"
  >
    <button
      type="button"
      class="review-text-modal__close"
      data-review-text-modal-close
      aria-label="<?php esc_attr_e('Закрыть', 'theme'); ?>"
    >
      <svg width="50" height="50" viewBox="0 0 50 50" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
        <circle cx="25" cy="25" r="25" fill="#22284B" />
        <path d="M20.2215 31.7306C19.9056 31.749 19.5949 31.644 19.3549 31.4377C18.8817 30.9617 18.8817 30.1928 19.3549 29.7168L29.7172 19.3545C30.2094 18.8939 30.9817 18.9195 31.4423 19.4117C31.8588 19.8568 31.8831 20.541 31.4991 21.0144L21.0758 31.4377C20.839 31.641 20.5333 31.7458 20.2215 31.7306Z" fill="white" />
        <path d="M30.5712 31.7307C30.251 31.7293 29.9441 31.6022 29.7168 31.3768L19.3545 21.0144C18.9161 20.5025 18.9757 19.732 19.4877 19.2935C19.9446 18.9022 20.6185 18.9022 21.0755 19.2935L31.4988 29.6558C31.9908 30.1165 32.0163 30.8889 31.5556 31.3809C31.5373 31.4005 31.5183 31.4194 31.4988 31.4378C31.2435 31.6597 30.9076 31.7658 30.5712 31.7307Z" fill="white" />
      </svg>
    </button>

    <div class="review-text-modal__header">
      <h2 class="review-text-modal__name" id="review-text-modal-title" data-review-modal-name></h2>
      <p class="review-text-modal__bank" data-review-modal-bank hidden></p>
    </div>

    <div class="review-text-modal__body">
      <p data-review-modal-text></p>
    </div>

    <div class="review-text-modal__footer">
      <div class="review-text-modal__meta" data-review-modal-meta></div>
      <div class="review-text-modal__rating" data-review-modal-rating aria-hidden="true"></div>
    </div>
  </div>
</div>
