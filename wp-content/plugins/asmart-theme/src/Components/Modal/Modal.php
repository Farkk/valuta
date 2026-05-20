<?php

declare(strict_types=1);

namespace Asmart\Components\Modal;

use Asmart\Components\BaseComponent;

/**
 * Компонент Modal
 *
 * Модальный диалог с поддержкой множественных шаблонов.
 *
 */
final class Modal extends BaseComponent
{
  protected static string $componentName = 'modal';

  /**
   * Рендеринг компонента модалки
   *
   * @param string $title Заголовок модалки
   * @param string $content Содержимое модалки (разрешен HTML)
   * @param ModalTemplate $template Тип шаблона модалки
   * @param ModalSize $size Размер модалки
   * @param bool $closeable Можно ли закрыть модалку
   * @param string $id Уникальный ID модалки
   * @param string|null $footer Опциональное содержимое подвала (HTML)
   * @param array<string, string> $attributes Дополнительные HTML атрибуты
   * @return void
   */
  public static function render(
    string $title,
    string $content,
    ModalTemplate $template = ModalTemplate::Default,
    ModalSize $size = ModalSize::Medium,
    bool $closeable = true,
    string $id = '',
    ?string $footer = null,
    array $attributes = [],
  ): void {
    $props = new ModalProps(
      title: $title,
      content: $content,
      template: $template,
      size: $size,
      closeable: $closeable,
      id: $id,
      footer: $footer,
      attributes: $attributes,
    );

    // Рендеринг с использованием шаблона, указанного в props
    static::renderTemplate($template->value, static::propsToArray($props));
  }
}
