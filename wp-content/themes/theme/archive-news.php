<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();

Template::part('template-parts/news/archive', null, [
    'current_tag' => '',
]);

get_footer();
