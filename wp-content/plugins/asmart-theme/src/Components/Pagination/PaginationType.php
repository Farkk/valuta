<?php

declare(strict_types=1);

namespace Asmart\Components\Pagination;

/**
 * Типы пагинации
 *
 * Определяет различные стратегии навигации по контенту.
 */
enum PaginationType: string
{
  /**
   * Обычная пагинация с перезагрузкой страницы
   */
  case Default = 'default';

  /**
   * AJAX пагинация без перезагрузки страницы
   */
  case Ajax = 'ajax';

  /**
   * Кнопка "Загрузить ещё"
   */
  case LoadMore = 'load-more';

  /**
   * Бесконечная прокрутка (Infinite Scroll)
   */
  case Infinite = 'infinite';
}
