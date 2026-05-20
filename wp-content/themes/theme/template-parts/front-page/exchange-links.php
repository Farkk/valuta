<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\SEO\SeoPageManager;
use Theme\SEO\SeoTemplateRenderer;

$isSeoPage = SeoPageManager::hasCurrentPage();
$currentCurrencyCode = $isSeoPage ? SeoPageManager::getCurrentCurrencyCode() : 'usd';
$currentCurrencyForms = SeoTemplateRenderer::getCurrencyForms($currentCurrencyCode);
$conversionUrl = static function (int $amount) use ($isSeoPage, $currentCurrencyForms): string {
    if ($isSeoPage) {
        return home_url('/' . $amount . '-' . ($currentCurrencyForms['currency_amount_slug'] ?? 'dollarov') . '-v-rublyah/');
    }

    return home_url('/currency/' . $amount . ($currentCurrencyForms['code'] ?? 'usd') . '_rub/');
};

$dollarAmounts = [1, 10, 100, 200, 300, 400, 500, 1000, 2000, 10000, 20000, 25000, 30000, 35000, 40000, 50000];
$dollarLinks = array_map(
    static fn(int $amount): array => [
        'label' => sprintf(__('%d %s в рублях', 'theme'), $amount, $amount === 1 ? 'доллар' : 'долларов'),
        'url' => $conversionUrl($amount),
    ],
    $dollarAmounts
);

$crossLinks = [
    ['label' => 'Турецкая лира к доллару', 'url' => '#'],
    ['label' => 'Рубль к тенге', 'url' => '#'],
    ['label' => 'Доллар к тенге', 'url' => '#'],
    ['label' => 'Рубль к драму', 'url' => '#'],
    ['label' => 'Юань к доллару', 'url' => '#'],
    ['label' => 'Евро к доллару', 'url' => '#'],
    ['label' => 'Гривны к доллару', 'url' => '#'],
    ['label' => 'Дирхам к доллару', 'url' => '#'],
    ['label' => 'Грузинский лари к доллару', 'url' => '#'],
    ['label' => 'Тайский бат к доллару', 'url' => '#'],
];
?>
<section class="exchange-links">
  <div class="container">
    <div class="exchange-links__inner">
      <div class="exchange-links__block">
        <h2 class="exchange-links__title" id="exchange-links-dollar"><?php esc_html_e('Доллары в рублях', 'theme'); ?></h2>
        <ul class="exchange-links__list" aria-labelledby="exchange-links-dollar">
          <?php foreach ($dollarLinks as $label) : ?>
            <li class="exchange-links__item">
              <a class="exchange-links__link" href="<?php echo esc_url((string) $label['url']); ?>"><?php echo esc_html((string) $label['label']); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="exchange-links__block">
        <h2 class="exchange-links__title" id="exchange-links-cross"><?php esc_html_e('Кросс-курсы валют', 'theme'); ?></h2>
        <ul class="exchange-links__list" aria-labelledby="exchange-links-cross">
          <?php foreach ($crossLinks as $label) : ?>
            <li class="exchange-links__item">
              <a class="exchange-links__link" href="<?php echo esc_url((string) $label['url']); ?>"><?php echo esc_html((string) $label['label']); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
