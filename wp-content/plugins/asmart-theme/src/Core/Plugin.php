<?php

declare(strict_types=1);

namespace Asmart\Core;

/**
 * Главный класс плагина
 *
 * Паттерн Singleton для инициализации плагина и управления его жизненным циклом.
 * Обрабатывает регистрацию компонентов и загрузку плагина.
 */
final class Plugin
{
  private static ?self $instance = null;

  private TemplateResolver $templateResolver;
  private bool $initialized = false;

  /**
   * Приватный конструктор для предотвращения прямого создания экземпляра
   */
  private function __construct()
  {
    $this->templateResolver = new TemplateResolver();
  }

  /**
   * Получить экземпляр singleton
   */
  public static function getInstance(): self
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Инициализация плагина
   *
   * Вызывается через хук plugins_loaded в главном файле плагина
   */
  public function init(): void
  {
    if ($this->initialized) {
      return;
    }

    $this->loadTextDomain();
    $this->registerHooks();
    $this->registerComponents();

    $this->initialized = true;
  }

  /**
   * Загрузка текстового домена плагина для переводов
   */
  private function loadTextDomain(): void
  {
    load_plugin_textdomain(
      'asmart',
      false,
      dirname(plugin_basename(ASMART_PLUGIN_FILE)) . '/languages',
    );
  }

  /**
   * Регистрация WordPress хуков
   *
   */
  private function registerHooks(): void
  {
    // Подключение ассетов только при необходимости
    add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

    // Очистка кеша шаблонов при смене темы
    add_action('switch_theme', [$this->templateResolver, 'clearCache']);
  }

  /**
   * Регистрация доступных компонентов
   *
   * Компоненты регистрируются здесь для поддержания единого источника истины
   */
  private function registerComponents(): void
  {
    // Компоненты будут регистрироваться здесь по мере их реализации
    // Примеры: Button, Modal, Pagination

    // Компоненты загружаются лениво через автозагрузчик Composer
    // Нет необходимости явно подключать файлы компонентов
  }

  /**
   * Подключение ассетов плагина (CSS/JS)
   *
   * Ассеты подключаются условно в зависимости от использования.
   * Использует file timestamp для автоматического сброса кеша.
   */
  public function enqueueAssets(): void
  {
    $distPath = ASMART_PLUGIN_DIR . 'assets/dist/';
    $distUrl = ASMART_PLUGIN_URL . 'assets/dist/';

    // Проверяем наличие манифеста сборки Vite
    $manifestFile = $distPath . '.vite/manifest.json';

    if (file_exists($manifestFile)) {
      // Production build с Vite - используем манифест
      $manifest = json_decode(file_get_contents($manifestFile), true);

      // Main CSS (содержит переменные и базовые стили)
      $mainKey = 'assets/src/scss/main.scss';
      if (isset($manifest[$mainKey]['file'])) {
        wp_enqueue_style(
          'asmart-main',
          $distUrl . $manifest[$mainKey]['file'],
          [],
          null, // версия уже в хеше файла
        );
      }

      // Button CSS
      $buttonKey = 'assets/src/scss/button.scss';
      if (isset($manifest[$buttonKey]['file'])) {
        wp_enqueue_style(
          'asmart-button',
          $distUrl . $manifest[$buttonKey]['file'],
          ['asmart-main'],
          null,
        );
      }

      // Modal CSS
      $modalKey = 'assets/src/scss/modal.scss';
      if (isset($manifest[$modalKey]['file'])) {
        wp_enqueue_style(
          'asmart-modal',
          $distUrl . $manifest[$modalKey]['file'],
          ['asmart-main'],
          null,
        );
      }

      // Pagination CSS
      $paginationKey = 'assets/src/scss/pagination.scss';
      if (isset($manifest[$paginationKey]['file'])) {
        wp_enqueue_style(
          'asmart-pagination',
          $distUrl . $manifest[$paginationKey]['file'],
          ['asmart-main'],
          null,
        );
      }

      // Card CSS
      $cardKey = 'assets/src/scss/card.scss';
      if (isset($manifest[$cardKey]['file'])) {
        wp_enqueue_style(
          'asmart-card',
          $distUrl . $manifest[$cardKey]['file'],
          ['asmart-main'],
          null,
        );
      }

      // Modal JS
      $modalJsKey = 'assets/src/js/modal.js';
      if (isset($manifest[$modalJsKey]['file'])) {
        wp_enqueue_script(
          'asmart-modal-js',
          $distUrl . $manifest[$modalJsKey]['file'],
          [],
          null,
          true,
        );
      }

      // Pagination JS
      $paginationJsKey = 'assets/src/js/pagination.js';
      if (isset($manifest[$paginationJsKey]['file'])) {
        wp_enqueue_script(
          'asmart-pagination-js',
          $distUrl . $manifest[$paginationJsKey]['file'],
          [],
          null,
          true,
        );

        // Добавление nonce для AJAX запросов
        wp_localize_script('asmart-pagination-js', 'asmartPaginationData', [
          'nonce' => wp_create_nonce('asmart_pagination'),
          'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
      }
    }
  }

  /**
   * Получить экземпляр резолвера шаблонов
   */
  public function getTemplateResolver(): TemplateResolver
  {
    return $this->templateResolver;
  }

  /**
   * Предотвращение клонирования
   */
  private function __clone() {}

  /**
   * Предотвращение десериализации
   */
  public function __wakeup()
  {
    throw new \Exception('Cannot unserialize singleton');
  }
}
