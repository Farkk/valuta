<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;
use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyRouter;
use Theme\Filters\FilterRegistry;
use Theme\ACF\AcfManager;
use Theme\Helpers\Text;
use Theme\SEO\SeoPageManager;

$currentCity = CityManager::getCurrentCity();
$cityPrepositional = Text::cityPrepositional((string) $currentCity['city_name']);
$cityTrigger = sprintf(
    '<button type="button" class="hero__city-trigger" data-modal-target="city-modal" aria-label="%s">%s</button>',
    esc_attr__('Выбрать город', 'theme'),
    esc_html($cityPrepositional)
);

$defaultTab = FilterRegistry::getDefaultTab();
$currentCurrency = CurrencyRouter::getCurrentCurrency();
$pairData = CurrencyRouter::getCurrencyPairData();
$defaultAmount = $pairData !== null ? (string) $pairData['amount'] : '';

if (SeoPageManager::hasCurrentPage()) {
    $heroTitle = SeoPageManager::getCurrentValue('h1');
    $currentCurrency = SeoPageManager::getCurrentCurrencyCode();

    if (SeoPageManager::getCurrentAmount() > 0) {
        $defaultAmount = (string) SeoPageManager::getCurrentAmount();
    }
} elseif ($pairData !== null) {
    $template = AcfManager::getField('template_title_usd', 'option', '');
    $currencyTitle = (string) $pairData['amount'] . ' ' . strtoupper((string) $pairData['from']);
    $heroTitle = $template !== ''
        ? str_replace(
            ['$city', '$value', '$'],
            [$cityTrigger, strtoupper((string) $pairData['from']), $currencyTitle],
            $template
        )
        : sprintf(
            '%s %s в российских рублях <span>в %s</span>',
            esc_html((string) $pairData['amount']),
            esc_html(strtoupper((string) $pairData['from'])),
            $cityTrigger
        );
} elseif (CurrencyRouter::isCurrencyPage()) {
    $template = AcfManager::getField('template_title_value', 'option', '');
    $heroTitle = $template !== ''
        ? str_replace(['$city', '$'], [$cityTrigger, strtoupper($currentCurrency)], $template)
        : sprintf('Курсы обмена %s в банках <span>в %s</span>', esc_html(strtoupper($currentCurrency)), $cityTrigger);
} else {
    $template = AcfManager::getField('template_title', 'option', '');
    $heroTitle = $template !== ''
        ? str_replace('$', $cityTrigger, $template)
        : sprintf('Найди лучший курс<br />валют <span>в %s</span>', $cityTrigger);
}
?>
<section class="hero">
  <div class="container">
    <div class="hero__inner">
      <div class="hero__content">
        <h1 class="hero__title">
          <?php
          echo wp_kses(
              $heroTitle,
              [
                  'br'   => [],
                  'span' => [],
                  'button' => [
                      'type' => [],
                      'class' => [],
                      'data-modal-target' => [],
                      'aria-label' => [],
                  ],
              ]
          );
          ?>
        </h1>
        <div class="hero__date">
          <?php esc_html_e('По состоянию на: ', 'theme'); ?><span><?= esc_html(wp_date('d.m.Y')) ?></span>
        </div>
        <?php Template::part('template-parts/components/converter', null, [
            'currency' => $currentCurrency,
            'amount' => $defaultAmount,
            'default_tab' => $defaultTab,
        ]); ?>
      </div>
      <img
        class="hero__img"
        src="<?= esc_url(Template::asset('assets/images/hero.png')) ?>"
        alt=""
      >
    </div>
  </div>
</section>
