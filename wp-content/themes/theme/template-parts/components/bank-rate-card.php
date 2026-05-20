<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Banks\BankManager;
use Theme\Helpers\Template;

/**
 * Карточка курса банка (список офферов).
 *
 * @var array<string, mixed> $args {
 *     @type string              $bank_name   Название банка.
 *     @type string              $bank_code   Код банка.
 *     @type string              $updated_at  Текст «Обновление: …».
 *     @type string              $buy_label   Подпись первой ставки.
 *     @type string              $sell_label  Подпись второй ставки.
 *     @type string              $buy_rate    Сумма покупки с валютой (уже отформатирована).
 *     @type string              $sell_rate   Сумма продажи с валютой.
 *     @type string              $cta_variant `reserve` | `details`.
 *     @type string              $cta_url     URL основной кнопки.
 *     @type string              $bank_url    URL банка/резерва для модалок.
 *     @type string              $map_url     URL кнопки «на карте».
 *     @type string              $logo_url    URL логотипа (по умолчанию bank.png).
 *     @type list<string>        $badges      Подписи бейджей; пусто — карточка без рамки.
 *     @type string              $disclaimer  Текст дисклеймера внизу.
 * }
 */
$args = wp_parse_args(
  $args ?? [],
  [
    'bank_name'   => '',
    'bank_code'   => '',
    'updated_at'  => '',
    'buy_label'   => __('Покупка', 'theme'),
    'sell_label'  => __('Продажа', 'theme'),
    'buy_rate'    => '',
    'sell_rate'   => '',
    'cta_variant' => 'details',
    'cta_url'     => '#',
    'bank_url'    => '',
    'map_url'     => '#',
    'logo_url'    => '',
    'badges'      => [],
    'disclaimer'  => '',
  ]
);

$bankName = trim((string) $args['bank_name']);
if ($bankName === '') {
  return;
}

$bankCode = trim((string) $args['bank_code']);
$logoUrl = trim((string) $args['logo_url']);
if ($logoUrl === '') {
  $logoUrl = Template::asset('assets/images/bank.png');
}

$updatedAt = trim((string) $args['updated_at']);
$buyRate = trim((string) $args['buy_rate']);
$sellRate = trim((string) $args['sell_rate']);
$buyLabel = trim((string) $args['buy_label']);
$sellLabel = trim((string) $args['sell_label']);
$ctaUrl = trim((string) $args['cta_url']);
if ($ctaUrl === '') {
  $ctaUrl = '#';
}

$mapUrl = trim((string) $args['map_url']);
if ($mapUrl === '') {
  $mapUrl = '#';
}

$bankUrl = trim((string) $args['bank_url']);
if ($bankUrl === '') {
  $bankUrl = $ctaUrl;
}

$ctaVariant = (string) $args['cta_variant'];
$ctaText = $ctaVariant === 'details'
  ? __('Подробнее', 'theme')
  : __('Забронировать', 'theme');
$isDetailsButton = $ctaVariant === 'details';
$linkAmount = array_key_exists('link_amount', $args ?? []) ? (float) $args['link_amount'] : null;
$linkCurrency = array_key_exists('link_currency', $args ?? []) ? (string) $args['link_currency'] : null;
$linkTab = array_key_exists('link_tab', $args ?? []) ? (string) $args['link_tab'] : null;
$bankDetailUrl = $bankCode !== ''
  ? BankManager::getPageUrl($bankCode, $linkAmount, $linkCurrency, $linkTab)
  : '';
$hasDetailLink = $bankDetailUrl !== '';

$badges = $args['badges'];
if (! is_array($badges)) {
  $badges = [];
}

/** @var list<string> $badgeLabels */
$badgeLabels = [];
foreach ($badges as $label) {
  $t = trim((string) $label);
  if ($t !== '') {
    $badgeLabels[] = $t;
  }
}

$hasBadges = $badgeLabels !== [];

$disclaimer = trim((string) $args['disclaimer']);
if ($disclaimer === '') {
  $disclaimer = __(
    'Расчет суммы носит ориентировочный характер основываясь на курсах Банка и не учитывает дополнительные комиссии',
    'theme'
  );
}

$articleClasses = 'bank-rate-card'
  . ($hasBadges ? ' bank-rate-card--featured' : '')
  . ($hasDetailLink ? ' bank-rate-card--linked' : '');
$panelClass = 'bank-rate-card__panel' . ($hasBadges ? '' : ' bank-rate-card__panel--solo');
?>
<article
  class="<?php echo esc_attr($articleClasses); ?>"
  aria-label="<?php echo esc_attr($bankName); ?>"
  data-bank-card
  data-bank-code="<?php echo esc_attr($bankCode); ?>"
  data-bank-name="<?php echo esc_attr($bankName); ?>"
  data-bank-logo="<?php echo esc_url($logoUrl); ?>"
  data-bank-url="<?php echo esc_url($bankUrl); ?>">
  <?php if ($hasBadges) : ?>
    <div class="bank-rate-card__frame">
      <ul class="bank-rate-card__badges">
        <?php foreach ($badgeLabels as $badge) : ?>
          <li class="bank-rate-card__badge">
            <svg
              class="bank-rate-card__badge-icon"
              width="7"
              height="8"
              viewBox="0 0 7 8"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              aria-hidden="true"
              focusable="false">
              <ellipse cx="3.5" cy="3.52893" rx="3.5" ry="3.52893" fill="#E30611" />
            </svg>
            <span class="bank-rate-card__badge-text"><?php echo esc_html($badge); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="<?php echo esc_attr($panelClass); ?>">
      <?php else : ?>
        <div class="<?php echo esc_attr($panelClass); ?>">
        <?php endif; ?>
        <?php if ($hasDetailLink) : ?>
          <a
            class="bank-rate-card__overlay"
            href="<?php echo esc_url($bankDetailUrl); ?>"
            aria-label="<?php echo esc_attr(sprintf(__('Перейти к странице банка %s', 'theme'), $bankName)); ?>"
          ></a>
        <?php endif; ?>
        <div class="bank-rate-card__row">
          <div class="bank-rate-card__identity">
            <img
              class="bank-rate-card__logo"
              src="<?php echo esc_url($logoUrl); ?>"
              width="40"
              height="40"
              alt=""
              decoding="async"
              loading="lazy" />
            <div class="bank-rate-card__meta">
              <p class="bank-rate-card__name"><?php echo esc_html($bankName); ?></p>
              <?php if ($updatedAt !== '') : ?>
                <p class="bank-rate-card__updated"><?php echo esc_html($updatedAt); ?></p>
              <?php endif; ?>
            </div>
          </div>

          <div class="bank-rate-card__rates">
            <div class="bank-rate-card__rate">
              <span class="bank-rate-card__rate-label"><?php echo esc_html($buyLabel); ?></span>
              <span class="bank-rate-card__rate-value bank-rate-card__rate-value--buy"><?php echo esc_html($buyRate); ?></span>
            </div>
            <div class="bank-rate-card__rate">
              <span class="bank-rate-card__rate-label"><?php echo esc_html($sellLabel); ?></span>
              <span class="bank-rate-card__rate-value bank-rate-card__rate-value--sell"><?php echo esc_html($sellRate); ?></span>
            </div>
          </div>

          <div class="bank-rate-card__actions">
            <?php if ($isDetailsButton) : ?>
              <button class="bank-rate-card__cta" type="button" data-bank-offices-trigger>
                <?php echo esc_html($ctaText); ?>
              </button>
            <?php else : ?>
              <a class="bank-rate-card__cta" href="<?php echo esc_url($ctaUrl); ?>">
                <?php echo esc_html($ctaText); ?>
              </a>
            <?php endif; ?>
            <button
              type="button"
              class="bank-rate-card__map"
              data-bank-map-trigger
              aria-label="<?php esc_attr_e('Показать на карте', 'theme'); ?>">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="40" height="40" rx="20" fill="#F2F3F7" />
                <path d="M20.5 15.0244C18.5836 15.0244 17.0244 16.5836 17.0244 18.5C17.0244 20.4165 18.5836 21.9756 20.5 21.9756C22.4165 21.9756 23.9756 20.4165 23.9756 18.5C23.9756 16.5836 22.4165 15.0244 20.5 15.0244ZM20.5 20.5122C19.3905 20.5122 18.4878 19.6095 18.4878 18.5C18.4878 17.3905 19.3905 16.4878 20.5 16.4878C21.6095 16.4878 22.5122 17.3905 22.5122 18.5C22.5122 19.6095 21.6095 20.5122 20.5 20.5122Z" fill="#22284B" />
                <path d="M20.5 11C16.3645 11 13 14.3645 13 18.5V18.7073C13 20.7988 14.1991 23.2361 16.5641 25.9513C18.2786 27.9197 19.9691 29.2922 20.0402 29.3496L20.5 29.7212L20.9598 29.3497C21.031 29.2922 22.7215 27.9197 24.4359 25.9513C26.8009 23.2361 28 20.7989 28 18.7074V18.5C28 14.3645 24.6355 11 20.5 11ZM26.5366 18.7074C26.5366 22.2419 21.9824 26.5205 20.5 27.8194C19.0172 26.5201 14.4634 22.2416 14.4634 18.7074V18.5C14.4634 15.1715 17.1714 12.4634 20.5 12.4634C23.8286 12.4634 26.5366 15.1715 26.5366 18.5V18.7074Z" fill="#22284B" />
              </svg>
            </button>
          </div>
        </div>

        <p class="bank-rate-card__disclaimer"><?php echo esc_html($disclaimer); ?></p>
        </div>
        <?php if ($hasBadges) : ?>
      </div>
    <?php endif; ?>
</article>
