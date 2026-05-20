<?php
/**
 * Pagination Template - Default
 *
 * @var \Asmart\Components\Pagination\PaginationProps $props
 */

declare(strict_types=1);

use Asmart\Components\Pagination\PaginationProps;

if (!isset($props) || !($props instanceof PaginationProps)) {
    return;
}

$visiblePages = $props->getVisiblePages();
?>

<nav class="asmart-pagination" aria-label="<?php esc_attr_e('Pagination', 'asmart'); ?>">
    <ul class="asmart-pagination__list">
        <?php if ($props->showFirstLast && $props->currentPage > 1): ?>
            <li class="asmart-pagination__item">
                <a href="<?php echo esc_url($props->getPageUrl(1)); ?>"
                   class="asmart-pagination__link"
                   aria-label="<?php esc_attr_e('First page', 'asmart'); ?>">
                    &laquo;
                </a>
            </li>
        <?php endif; ?>

        <?php if ($props->showPrevNext && $props->hasPrev()): ?>
            <li class="asmart-pagination__item">
                <a href="<?php echo esc_url($props->getPageUrl($props->currentPage - 1)); ?>"
                   class="asmart-pagination__link"
                   aria-label="<?php esc_attr_e('Previous page', 'asmart'); ?>">
                    &lsaquo;
                </a>
            </li>
        <?php endif; ?>

        <?php if ($visiblePages[0] > 1): ?>
            <li class="asmart-pagination__item asmart-pagination__item--ellipsis">
                <span class="asmart-pagination__ellipsis">...</span>
            </li>
        <?php endif; ?>

        <?php foreach ($visiblePages as $page): ?>
            <li class="asmart-pagination__item">
                <?php if ($page === $props->currentPage): ?>
                    <span class="asmart-pagination__link asmart-pagination__link--active" aria-current="page">
                        <?php echo esc_html($page); ?>
                    </span>
                <?php else: ?>
                    <a href="<?php echo esc_url($props->getPageUrl($page)); ?>"
                       class="asmart-pagination__link"
                       aria-label="<?php echo esc_attr(sprintf(__('Page %d', 'asmart'), $page)); ?>">
                        <?php echo esc_html($page); ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

        <?php if (end($visiblePages) < $props->totalPages): ?>
            <li class="asmart-pagination__item asmart-pagination__item--ellipsis">
                <span class="asmart-pagination__ellipsis">...</span>
            </li>
        <?php endif; ?>

        <?php if ($props->showPrevNext && $props->hasNext()): ?>
            <li class="asmart-pagination__item">
                <a href="<?php echo esc_url($props->getPageUrl($props->currentPage + 1)); ?>"
                   class="asmart-pagination__link"
                   aria-label="<?php esc_attr_e('Next page', 'asmart'); ?>">
                    &rsaquo;
                </a>
            </li>
        <?php endif; ?>

        <?php if ($props->showFirstLast && $props->currentPage < $props->totalPages): ?>
            <li class="asmart-pagination__item">
                <a href="<?php echo esc_url($props->getPageUrl($props->totalPages)); ?>"
                   class="asmart-pagination__link"
                   aria-label="<?php esc_attr_e('Last page', 'asmart'); ?>">
                    &raquo;
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
