<?php

declare(strict_types=1);

namespace Theme\VirtualPages;

use Theme\Core\Contracts\ServiceProvider;

final class VirtualPageManager implements ServiceProvider
{
    private const QUERY_VAR = 'theme_virtual_page';

    /**
     * @var array<string, string>
     */
    private array $pages = [
        'preview-virtual-page' => 'example',
    ];

    public function register(): void
    {
        add_action('init', [$this, 'registerRoutes']);
        add_action('after_switch_theme', [$this, 'flushRules']);
        add_filter('query_vars', [$this, 'registerQueryVars']);
        add_action('template_redirect', [$this, 'prepareQuery']);
        add_filter('template_include', [$this, 'resolveTemplate']);
    }

    public function registerRoutes(): void
    {
        foreach ($this->pages as $slug => $template) {
            add_rewrite_rule(
                '^' . preg_quote($slug, '/') . '/?$',
                'index.php?' . self::QUERY_VAR . '=' . rawurlencode($template),
                'top'
            );
        }
    }

    public function flushRules(): void
    {
        $this->registerRoutes();
        flush_rewrite_rules();
    }

    /**
     * @param array<int, string> $queryVars
     * @return array<int, string>
     */
    public function registerQueryVars(array $queryVars): array
    {
        $queryVars[] = self::QUERY_VAR;

        return $queryVars;
    }

    public function prepareQuery(): void
    {
        $virtualPage = get_query_var(self::QUERY_VAR);

        if ($virtualPage === '') {
            return;
        }

        global $wp_query;

        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;

        status_header(200);
    }

    public function resolveTemplate(string $template): string
    {
        $virtualPage = (string) get_query_var(self::QUERY_VAR);

        if ($virtualPage === '') {
            return $template;
        }

        $virtualTemplate = THEME_PATH . '/virtual-pages/' . $virtualPage . '.php';

        return file_exists($virtualTemplate) ? $virtualTemplate : $template;
    }
}

