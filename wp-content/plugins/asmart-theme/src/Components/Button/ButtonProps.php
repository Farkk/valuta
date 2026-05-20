<?php

declare(strict_types=1);

namespace Asmart\Components\Button;

use Asmart\Support\BaseProps;

/**
 * Props кнопки
 *
 * Иммутабельный DTO для компонента Button.
 */
readonly class ButtonProps extends BaseProps
{
  /**
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
   */
  public function __construct(
    public string $label,
    public ButtonVariant $variant = ButtonVariant::Primary,
    public ?string $url = null,
    public bool $disabled = false,
    public ?string $type = 'button',
    public array $attributes = [],
    public ?string $icon = null,
    public ?string $ariaLabel = null,
    public ?string $customClass = null,
    public ?string $modalTarget = null,
  ) {
    $this->validate();
  }

  /**
   * Валидация props
   *
   * @throws \InvalidArgumentException
   */
  protected function validate(): void
  {
    if (empty(trim($this->label))) {
      throw new \InvalidArgumentException('Button label cannot be empty');
    }

    if (
      $this->type !== null &&
      !in_array($this->type, ['button', 'submit', 'reset'], true)
    ) {
      throw new \InvalidArgumentException(
        'Button type must be one of: button, submit, reset',
      );
    }
  }

  /**
   * Проверка, является ли кнопка ссылкой
   */
  public function isLink(): bool
  {
    return $this->url !== null;
  }

  /**
   * Получить имя тега (button или a)
   */
  public function getTagName(): string
  {
    return $this->isLink() ? 'a' : 'button';
  }
}
