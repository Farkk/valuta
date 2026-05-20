<?php

declare(strict_types=1);

namespace Asmart\Contracts;

/**
 * Интерфейс компонента
 *
 * Контракт для всех UI компонентов.
 * Определяет публичный API, который должен реализовать каждый компонент.
 *
 * Примечание: метод render() НЕ является частью этого интерфейса, потому что каждый компонент
 * имеет свою уникальную сигнатуру render() с именованными аргументами.
 */
interface ComponentInterface
{
  /**
   * Получить разрешенный путь к шаблону компонента
   *
   * @param string $template Имя шаблона (например, 'default', 'success')
   * @return string Полный путь к файлу шаблона
   */
  public static function getTemplatePath(string $template): string;
}
