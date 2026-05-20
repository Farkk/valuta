<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Banks\BankManager;
use Theme\Helpers\Template;

$bank = BankManager::getCurrentBank();

if ($bank === []) {
    return;
}

$bankName = (string) ($bank['bank_name'] ?? '');
$bankLogo = (string) ($bank['bank_logo'] ?? '');
$bankDescription = (string) ($bank['bank_description'] ?? '');
$updateTime = (string) ($bank['update_time'] ?? '');
$offices = is_array($bank['offices'] ?? null) ? $bank['offices'] : [];
$tab = (string) ($bank['tab'] ?? 'buy');
$amount = (float) ($bank['amount'] ?? 0);
$bankUrl = (string) ($bank['bank_url'] ?? '');
$showDisclaimer = ! empty($bank['show_disclaimer']);
?>
<div class="container">
  <?php Template::breadcrumbs('bank-single__breadcrumbs'); ?>

  <div class="bank-single__grid">
    <div class="bank-single__main">
      <article class="bank-single__card" aria-label="<?php echo esc_attr($bankName); ?>">
        <header class="bank-single__header">
          <h1 class="bank-single__title"><?php echo esc_html($bankName); ?></h1>
          <?php if ($bankLogo !== '') : ?>
            <img
              class="bank-single__logo"
              src="<?php echo esc_url($bankLogo); ?>"
              width="49"
              height="49"
              alt=""
              decoding="async"
            >
          <?php endif; ?>
        </header>

        <?php if ($bankDescription !== '') : ?>
          <div class="bank-single__description entry-content">
            <?php echo wp_kses_post(wpautop($bankDescription)); ?>
          </div>
        <?php endif; ?>

        <section class="bank-single__offices" aria-labelledby="bank-single-offices-heading">
          <h2 id="bank-single-offices-heading" class="bank-single__offices-title">
            <?php esc_html_e('Отделения', 'theme'); ?>
          </h2>
          <?php if ($updateTime !== '') : ?>
            <p class="bank-single__offices-update"><?php echo esc_html($updateTime); ?></p>
          <?php endif; ?>

          <?php if ($offices === []) : ?>
            <p class="bank-single__offices-empty"><?php esc_html_e('Офисы не найдены', 'theme'); ?></p>
          <?php else : ?>
            <div class="bank-single__offices-list">
              <?php foreach ($offices as $office) : ?>
                <?php
                if (! is_array($office)) {
                    continue;
                }

                Template::part('template-parts/banks/bank-office-card', null, [
                    'office' => $office,
                    'bank_code' => (string) ($bank['bank_code'] ?? ''),
                    'bank_logo' => $bankLogo,
                    'bank_name' => $bankName,
                    'bank_url' => $bankUrl,
                    'tab' => $tab,
                    'amount' => $amount,
                    'show_disclaimer' => $showDisclaimer,
                ]);
                ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </article>
    </div>

    <?php Template::part('template-parts/banks/bank-sidebar'); ?>
  </div>
</div>
