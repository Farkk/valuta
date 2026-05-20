<?php

declare(strict_types=1);

namespace Asmart\Components\Card;

use Asmart\Support\BaseProps;

/**
 * Props карточки
 *
 * Иммутабельный DTO для компонента Card.
 * Универсальная карточка может использоваться для постов, товаров, и любого контента.
 */
readonly class CardProps extends BaseProps
{
  /**
   * @param string $title Заголовок карточки
   * @param CardLayout $layout Тип отображения (grid или list)
   * @param string|null $excerpt Краткое описание/выдержка
   * @param string|null $imageUrl URL изображения
   * @param string|null $imageAlt Alt текст для изображения
   * @param bool $lazyLoadImage Использовать ли lazy loading для изображения
   * @param string|null $url Ссылка на полный контент
   * @param array<string, string> $meta Массив метаданных (например, дата, автор, категория)
   * @param string|null $buttonHtml HTML код кнопки (можно передать компонент Button)
   * @param string|null $customClass Пользовательский CSS класс
   * @param array<string, string> $attributes Дополнительные HTML атрибуты
   * @param bool $hoverEffect Включить ли эффект при наведении
   */
  public function __construct(
    public string $title,
    public CardLayout $layout = CardLayout::Grid,
    public ?string $excerpt = null,
    public ?string $imageUrl = null,
    public ?string $imageAlt = null,
    public bool $lazyLoadImage = true,
    public ?string $url = null,
    public array $meta = [],
    public ?string $buttonHtml = null,
    public ?string $customClass = null,
    public array $attributes = [],
    public bool $hoverEffect = true,
  ) {
    $this->validate();
  }

  /**
   * Валидация props
   */
  protected function validate(): void
  {
    if (empty(trim($this->title))) {
      throw new \InvalidArgumentException('Card title cannot be empty');
    }

    if ($this->imageUrl !== null && empty(trim($this->imageUrl))) {
      throw new \InvalidArgumentException('Image URL cannot be empty string');
    }
  }

  /**
   * Проверка наличия изображения
   */
  public function hasImage(): bool
  {
    return $this->imageUrl !== null;
  }

  /**
   * Проверка наличия описания
   */
  public function hasExcerpt(): bool
  {
    return $this->excerpt !== null && !empty(trim($this->excerpt));
  }

  /**
   * Проверка наличия метаданных
   */
  public function hasMeta(): bool
  {
    return !empty($this->meta);
  }

  /**
   * Проверка наличия кнопки
   */
  public function hasButton(): bool
  {
    return $this->buttonHtml !== null;
  }

  /**
   * Проверка наличия ссылки
   */
  public function hasUrl(): bool
  {
    return $this->url !== null;
  }

  /**
   * Получить CSS классы карточки
   */
  public function getCssClasses(): string
  {
    $classes = ['asmart-card', "asmart-card--{$this->layout->value}"];

    if ($this->hoverEffect) {
      $classes[] = 'asmart-card--hover';
    }

    if ($this->customClass) {
      $classes[] = $this->customClass;
    }

    return implode(' ', $classes);
  }

  /**
   * Получить атрибуты загрузки изображения
   */
  public function getImageLoadingAttr(): string
  {
    return $this->lazyLoadImage ? 'lazy' : 'eager';
  }
}
