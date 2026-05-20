<?php

declare(strict_types=1);

namespace Theme\Assets;

use Theme\Core\Contracts\ServiceProvider;
use Theme\Helpers\Debug;

final class AssetsManager implements ServiceProvider
{
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_head', [$this, 'enqueueYandexMaps'], 5);
    }

    public function enqueueAssets(): void
    {
        $cssPath = THEME_DIST_PATH . '/css/style.css';
        $jsPath = THEME_DIST_PATH . '/js/app.js';

        if (! file_exists($cssPath) && ! file_exists($jsPath)) {
            wp_enqueue_style('theme-fallback', get_stylesheet_uri(), [], THEME_VERSION);

            return;
        }

        if (file_exists($cssPath)) {
            wp_enqueue_style(
                'theme-app',
                THEME_DIST_URI . '/css/style.css',
                [],
                (string) filemtime($cssPath)
            );
        }

        if (file_exists($jsPath)) {
            wp_enqueue_script(
                'theme-app',
                THEME_DIST_URI . '/js/app.js',
                [],
                (string) filemtime($jsPath),
                true
            );
        }

        $this->localizeScript();
    }

    private function localizeScript(): void
    {
        wp_localize_script(
            'theme-app',
            'themeSettings',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('theme_nonce'),
                'debug'   => Debug::enabled(),
                'yandexMapsKey' => 'e1dda692-8660-435d-9a8a-73b098beca58',
            ]
        );
    }

    public function enqueueYandexMaps(): void
    {
        echo '<script src="https://api-maps.yandex.ru/v3/?apikey=e1dda692-8660-435d-9a8a-73b098beca58&lang=ru_RU"></script>' . "\n";
    }
}
