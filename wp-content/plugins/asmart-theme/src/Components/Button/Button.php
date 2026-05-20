<?php

declare(strict_types=1);

namespace Asmart\Components\Button;

use Asmart\Components\BaseComponent;

/**
 * Компонент Button
 *
 *
 */
final class Button extends BaseComponent
{
  protected static string $componentName = 'button';

  /**
   * Рендеринг компонента кнопки
   *
   * @param string $label Текстовая метка кнопки
   * @param ButtonVariant $variant Визуальный вариант кнопки
   * @param string|null $url Опциональный URL для кнопки-ссылки
   * @param bool $disabled Отключена ли кнопка
   * @param string|null $type Тип кнопки (button, submit, reset)
   * @param array<string, string> $attributes Дополнительные HTML атрибуты
   * @param string|null $icon Опциональный класс иконки или SVG
   * @param string|null $ariaLabel Опциональный aria-label для доступности
   * @param string|null $customClass Пользовательский CSS класс для дополнительной стилизации
   * @param string|null $modalTarget ID модалки для открытия (data-modal-target)
   * @return void
   */
  public static function render(
    string $label,
    ButtonVariant $variant = ButtonVariant::Primary,
    ?string $url = null,
    bool $disabled = false,
    ?string $type = 'button',
    array $attributes = [],
    ?string $icon = null,
    ?string $ariaLabel = null,
    ?string $customClass = null,
    ?string $modalTarget = null,
  ): void {
    $props = new ButtonProps(
      label: $label,
      variant: $variant,
      url: $url,
      disabled: $disabled,
      type: $type,
      attributes: $attributes,
      icon: $icon,
      ariaLabel: $ariaLabel,
      customClass: $customClass,
      modalTarget: $modalTarget,
    );

    static::renderTemplate('default', static::propsToArray($props));
  }
}
