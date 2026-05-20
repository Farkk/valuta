<?php
/**
 * Plugin Name: Asmart UI Components
 * Plugin URI: https://github.com/asmart/wp-ui-plugin
 * Description: High-performance UI components for WordPress custom themes
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Asmart
 * Author URI: https://asmart.dev
 * License: MIT
 * Text Domain: asmart
 * Domain Path: /languages
 */

declare(strict_types=1);

namespace Asmart;

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Define plugin constants
define('ASMART_PLUGIN_VERSION', '1.0.0');
define('ASMART_PLUGIN_FILE', __FILE__);
define('ASMART_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ASMART_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ASMART_TEMPLATES_DIR', ASMART_PLUGIN_DIR . 'templates/');

// Require Composer autoloader
if (file_exists(ASMART_PLUGIN_DIR . 'vendor/autoload.php')) {
  require_once ASMART_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize plugin
add_action('plugins_loaded', function () {
  Core\Plugin::getInstance()->init();
});

// Activation hook
register_activation_hook(__FILE__, function () {
  // Perform activation tasks if needed
  if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(
      esc_html__('Asmart UI Components requires PHP 8.1 or higher.', 'asmart'),
      esc_html__('Plugin Activation Error', 'asmart'),
      ['back_link' => true],
    );
  }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
  // Perform deactivation tasks if needed
  // Clean up transients or temporary data
});
