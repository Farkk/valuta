<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\About;
use Theme\Helpers\Template;

$postId = get_the_ID();
$heroImageUrl = About::getHeroImageUrl($postId);
$partners = About::getPartners();
$benefits = About::getBenefits();
$bookingUrl = home_url('/#search-result');
?>
<section class="about-page__section">
  <div class="container">
    <?php Template::breadcrumbs('about-page__breadcrumbs'); ?>

    <div class="about-page__hero">
      <div class="about-page__hero-content">
        <h1 class="about-page__title">
          <span class="about-page__title-brand">Valuta.info</span>
          <?php esc_html_e(' — сервис для поиска наиболее выгодных курсов обмена валют в банках', 'theme'); ?>
        </h1>

        <?php
        Template::part('template-parts/components/button', 'primary', [
            'label'       => __('Забронировать валюту', 'theme'),
            'url'         => $bookingUrl,
            'extra_class' => 'about-page__cta',
        ]);
        ?>
      </div>

      <div class="about-page__hero-media<?php echo $heroImageUrl === '' ? ' about-page__hero-media--empty' : ''; ?>">
        <?php if ($heroImageUrl !== '') : ?>
          <img
            class="about-page__hero-image"
            src="<?php echo esc_url($heroImageUrl); ?>"
            alt=""
            width="663"
            height="323"
            loading="lazy"
            decoding="async"
          >
        <?php endif; ?>
      </div>
    </div>

    <hr class="about-page__divider">

    <?php if ($partners !== []) : ?>
      <section class="about-page__partners" aria-labelledby="about-page-partners-heading">
        <h2 id="about-page-partners-heading" class="about-page__partners-title">
          <?php esc_html_e('Наши партнёры', 'theme'); ?>
        </h2>

        <ul class="about-page__partners-list">
          <?php foreach ($partners as $partner) : ?>
            <li class="about-page__partners-item">
              <?php if ($partner['url'] !== '') : ?>
                <a class="about-page__partner" href="<?php echo esc_url($partner['url']); ?>" target="_blank" rel="noopener noreferrer">
              <?php else : ?>
                <span class="about-page__partner">
              <?php endif; ?>
                  <span class="about-page__partner-logo-wrap">
                    <img
                      class="about-page__partner-logo"
                      src="<?php echo esc_url($partner['logo']); ?>"
                      alt=""
                      width="40"
                      height="40"
                      loading="lazy"
                      decoding="async"
                    >
                  </span>
                  <span class="about-page__partner-name"><?php echo esc_html($partner['name']); ?></span>
              <?php if ($partner['url'] !== '') : ?>
                </a>
              <?php else : ?>
                </span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>

      <hr class="about-page__divider">
    <?php endif; ?>

    <section class="about-page__benefits" aria-label="<?php esc_attr_e('Преимущества сервиса', 'theme'); ?>">
      <ul class="about-page__benefits-list">
        <?php foreach ($benefits as $benefit) : ?>
          <li class="about-page__benefit">
            <span class="about-page__benefit-icon" aria-hidden="true">
              <?php
              $iconPath = get_template_directory() . '/assets/images/about/' . $benefit['icon'] . '.svg';

              if (is_readable($iconPath)) {
                  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                  echo file_get_contents($iconPath);
              }
              ?>
            </span>
            <div class="about-page__benefit-body">
              <h3 class="about-page__benefit-title"><?php echo esc_html($benefit['title']); ?></h3>
              <p class="about-page__benefit-text"><?php echo esc_html($benefit['text']); ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </div>
</section>
