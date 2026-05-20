<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();
?>
<main id="primary" class="site-main site-main--front-page">
  <?php Template::part('template-parts/front-page/hero'); ?>
  <?php Template::part('template-parts/banks/banks-list-skeleton'); ?>
  <?php Template::part('template-parts/front-page/exchange-links'); ?>
  <?php Template::part('template-parts/front-page/reviews'); ?>
  <?php Template::part('template-parts/front-page/faq'); ?>
  <?php Template::part('template-parts/front-page/news-preview'); ?>
  <?php Template::part('template-parts/front-page/front-intro'); ?>
  <?php Template::part('template-parts/front-page/other-currencies'); ?>

</main>
<?php
get_footer();
