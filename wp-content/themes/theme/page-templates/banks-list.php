<?php
/**
 * Template Name: Banks list (skeleton)
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();
?>
<main id="primary" class="site-main site-main--banks-list">
    <?php Template::part('template-parts/banks/banks-list-skeleton'); ?>
</main>
<?php
get_footer();
