<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();

while (have_posts()) {
    the_post();
    Template::part('template-parts/content/single');
}

get_footer();
