<?php

declare(strict_types=1);

namespace Asmart\Components\Pagination;

use Asmart\Support\BaseProps;

/**
 * Props пагинации
 *
 * Иммутабельный DTO для компонента Pagination.
 */
readonly class PaginationProps extends BaseProps
{
  /**
   * @param int $currentPage Номер текущей страницы
   * @param int $totalPages Общее количество страниц
   * @param string $baseUrl Базовый URL для ссылок пагинации
   * @param PaginationType $type Тип пагинации (default, ajax, load-more, infinite)
   * @param bool $showFirstLast Показывать ссылки на первую/последнюю страницу
   * @param bool $showPrevNext Показывать ссылки предыдущая/следующая
   * @param int $maxVisible Максимальное количество видимых ссылок на страницы
   * @param string $format Формат URL (%d будет заменен на номер страницы)
   * @param ?string $ajaxUrl URL для AJAX запросов (для ajax, load-more, infinite)
   * @param ?string $ajaxAction WordPress AJAX action
   * @param ?string $containerId ID контейнера для вставки контента
   * @param ?string $loadMoreText Текст кнопки "Загрузить ещё"
   * @param int $infiniteOffset Отступ для infinite scroll (в пикселях от низа)
   */
  public function __construct(
    public int $currentPage,
    public int $totalPages,
    public string $baseUrl,
    public PaginationType $type = PaginationType::Default,
    public bool $showFirstLast = true,
    public bool $showPrevNext = true,
    public int $maxVisible = 7,
    public string $format = '%d/',
    public ?string $ajaxUrl = null,
    public ?string $ajaxAction = null,
    public ?string $containerId = null,
    public ?string $loadMoreText = null,
    public int $infiniteOffset = 300,
  ) {
    $this->validate();
  }

  /**
   * Валидация props
   */
  protected function validate(): void
  {
    if ($this->currentPage < 1) {
      throw new \InvalidArgumentException('Current page must be >= 1');
    }

    if ($this->totalPages < 1) {
      throw new \InvalidArgumentException('Total pages must be >= 1');
    }

    if ($this->currentPage > $this->totalPages) {
      throw new \InvalidArgumentException(
        'Current page cannot exceed total pages',
      );
    }

    if ($this->maxVisible < 3) {
      throw new \InvalidArgumentException('Max visible pages must be >= 3');
    }

    // Валидация для AJAX типов
    if (
      in_array($this->type, [
        PaginationType::Ajax,
        PaginationType::LoadMore,
        PaginationType::Infinite,
      ])
    ) {
      if (empty($this->ajaxUrl)) {
        throw new \InvalidArgumentException(
          'Ajax URL is required for AJAX pagination types',
        );
      }

      if (empty($this->ajaxAction)) {
        throw new \InvalidArgumentException(
          'Ajax action is required for AJAX pagination types',
        );
      }

      if (empty($this->containerId)) {
        throw new \InvalidArgumentException(
          'Container ID is required for AJAX pagination types',
        );
      }
    }

    if ($this->infiniteOffset < 0) {
      throw new \InvalidArgumentException(
        'Infinite scroll offset must be >= 0',
      );
    }
  }

  /**
   * Получить URL для конкретной страницы
   */
  public function getPageUrl(int $page): string
  {
    return $this->baseUrl . sprintf($this->format, $page);
  }

  /**
   * Получить номера видимых страниц
   *
   * @return array<int>
   */
  public function getVisiblePages(): array
  {
    $pages = [];
    $half = (int) floor($this->maxVisible / 2);

    $start = max(1, $this->currentPage - $half);
    $end = min($this->totalPages, $this->currentPage + $half);

    // Корректировка если на краях
    if ($this->currentPage <= $half) {
      $end = min($this->totalPages, $this->maxVisible);
    } elseif ($this->currentPage >= $this->totalPages - $half) {
      $start = max(1, $this->totalPages - $this->maxVisible + 1);
    }

    for ($i = $start; $i <= $end; $i++) {
      $pages[] = $i;
    }

    return $pages;
  }

  /**
   * Проверка наличия предыдущей страницы
   */
  public function hasPrev(): bool
  {
    return $this->currentPage > 1;
  }

  /**
   * Проверка наличия следующей страницы
   */
  public function hasNext(): bool
  {
    return $this->currentPage < $this->totalPages;
  }
}
