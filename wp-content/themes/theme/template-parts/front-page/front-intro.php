<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Homepage;

$exchangeText = Homepage::getExchangeText();
$title = trim((string) ($exchangeText['title'] ?? ''));
$description = trim((string) ($exchangeText['description'] ?? ''));

if ($title === '') {
    $title = __('Где выгодно обменять валюту в России в 2026 году', 'theme');
}

if ($description === '') {
    $description = __('При необходимости узнать в реальном времени выгодный курс обмена валют в банках необязательно их посещение. Наш сайт поможет сэкономить время и найти актуальные сведения по текущим котировкам валют мира.', 'theme');
}
?>
<section class="front-intro" aria-labelledby="front-intro-title">
  <div class="container">
    <div class="front-intro__inner">
      <h2 class="front-intro__title" id="front-intro-title">
        <?php echo esc_html($title); ?>
      </h2>
      <div class="front-intro__body">
        <?php
        echo wp_kses_post(
            str_replace('<p>', '<p class="front-intro__text">', wpautop($description))
            ?:
            '<p class="front-intro__text">' . esc_html($description) . '</p>'
        );
        ?>
      </div>
    </div>
  </div>
</section>
