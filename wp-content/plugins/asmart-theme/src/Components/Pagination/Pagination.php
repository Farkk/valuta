<?php

declare(strict_types=1);

namespace Asmart\Components\Pagination;

use Asmart\Components\BaseComponent;

/**
 * Компонент Pagination
 *
 * Гибкий компонент пагинации для навигации по контенту.
 *
 */
final class Pagination extends BaseComponent
{
  protected static string $componentName = 'pagination';

  /**
   * Рендеринг компонента пагинации
   *
   * @param int $currentPage Номер текущей страницы
   * @param int $totalPages Общее количество страниц
   * @param string $baseUrl Базовый URL для ссылок пагинации
   * @param PaginationType $type Тип пагинации
   * @param bool $showFirstLast Показывать ссылки на первую/последнюю страницу
   * @param bool $showPrevNext Показывать ссылки предыдущая/следующая
   * @param int $maxVisible Максимальное количество видимых ссылок на страницы
   * @param string $format Формат URL (%d будет заменен на номер страницы)
   * @param ?string $ajaxUrl URL для AJAX запросов
   * @param ?string $ajaxAction WordPress AJAX action
   * @param ?string $containerId ID контейнера для вставки контента
   * @param ?string $loadMoreText Текст кнопки "Загрузить ещё"
   * @param int $infiniteOffset Отступ для infinite scroll (в пикселях)
   * @return void
   */
  public static function render(
    int $currentPage,
    int $totalPages,
    string $baseUrl,
    PaginationType $type = PaginationType::Default,
    bool $showFirstLast = true,
    bool $showPrevNext = true,
    int $maxVisible = 7,
    string $format = '%d/',
    ?string $ajaxUrl = null,
    ?string $ajaxAction = null,
    ?string $containerId = null,
    ?string $loadMoreText = null,
    int $infiniteOffset = 300,
  ): void {
    // Не рендерить, если только одна страница (кроме load-more и infinite)
    if (
      $totalPages <= 1 &&
      !in_array($type, [PaginationType::LoadMore, PaginationType::Infinite])
    ) {
      return;
    }

    $props = new PaginationProps(
      currentPage: $currentPage,
      totalPages: $totalPages,
      baseUrl: $baseUrl,
      type: $type,
      showFirstLast: $showFirstLast,
      showPrevNext: $showPrevNext,
      maxVisible: $maxVisible,
      format: $format,
      ajaxUrl: $ajaxUrl,
      ajaxAction: $ajaxAction,
      containerId: $containerId,
      loadMoreText: $loadMoreText,
      infiniteOffset: $infiniteOffset,
    );

    // Выбор шаблона в зависимости от типа
    $template = match ($type) {
      PaginationType::Ajax => 'ajax',
      PaginationType::LoadMore => 'load-more',
      PaginationType::Infinite => 'infinite',
      default => 'default',
    };

    static::renderTemplate($template, static::propsToArray($props));
  }
}
