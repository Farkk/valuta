<?php

declare(strict_types=1);

use Theme\Helpers\HeaderMenuFallback;
use Theme\Cities\CityManager;

defined('ABSPATH') || exit;

$home_url = home_url('/');
$site_name = get_bloginfo('name', 'display');
$current_city = CityManager::getCurrentCity();
$nav_label = sprintf(
    /* translators: %s: site title */
    __('%s — основная навигация', 'theme'),
    $site_name
);
$mobile_menu_label = sprintf(
    /* translators: %s: site title */
    __('%s — меню', 'theme'),
    $site_name
);
$book_url = home_url('/#search-result');
$book_label = __('Забронировать валюту', 'theme');
$city_pin_svg = '<svg width="11" height="13" viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M5.5 0C2.46735 0 0 2.45405 0 5.47036C0 8.85394 3.79619 11.9505 4.95969 12.8194C5.12152 12.9398 5.31054 13 5.5 13C5.68946 13 5.87892 12.9398 6.04031 12.819C7.20381 11.9505 11 8.85394 11 5.47036C11 2.45405 8.53265 0 5.5 0ZM5.5 7.74033C4.19353 7.74033 3.13133 6.68342 3.13133 5.38399C3.13133 4.08455 4.19353 3.02808 5.5 3.02808C6.80647 3.02808 7.86867 4.08499 7.86867 5.38399C7.86867 6.68298 6.80647 7.74033 5.5 7.74033Z" fill="#E30611" /></svg>';
$burger_svg = '<svg width="26" height="14" viewBox="0 0 26 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M25.1873 2.47617H0.81249C0.363976 2.47617 1.71661e-05 1.92145 1.71661e-05 1.23809C1.71661e-05 0.554618 0.364044 0 0.81249 0H25.1873C25.6358 0 25.9998 0.554721 25.9998 1.23809C25.9998 1.92145 25.6357 2.47617 25.1873 2.47617Z" fill="#22284B" /><path d="M0.812735 5.68753H25.1875C25.636 5.68753 26 6.24225 26 6.92562C26 7.60898 25.636 8.1637 25.1875 8.1637H0.812735C0.364222 8.1637 0.000263214 7.60898 0.000263214 6.92562C0.000263214 6.24225 0.364222 5.68753 0.812735 5.68753Z" fill="#22284B" /><path d="M0.812664 11.375H17.0625C17.511 11.375 17.875 11.9297 17.875 12.6131C17.875 13.2966 17.511 13.8512 17.0625 13.8512H0.812664C0.364149 13.8512 0.000190735 13.2965 0.000190735 12.6131C0.000123978 11.9296 0.364149 11.375 0.812664 11.375Z" fill="#22284B" /></svg>';
$close_svg = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M1.32714 13.9927C0.983897 14.0129 0.646331 13.8974 0.385624 13.6707C-0.128541 13.1475 -0.128541 12.3024 0.385624 11.7792L11.6442 0.389617C12.1789 -0.116614 13.0181 -0.0884728 13.5185 0.452526C13.971 0.941748 13.9974 1.69369 13.5802 2.21411L2.25538 13.6707C1.99803 13.8942 1.66588 14.0094 1.32714 13.9927Z" fill="#22284B" /><path d="M12.5724 13.9928C12.2245 13.9913 11.8911 13.8516 11.6441 13.6037L0.385517 2.21414C-0.0908308 1.6514 -0.0260689 0.804518 0.530194 0.322585C1.02667 -0.107528 1.75888 -0.107528 2.25532 0.322585L13.5802 11.7121C14.1148 12.2185 14.1425 13.0675 13.6419 13.6083C13.622 13.6298 13.6015 13.6506 13.5802 13.6708C13.3029 13.9147 12.9379 14.0313 12.5724 13.9928Z" fill="#22284B" /></svg>';

$render_header_menu = static function (string $menu_id, string $menu_class, string $nav_class) use ($nav_label): void {
    ?>
    <nav class="<?php echo esc_attr($nav_class); ?>" aria-label="<?php echo esc_attr($nav_label); ?>">
      <?php
      wp_nav_menu(
          [
              'theme_location' => 'header',
              'container'      => false,
              'menu_class'     => $menu_class,
              'menu_id'        => $menu_id,
              'fallback_cb'    => [HeaderMenuFallback::class, 'render'],
              'depth'          => 1,
          ]
      );
      ?>
    </nav>
    <?php
};
?>
<header class="header">
  <div class="container">
    <div class="header__inner">
      <div class="header__info">
        <a href="<?php echo esc_url($home_url); ?>" class="header__logo" rel="home">
          <img
            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>"
            width="162"
            height="24"
            alt="<?php echo esc_attr($site_name); ?>">
        </a>
        <button
            type="button"
            class="btn-cities header__city"
            data-modal-target="city-modal"
            aria-haspopup="dialog"
        >
          <?php echo $city_pin_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <span class="header__city-name"><?php echo esc_html($current_city['city_name']); ?></span>
        </button>
      </div>

      <div class="header__actions">
        <?php $render_header_menu('header-menu', 'header__menu', 'header__nav'); ?>

        <?php
        get_template_part(
            'template-parts/components/button',
            'primary',
            [
                'label' => $book_label,
                'url'   => $book_url,
            ]
        );
        ?>
      </div>

      <button
          type="button"
          class="header__burger header-mobile-toggle"
          aria-label="<?php esc_attr_e('Открыть меню', 'theme'); ?>"
          aria-expanded="false"
          aria-controls="header-mobile-menu"
          data-header-menu-toggle
      >
        <span class="header-mobile-toggle__icon header-mobile-toggle__icon--burger">
          <?php echo $burger_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </span>
        <span class="header-mobile-toggle__icon header-mobile-toggle__icon--close">
          <?php echo $close_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </span>
      </button>
    </div>
  </div>
</header>

<div
    id="header-mobile-menu"
    class="mobile-menu"
    data-header-mobile-menu
    role="dialog"
    aria-modal="true"
    aria-label="<?php echo esc_attr($mobile_menu_label); ?>"
    aria-hidden="true"
>
  <div class="mobile-menu__inner">
    <div class="mobile-menu__header">
      <a href="<?php echo esc_url($home_url); ?>" class="mobile-menu__logo" rel="home">
        <img
          src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>"
          width="162"
          height="24"
          alt="<?php echo esc_attr($site_name); ?>">
      </a>
      <button
          type="button"
          class="mobile-menu__close"
          aria-label="<?php esc_attr_e('Закрыть меню', 'theme'); ?>"
          data-header-menu-close
      >
        <?php echo $close_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </button>
    </div>

    <div class="mobile-menu__content">
      <?php $render_header_menu('header-mobile-menu-list', 'mobile-menu__list', 'mobile-menu__nav'); ?>

      <div class="mobile-menu__actions">
        <button
            type="button"
            class="mobile-menu__city btn-cities"
            data-modal-target="city-modal"
            aria-haspopup="dialog"
        >
          <?php echo $city_pin_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <span><?php echo esc_html($current_city['city_name']); ?></span>
        </button>
        <a class="mobile-menu__book btn btn--primary" href="<?php echo esc_url($book_url); ?>">
          <?php echo esc_html($book_label); ?>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="mobile-menu-overlay" data-header-mobile-menu-overlay aria-hidden="true"></div>
