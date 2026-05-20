<?php

declare(strict_types=1);

namespace Asmart\Components;

use Asmart\Contracts\ComponentInterface;
use Asmart\Core\Loader;
use Asmart\Core\Plugin;

/**
 * Базовый компонент
 *
 * Абстрактный базовый класс для всех UI компонентов.
 * Предоставляет общий функционал для разрешения и рендеринга шаблонов.
 */
abstract class BaseComponent implements ComponentInterface
{
  /**
   * Имя компонента для разрешения шаблонов
   *
   * Должно быть переопределено в дочерних классах.
   * Примеры: 'button', 'modal', 'pagination'
   */
  protected static string $componentName = '';

  /**
   * Получить имя компонента
   *
   * @return string Имя компонента
   * @throws \RuntimeException Если имя компонента не установлено
   */
  protected static function getComponentName(): string
  {
    if (empty(static::$componentName)) {
      throw new \RuntimeException(
        sprintf('Component name not set in %s', static::class),
      );
    }

    return static::$componentName;
  }

  /**
   * Получить разрешенный путь к шаблону
   *
   * @param string $template Имя шаблона
   * @return string Полный путь к файлу шаблона
   */
  public static function getTemplatePath(string $template = 'default'): string
  {
    return Plugin::getInstance()
      ->getTemplateResolver()
      ->resolve(static::getComponentName(), $template);
  }

  /**
   * Рендеринг компонента с данными
   *
   * @param string $template Имя шаблона
   * @param array<string, mixed> $data Данные для передачи в шаблон
   * @return void
   */
  protected static function renderTemplate(string $template, array $data): void
  {
    $templatePath = static::getTemplatePath($template);
    Loader::render($templatePath, $data);
  }

  /**
   * Получить отрендеренный компонент в виде строки
   *
   * @param string $template Имя шаблона
   * @param array<string, mixed> $data Данные для передачи в шаблон
   * @return string Отрендеренный вывод
   */
  protected static function getTemplate(string $template, array $data): string
  {
    $templatePath = static::getTemplatePath($template);
    return Loader::get($templatePath, $data);
  }

  /**
   * Конвертация объекта Props в массив для шаблона
   *
   * @param object $props Объект Props
   * @return array<string, mixed> Props в виде массива
   */
  protected static function propsToArray(object $props): array
  {
    return ['props' => $props];
  }
}
