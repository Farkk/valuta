<?php
/**
 * Pagination Template - Infinite Scroll
 *
 * Бесконечная прокрутка с автоматической подгрузкой.
 *
 * @var \Asmart\Components\Pagination\PaginationProps $props
 */

declare(strict_types=1);

use Asmart\Components\Pagination\PaginationProps;

if (!isset($props) || !($props instanceof PaginationProps)) {
    return;
}

// Не показываем индикатор, если достигнута последняя страница
if (!$props->hasNext()) {
    return;
}

$dataAttrs = sprintf(
    'data-pagination="infinite" data-ajax-url="%s" data-ajax-action="%s" data-container-id="%s" data-current-page="%d" data-total-pages="%d" data-infinite-offset="%d"',
    esc_attr($props->ajaxUrl),
    esc_attr($props->ajaxAction),
    esc_attr($props->containerId),
    esc_attr((string) $props->currentPage),
    esc_attr((string) $props->totalPages),
    esc_attr((string) $props->infiniteOffset),
);
?>

<div class="asmart-pagination asmart-pagination--infinite" <?php echo $dataAttrs; ?>>
    <!-- Триггер для Intersection Observer -->
    <div class="asmart-pagination__infinite-trigger"></div>

    <!-- Индикатор загрузки -->
    <div class="asmart-pagination__infinite-loader" style="display: none;">
        <span class="asmart-pagination__spinner"></span>
        <span class="asmart-pagination__loading-text">
            <?php esc_html_e('Loading more...', 'asmart'); ?>
        </span>
    </div>

    <!-- Сообщение об окончании контента (показывается после последней загрузки) -->
    <div class="asmart-pagination__infinite-end" style="display: none;">
        <?php esc_html_e('All items loaded', 'asmart'); ?>
    </div>
</div>
