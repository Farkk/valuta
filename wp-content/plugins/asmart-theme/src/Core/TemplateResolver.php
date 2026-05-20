<?php

declare(strict_types=1);

namespace Asmart\Core;

/**
 * Резолвер шаблонов
 *
 * Разрешает пути к шаблонам с поддержкой переопределения темой и кеширования.
 * Реализует подход с приоритетом производительности
 */
final class TemplateResolver
{
  /**
   * In-memory кеш для разрешенных путей к шаблонам
   *
   * @var array<string, string>
   */
  private array $cache = [];

  /**
   * Имя директории компонентов в теме
   */
  private const THEME_DIR = 'asmart-components';

  /**
   * Разрешение пути к шаблону с поддержкой переопределения темой
   *
   * Порядок поиска:
   * 1. Активная тема: {theme}/asmart-components/{component}/{template}.php
   * 2. Плагин: {plugin}/templates/{component}/{template}.php
   *
   * @param string $component Имя компонента (например, 'button', 'modal')
   * @param string $template Имя шаблона (например, 'default', 'primary', 'success')
   * @return string Разрешенный путь к шаблону
   * @throws \RuntimeException Если шаблон не найден
   */
  public function resolve(
    string $component,
    string $template = 'default',
  ): string {
    $cacheKey = $this->getCacheKey($component, $template);

    // Возврат из кеша, если доступен
    if (isset($this->cache[$cacheKey])) {
      return $this->cache[$cacheKey];
    }

    // Сначала проверяем директорию темы
    $themePath = $this->getThemePath($component, $template);
    if (file_exists($themePath)) {
      $this->cache[$cacheKey] = $themePath;
      return $themePath;
    }

    // Fallback на директорию плагина
    $pluginPath = $this->getPluginPath($component, $template);
    if (file_exists($pluginPath)) {
      $this->cache[$cacheKey] = $pluginPath;
      return $pluginPath;
    }

    // Шаблон не найден
    throw new \RuntimeException(
      sprintf(
        'Template not found: %s/%s.php (searched in theme and plugin)',
        $component,
        $template,
      ),
    );
  }

  /**
   * Получить путь к шаблону в теме
   *
   * @param string $component Имя компонента
   * @param string $template Имя шаблона
   * @return string Полный путь к шаблону в теме
   */
  private function getThemePath(string $component, string $template): string
  {
    return sprintf(
      '%s/%s/%s/%s.php',
      get_stylesheet_directory(),
      self::THEME_DIR,
      $component,
      $template,
    );
  }

  /**
   * Получить путь к шаблону в плагине
   *
   * @param string $component Имя компонента
   * @param string $template Имя шаблона
   * @return string Полный путь к шаблону в плагине
   */
  private function getPluginPath(string $component, string $template): string
  {
    return sprintf('%s%s/%s.php', ASMART_TEMPLATES_DIR, $component, $template);
  }

  /**
   * Генерация ключа кеша для шаблона
   *
   * @param string $component Имя компонента
   * @param string $template Имя шаблона
   * @return string Ключ кеша
   */
  private function getCacheKey(string $component, string $template): string
  {
    return "{$component}:{$template}";
  }

  /**
   * Проверка существования шаблона
   *
   * @param string $component Имя компонента
   * @param string $template Имя шаблона
   * @return bool True, если шаблон существует
   */
  public function exists(string $component, string $template = 'default'): bool
  {
    try {
      $this->resolve($component, $template);
      return true;
    } catch (\RuntimeException) {
      return false;
    }
  }

  /**
   * Очистка кеша шаблонов
   *
   * Вызывается при смене темы для инвалидации закешированных путей
   */
  public function clearCache(): void
  {
    $this->cache = [];
  }

  /**
   * Получить все закешированные пути (для отладки)
   *
   * @return array<string, string>
   */
  public function getCachedPaths(): array
  {
    return $this->cache;
  }
}
