<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Theme\Filters\FilterRegistry;
use Theme\Helpers\Template;

get_header();
?>
<main id="primary" class="site-main bank-single" data-bank-default-tab="<?php echo esc_attr(FilterRegistry::getDefaultTab()); ?>">
  <?php Template::part('template-parts/banks/bank-single'); ?>
</main>
<?php
get_footer();
