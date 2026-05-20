<?php

declare(strict_types=1);

use Theme\Helpers\Template;

defined('ABSPATH') || exit;

$site_name = get_bloginfo('name', 'display');
$home_url = home_url('/');
$privacy_url = get_privacy_policy_url();

if ($privacy_url === '') {
  foreach (['politika-konfidentsialnosti', 'politika-konfidencialnosti', 'privacy-policy'] as $slug) {
    $pages = get_posts(
      [
        'name'           => $slug,
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
      ]
    );
    if ($pages !== []) {
      $privacy_url = get_permalink($pages[0]);
      break;
    }
  }
}

$footer_nav_label = __('Нижний колонтитул', 'theme');
?>
<footer class="site-footer">
  <div class="container site-footer__inner">
    <a class="site-footer__logo" href="<?php echo esc_url($home_url); ?>" rel="home">
      <img
        src="<?php echo esc_url(Template::asset('assets/images/logo_footer.svg')); ?>"
        width="217"
        height="31"
        alt="<?php echo esc_attr($site_name); ?>"
        loading="lazy"
        decoding="async">
    </a>
    <nav class="site-footer__nav" aria-label="<?php echo esc_attr($footer_nav_label); ?>">
      <a
        class="site-footer__link"
        href="<?php echo $privacy_url !== '' ? esc_url($privacy_url) : '#'; ?>">
        <?php esc_html_e('Политика конфиденциальности', 'theme'); ?>
      </a>
    </nav>
  </div>
</footer>
