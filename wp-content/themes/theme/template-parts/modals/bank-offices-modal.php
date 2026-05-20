<?php

declare(strict_types=1);

defined('ABSPATH') || exit;
?>
<div class="modal-bank-offices" id="modal-bank-offices" aria-hidden="true">
  <div class="modal-bank-offices__overlay" data-bank-offices-close></div>
  <div class="modal-bank-offices__container" role="dialog" aria-modal="true" aria-labelledby="modal-bank-offices-title">
    <div class="modal-bank-offices__header">
      <div class="modal-bank-offices__header-content">
        <div class="modal-bank-offices__heading-line">
          <h3 class="modal-bank-offices__title" id="modal-bank-offices-title"><?php esc_html_e('Отделения', 'theme'); ?></h3>
          <p class="modal-bank-offices__bank-name" data-bank-offices-name></p>
        </div>
        <p class="modal-bank-offices__update-time" data-bank-offices-update></p>
      </div>
      <button class="modal-bank-offices__close" type="button" aria-label="<?php esc_attr_e('Закрыть', 'theme'); ?>" data-bank-offices-close>
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
          <circle cx="20" cy="20" r="20" fill="#22284B" />
          <path d="M16.1771 25.3846C15.9244 25.3993 15.6759 25.3153 15.4839 25.1503C15.1053 24.7694 15.1053 24.1544 15.4839 23.7735L23.7737 15.4837C24.1674 15.1152 24.7853 15.1357 25.1538 15.5295C25.487 15.8856 25.5064 16.4329 25.1993 16.8116L16.8606 25.1503C16.6711 25.3129 16.4266 25.3968 16.1771 25.3846Z" fill="white" />
          <path d="M24.4569 25.3845C24.2008 25.3834 23.9553 25.2817 23.7734 25.1014L15.4836 16.8115C15.1328 16.4019 15.1805 15.7855 15.5901 15.4347C15.9557 15.1217 16.4948 15.1217 16.8603 15.4347L25.199 23.7246C25.5926 24.0931 25.613 24.711 25.2444 25.1047C25.2298 25.1204 25.2146 25.1355 25.199 25.1502C24.9948 25.3277 24.7261 25.4126 24.4569 25.3845Z" fill="white" />
        </svg>
      </button>
    </div>
    <div class="modal-bank-offices__content">
      <div class="modal-bank-offices__list" id="bank-offices-list" data-bank-offices-list>
        <div class="modal-bank-offices__loading"><?php esc_html_e('Загрузка...', 'theme'); ?></div>
      </div>
    </div>
  </div>
</div>
