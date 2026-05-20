<?php
/**
 * Template Name: Full Width
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();
?>
<main id="primary" class="site-main site-main--full-width">
    <section class="content-section">
        <div class="container flow">
            <?php Template::breadcrumbs('entry__breadcrumbs'); ?>

            <?php
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </section>
</main>
<?php
get_footer();

