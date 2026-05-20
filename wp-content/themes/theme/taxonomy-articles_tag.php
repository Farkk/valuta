<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();

$term = get_queried_object();
$currentTag = $term instanceof WP_Term ? $term->slug : '';

Template::part('template-parts/content/archive', null, [
    'content_type' => 'articles',
    'current_tag' => $currentTag,
]);

get_footer();
