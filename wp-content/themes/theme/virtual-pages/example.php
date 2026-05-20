<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

get_header();
?>
<main id="primary" class="site-main">
    <section class="content-section">
        <div class="container flow">
            <h1><?php esc_html_e('Virtual page example', 'theme'); ?></h1>
            <p><?php esc_html_e('This template is resolved by the virtual page manager without a real WordPress page entry.', 'theme'); ?></p>
        </div>
    </section>
</main>
<?php
get_footer();

