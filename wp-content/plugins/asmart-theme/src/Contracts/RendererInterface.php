<?php

declare(strict_types=1);

namespace Asmart\Contracts;

/**
 * Интерфейс рендерера
 *
 * Контракт для рендеринга шаблонов с данными.
 */
interface RendererInterface
{
  /**
   * Рендеринг шаблона с предоставленными данными
   *
   * @param string $templatePath Полный путь к файлу шаблона
   * @param array<string, mixed> $data Данные для передачи в шаблон
   * @return void
   */
  public function render(string $templatePath, array $data): void;

  /**
   * Получить отрендеренный шаблон в виде строки
   *
   * @param string $templatePath Полный путь к файлу шаблона
   * @param array<string, mixed> $data Данные для передачи в шаблон
   * @return string Отрендеренный вывод
   */
  public function get(string $templatePath, array $data): string;
}
