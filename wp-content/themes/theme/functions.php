<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('THEME_PATH', get_template_directory());
define('THEME_URI', get_template_directory_uri());
define('THEME_DIST_PATH', THEME_PATH . '/dist');
define('THEME_DIST_URI', THEME_URI . '/dist');
define('THEME_VERSION', (string) wp_get_theme()->get('Version'));

$composerAutoload = THEME_PATH . '/vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    require_once THEME_PATH . '/inc/Core/Autoloader.php';

    Theme\Core\Autoloader::register();
}

$theme = new Theme\Core\Application();
$theme->boot();

