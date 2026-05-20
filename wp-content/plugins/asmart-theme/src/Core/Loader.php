<?php

declare(strict_types=1);

namespace Asmart\Core;

/**
 * Загрузчик шаблонов
 *
 * Загружает и рендерит шаблоны компонентов с правильной изоляцией данных.
 */
final class Loader
{
  /**
   * Загрузка и рендеринг файла шаблона
   *
   * @param string $templatePath Полный путь к файлу шаблона
   * @param array<string, mixed> $data Данные для передачи в шаблон
   * @return void
   * @throws \RuntimeException Если файл шаблона не существует
   */
  public static function render(string $templatePath, array $data = []): void
  {
    if (!file_exists($templatePath)) {
      throw new \RuntimeException(
        sprintf('Template file not found: %s', $templatePath),
      );
    }

    // Извлечение данных для доступа к переменным в шаблоне
    // Использование extract с EXTR_SKIP для предотвращения перезаписи существующих переменных
    extract($data, EXTR_SKIP);

    // Подключение шаблона с изолированной областью видимости
    require $templatePath;
  }

  /**
   * Загрузка и возврат вывода шаблона в виде строки
   *
   * @param string $templatePath Полный путь к файлу шаблона
   * @param array<string, mixed> $data Данные для передачи в шаблон
   * @return string Отрендеренный вывод шаблона
   * @throws \RuntimeException Если файл шаблона не существует
   */
  public static function get(string $templatePath, array $data = []): string
  {
    ob_start();

    try {
      self::render($templatePath, $data);
      return ob_get_clean() ?: '';
    } catch (\Throwable $e) {
      ob_end_clean();
      throw $e;
    }
  }
}
