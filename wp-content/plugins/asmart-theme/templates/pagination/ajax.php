<?php
/**
 * Pagination Template - AJAX
 *
 * AJAX пагинация без перезагрузки страницы.
 *
 * @var \Asmart\Components\Pagination\PaginationProps $props
 */

declare(strict_types=1);

use Asmart\Components\Pagination\PaginationProps;

if (!isset($props) || !($props instanceof PaginationProps)) {
    return;
}

$visiblePages = $props->getVisiblePages();
$dataAttrs = sprintf(
    'data-pagination="ajax" data-ajax-url="%s" data-ajax-action="%s" data-container-id="%s" data-current-page="%d" data-total-pages="%d"',
    esc_attr($props->ajaxUrl),
    esc_attr($props->ajaxAction),
    esc_attr($props->containerId),
    esc_attr((string) $props->currentPage),
    esc_attr((string) $props->totalPages),
);
?>

<nav class="asmart-pagination asmart-pagination--ajax" aria-label="<?php esc_attr_e('Pagination', 'asmart'); ?>" <?php echo $dataAttrs; ?>>
    <ul class="asmart-pagination__list">
        <?php if ($props->showFirstLast && $props->currentPage > 1): ?>
            <li class="asmart-pagination__item">
                <button type="button"
                        class="asmart-pagination__link asmart-pagination__link--ajax"
                        data-page="1"
                        aria-label="<?php esc_attr_e('First page', 'asmart'); ?>">
                    &laquo;
                </button>
            </li>
        <?php endif; ?>

        <?php if ($props->showPrevNext && $props->hasPrev()): ?>
            <li class="asmart-pagination__item">
                <button type="button"
                        class="asmart-pagination__link asmart-pagination__link--ajax"
                        data-page="<?php echo esc_attr((string) ($props->currentPage - 1)); ?>"
                        aria-label="<?php esc_attr_e('Previous page', 'asmart'); ?>">
                    &lsaquo;
                </button>
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
                    <button type="button"
                            class="asmart-pagination__link asmart-pagination__link--ajax"
                            data-page="<?php echo esc_attr((string) $page); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('Page %d', 'asmart'), $page)); ?>">
                        <?php echo esc_html($page); ?>
                    </button>
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
                <button type="button"
                        class="asmart-pagination__link asmart-pagination__link--ajax"
                        data-page="<?php echo esc_attr((string) ($props->currentPage + 1)); ?>"
                        aria-label="<?php esc_attr_e('Next page', 'asmart'); ?>">
                    &rsaquo;
                </button>
            </li>
        <?php endif; ?>

        <?php if ($props->showFirstLast && $props->currentPage < $props->totalPages): ?>
            <li class="asmart-pagination__item">
                <button type="button"
                        class="asmart-pagination__link asmart-pagination__link--ajax"
                        data-page="<?php echo esc_attr((string) $props->totalPages); ?>"
                        aria-label="<?php esc_attr_e('Last page', 'asmart'); ?>">
                    &raquo;
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Индикатор загрузки -->
    <div class="asmart-pagination__loader" style="display: none;">
        <span class="asmart-pagination__spinner"></span>
    </div>
</nav>
