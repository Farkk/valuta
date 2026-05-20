<?php

declare(strict_types=1);

namespace Asmart\Support;

/**
 * Базовые Props
 *
 * Базовый класс для Props компонентов (DTO).
 * Все Props компонентов должны наследовать этот класс.
 *
 */
abstract readonly class BaseProps
{
  /**
   * Валидация данных props
   *
   * Переопределите в дочерних классах для добавления пользовательской валидации.
   * Выбрасывайте InvalidArgumentException при ошибке валидации.
   *
   * @return void
   * @throws \InvalidArgumentException
   */
  protected function validate(): void
  {
    // Базовая валидация может быть добавлена здесь при необходимости
  }
}
