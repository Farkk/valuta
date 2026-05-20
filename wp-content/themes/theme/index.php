<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Helpers\Template;

get_header();
?>
<main id="primary" class="site-main">
    <section class="content-section">
        <div class="container flow">
            <?php Template::breadcrumbs('entry__breadcrumbs'); ?>

            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
                        <header class="entry__header">
                            <h1 class="entry__title"><?php the_title(); ?></h1>
                        </header>

                        <div class="entry__content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <article class="entry">
                    <h1 class="entry__title"><?php esc_html_e('Nothing found', 'theme'); ?></h1>
                    <p><?php esc_html_e('Start creating content or adjust the query template.', 'theme'); ?></p>
                </article>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();

