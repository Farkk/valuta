<?php

declare(strict_types=1);

namespace Asmart\Components\Modal;

use Asmart\Support\BaseProps;

/**
 * Props модалки
 *
 * Иммутабельный DTO для компонента Modal.
 */
readonly class ModalProps extends BaseProps
{
  /**
   * @param string $title Заголовок модалки
   * @param string $content Содержимое модалки (разрешен HTML)
   * @param ModalTemplate $template Тип шаблона модалки
   * @param ModalSize $size Размер модалки
   * @param bool $closeable Можно ли закрыть модалку
   * @param string $id Уникальный ID модалки
   * @param string|null $footer Опциональное содержимое подвала (HTML)
   * @param array<string, string> $attributes Дополнительные HTML атрибуты
   */
  public function __construct(
    public string $title,
    public string $content,
    public ModalTemplate $template = ModalTemplate::Default,
    public ModalSize $size = ModalSize::Medium,
    public bool $closeable = true,
    public string $id = '',
    public ?string $footer = null,
    public array $attributes = [],
  ) {
    // Генерация уникального ID, если не предоставлен
    if (empty($this->id)) {
      $this->id = 'asmart-modal-' . uniqid();
    }

    $this->validate();
  }

  /**
   * Валидация props
   */
  protected function validate(): void
  {
    if (empty(trim($this->title))) {
      throw new \InvalidArgumentException('Modal title cannot be empty');
    }

    if (empty(trim($this->content))) {
      throw new \InvalidArgumentException('Modal content cannot be empty');
    }
  }

  /**
   * Получить классы модалки
   *
   * @return array<string>
   */
  public function getClasses(): array
  {
    return [
      'asmart-modal',
      'asmart-modal--' . $this->template->value,
      'asmart-modal--' . $this->size->value,
    ];
  }
}
