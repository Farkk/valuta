<?php
/**
 * Template Name: О проекте
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();

while (have_posts()) :
    the_post();
    ?>
<main id="primary" class="site-main about-page">
    <?php Template::part('template-parts/about/about-page'); ?>
</main>
    <?php
endwhile;

get_footer();
