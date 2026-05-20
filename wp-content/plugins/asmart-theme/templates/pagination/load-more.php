<?php

/**
 * Pagination Template - Load More
 *
 * Кнопка "Загрузить ещё" для подгрузки контента.
 *
 * @var \Asmart\Components\Pagination\PaginationProps $props
 */

declare(strict_types=1);

use Asmart\Components\Pagination\PaginationProps;

if (!isset($props) || !($props instanceof PaginationProps)) {
  return;
}

// Не показываем кнопку, если достигнута последняя страница
if (!$props->hasNext()) {
  return;
}

$buttonText = $props->loadMoreText ?? __('Load More', 'asmart');
$dataAttrs = sprintf(
  'data-pagination="load-more" data-ajax-url="%s" data-ajax-action="%s" data-container-id="%s" data-current-page="%d" data-total-pages="%d"',
  esc_attr($props->ajaxUrl),
  esc_attr($props->ajaxAction),
  esc_attr($props->containerId),
  esc_attr((string) $props->currentPage),
  esc_attr((string) $props->totalPages),
);
?>

<div class="asmart-pagination asmart-pagination--load-more" <?php echo $dataAttrs; ?>>
  <button type="button" class="asmart-pagination__load-more-btn">
    <span class="asmart-pagination__load-more-text"><?php echo esc_html($buttonText); ?></span>
    <span class="asmart-pagination__spinner" style="display: none;"></span>
  </button>
</div>
