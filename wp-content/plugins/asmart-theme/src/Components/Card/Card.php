<?php

declare(strict_types=1);

namespace Asmart\Components\Card;

use Asmart\Components\BaseComponent;

/**
 * Компонент Card
 *
 * Универсальный компонент карточки для отображения контента.
 * Может использоваться для постов, товаров, и любого другого контента.
 *
 */
final class Card extends BaseComponent
{
  protected static string $componentName = 'card';

  /**
   * Рендеринг компонента карточки
   *
   * @param string $title Заголовок карточки
   * @param CardLayout $layout Тип отображения (grid или list)
   * @param string|null $excerpt Краткое описание
   * @param string|null $imageUrl URL изображения
   * @param string|null $imageAlt Alt текст для изображения
   * @param bool $lazyLoadImage Использовать ли lazy loading для изображения
   * @param string|null $url Ссылка на полный контент
   * @param array<string, string> $meta Массив метаданных
   * @param string|null $buttonHtml HTML код кнопки
   * @param string|null $customClass Пользовательский CSS класс
   * @param array<string, string> $attributes Дополнительные HTML атрибуты
   * @param bool $hoverEffect Включить ли эффект при наведении
   * @return void
   */
  public static function render(
    string $title,
    CardLayout $layout = CardLayout::Grid,
    ?string $excerpt = null,
    ?string $imageUrl = null,
    ?string $imageAlt = null,
    bool $lazyLoadImage = true,
    ?string $url = null,
    array $meta = [],
    ?string $buttonHtml = null,
    ?string $customClass = null,
    array $attributes = [],
    bool $hoverEffect = true,
  ): void {
    $props = new CardProps(
      title: $title,
      layout: $layout,
      excerpt: $excerpt,
      imageUrl: $imageUrl,
      imageAlt: $imageAlt,
      lazyLoadImage: $lazyLoadImage,
      url: $url,
      meta: $meta,
      buttonHtml: $buttonHtml,
      customClass: $customClass,
      attributes: $attributes,
      hoverEffect: $hoverEffect,
    );

    // Выбор шаблона в зависимости от layout
    $template = match ($layout) {
      CardLayout::List => 'list',
      default => 'grid',
    };

    static::renderTemplate($template, static::propsToArray($props));
  }

  /**
   * Получить отрендеренную карточку в виде строки
   *
   * Полезно для использования внутри других компонентов или AJAX ответов.
   *
   * @param string $title Заголовок карточки
   * @param CardLayout $layout Тип отображения
   * @param string|null $excerpt Краткое описание
   * @param string|null $imageUrl URL изображения
   * @param string|null $imageAlt Alt текст для изображения
   * @param bool $lazyLoadImage Использовать ли lazy loading
   * @param string|null $url Ссылка на полный контент
   * @param array<string, string> $meta Массив метаданных
   * @param string|null $buttonHtml HTML код кнопки
   * @param string|null $customClass Пользовательский CSS класс
   * @param array<string, string> $attributes Дополнительные HTML атрибуты
   * @param bool $hoverEffect Включить ли эффект при наведении
   * @return string
   */
  public static function get(
    string $title,
    CardLayout $layout = CardLayout::Grid,
    ?string $excerpt = null,
    ?string $imageUrl = null,
    ?string $imageAlt = null,
    bool $lazyLoadImage = true,
    ?string $url = null,
    array $meta = [],
    ?string $buttonHtml = null,
    ?string $customClass = null,
    array $attributes = [],
    bool $hoverEffect = true,
  ): string {
    $props = new CardProps(
      title: $title,
      layout: $layout,
      excerpt: $excerpt,
      imageUrl: $imageUrl,
      imageAlt: $imageAlt,
      lazyLoadImage: $lazyLoadImage,
      url: $url,
      meta: $meta,
      buttonHtml: $buttonHtml,
      customClass: $customClass,
      attributes: $attributes,
      hoverEffect: $hoverEffect,
    );

    $template = match ($layout) {
      CardLayout::List => 'list',
      default => 'grid',
    };

    return static::getTemplate($template, static::propsToArray($props));
  }
}
