<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyManager;
use Theme\Helpers\Homepage;
use Theme\Helpers\Text;

$flagUrl = THEME_URI . '/assets/images/other-currency-flag-placeholder.svg';
$currentCity = CityManager::getCurrentCity();
$cityPrepositional = Text::cityPrepositional((string) ($currentCity['city_name'] ?? ''));
$currencies = array_values(array_filter(
    CurrencyManager::getCurrencies(),
    static fn(array $currency): bool => ($currency['code'] ?? '') !== 'rub'
));
?>
<section class="other-currencies" aria-labelledby="other-currencies-heading">
  <div class="container">
    <div class="other-currencies__inner">
      <h2 id="other-currencies-heading" class="other-currencies__title">
        <?php esc_html_e('Курсы других валют', 'theme'); ?>
        <span class="other-currencies__title-accent"><?php echo esc_html('в ' . $cityPrepositional); ?></span>
      </h2>
      <div class="other-currencies__panel">
        <ul class="other-currencies__list">
          <?php foreach ($currencies as $row) : ?>
            <li class="other-currencies__item">
              <a class="other-currencies__link" href="<?php echo esc_url(Homepage::getCurrencyUrl((string) ($row['code'] ?? 'usd'), $currentCity)); ?>">
                <img
                  class="other-currencies__flag"
                  src="<?php echo esc_url($flagUrl); ?>"
                  width="30"
                  height="30"
                  alt=""
                  decoding="async"
                  loading="lazy"
                />
                <span class="other-currencies__label">
                  <span class="other-currencies__code"><?php echo esc_html(strtoupper((string) ($row['code'] ?? ''))); ?></span><span class="other-currencies__sep"> / </span><span class="other-currencies__name"><?php echo esc_html((string) ($row['name_ru'] ?? '')); ?></span>
                </span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="other-currencies__action">
          <a class="other-currencies__cta" href="<?php echo esc_url(Homepage::getCurrencyUrl((string) (($currencies[0]['code'] ?? 'usd')), $currentCity)); ?>">
            <?php esc_html_e('Все курсы', 'theme'); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
