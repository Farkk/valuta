<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Banks\BankManager;
use Theme\Banks\BankOffices;
use Theme\Banks\BankRegistry;

/**
 * @var array<string, mixed> $args
 */
$args = wp_parse_args(
    $args ?? [],
    [
        'office' => [],
        'bank_code' => '',
        'bank_logo' => '',
        'bank_name' => '',
        'bank_url' => '',
        'tab' => 'buy',
        'amount' => 0.0,
        'show_disclaimer' => false,
    ]
);

$office = is_array($args['office']) ? $args['office'] : [];
$bankCode = BankManager::normalizeCode((string) ($args['bank_code'] ?? ''));
$bankLogo = trim((string) $args['bank_logo']);
$bankName = trim((string) $args['bank_name']);
$bankUrl = trim((string) $args['bank_url']);
if ($bankUrl === '' && $bankCode !== '') {
  $bankUrl = BankRegistry::getBankUrl($bankCode);
}
$tab = (string) $args['tab'];
$amount = (float) $args['amount'];
$showDisclaimer = (bool) $args['show_disclaimer'];

$address = trim((string) ($office['address'] ?? ''));
if ($address === '') {
    return;
}

$workStatus = trim((string) ($office['work_status'] ?? ''));
$rates = BankOffices::formatRates($office, $tab, $amount);
$mapUrl = BankOffices::getMapUrl($office);
?>
<article class="bank-single__office">
  <div class="bank-single__office-main">
    <div class="bank-single__office-left">
      <div class="bank-single__office-logo-wrap">
        <?php if ($bankLogo !== '') : ?>
          <img
            class="bank-single__office-logo"
            src="<?php echo esc_url($bankLogo); ?>"
            width="38"
            height="38"
            alt="<?php echo esc_attr($bankName); ?>"
            decoding="async"
            loading="lazy"
          >
        <?php else : ?>
          <span class="bank-single__office-logo bank-single__office-logo--placeholder" aria-hidden="true"></span>
        <?php endif; ?>
      </div>
      <div class="bank-single__office-content">
        <p class="bank-single__office-address"><?php echo esc_html($address); ?></p>
        <?php if ($workStatus !== '') : ?>
          <p class="bank-single__office-work-status"><?php echo esc_html($workStatus); ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="bank-single__office-right">
      <div class="bank-single__office-rates">
        <?php foreach ($rates as $rate) : ?>
          <div class="bank-single__office-rate">
            <p class="bank-single__office-rate-label"><?php echo esc_html((string) ($rate['label'] ?? '')); ?></p>
            <p class="bank-single__office-rate-value<?php echo ! empty($rate['primary']) ? ' bank-single__office-rate-value--primary' : ''; ?>">
              <?php echo esc_html((string) ($rate['value'] ?? '')); ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="bank-single__office-actions">
        <?php if ($mapUrl !== '') : ?>
          <a
            class="bank-single__office-map-link"
            href="<?php echo esc_url($mapUrl); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="<?php esc_attr_e('Показать на карте', 'theme'); ?>"
          >
            <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
              <rect width="33" height="33" rx="16.5" fill="#F2F3F7" />
              <path d="M17.1811 11.8533C15.3461 11.8533 13.8533 13.3461 13.8533 15.1811C13.8533 17.016 15.3461 18.5089 17.1811 18.5089C19.016 18.5089 20.5089 17.016 20.5089 15.1811C20.5089 13.3461 19.016 11.8533 17.1811 11.8533ZM17.1811 17.1077C16.1187 17.1077 15.2544 16.2434 15.2544 15.1811C15.2544 14.1187 16.1187 13.2544 17.1811 13.2544C18.2434 13.2544 19.1077 14.1187 19.1077 15.1811C19.1077 16.2434 18.2434 17.1077 17.1811 17.1077Z" fill="#22284B" />
              <path d="M17.181 8C13.2214 8 10 11.2214 10 15.181V15.3796C10 17.3821 11.1481 19.7157 13.4126 22.3155C15.0541 24.2001 16.6727 25.5142 16.7408 25.5693L17.181 25.925L17.6213 25.5693C17.6894 25.5143 19.308 24.2001 20.9495 22.3155C23.2139 19.7157 24.3621 17.3822 24.3621 15.3796V15.1811C24.3621 11.2214 21.1407 8 17.181 8ZM22.9609 15.3796C22.9609 18.7638 18.6004 22.8605 17.181 24.1041C15.7613 22.8601 11.4012 18.7636 11.4012 15.3796V15.1811C11.4012 11.9941 13.994 9.4012 17.181 9.4012C20.3681 9.4012 22.9609 11.9941 22.9609 15.1811V15.3796Z" fill="#22284B" />
            </svg>
          </a>
        <?php endif; ?>

        <?php if ($bankUrl !== '') : ?>
          <a class="bank-single__office-reserve-btn" href="<?php echo esc_url($bankUrl); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('Забронировать', 'theme'); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($showDisclaimer) : ?>
    <p class="bank-single__office-disclaimer">
      <?php esc_html_e('Расчет суммы носит ориентировочный характер основываясь на курсах Банка и не учитывает дополнительные комиссии.', 'theme'); ?>
    </p>
  <?php endif; ?>
</article>
