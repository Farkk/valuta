<?php

declare(strict_types=1);

namespace Theme\Content;

use Theme\Core\Contracts\ServiceProvider;
use Theme\Helpers\PostTypeContent;
use WP_Query;
use WP_Term;

final class ContentTypeRouter implements ServiceProvider
{
    private const REWRITE_OPTION = 'theme_content_type_rewrite_version';

    private const REWRITE_VERSION = '2';

    public function register(): void
    {
        add_action('init', [$this, 'registerRewriteRules'], 20);
        add_action('init', [$this, 'maybeFlushRewriteRules'], 99);
        add_filter('request', [$this, 'parseCategoryRequest']);
        add_action('pre_get_posts', [$this, 'primeTaxonomyMainQuery']);
        add_filter('template_include', [$this, 'resolveTemplate']);
    }

    public function registerRewriteRules(): void
    {
        foreach (PostTypeContent::getRoutedConfigs() as $config) {
            $prefix = $config['category_prefix'];

            add_rewrite_rule(
                '^' . $prefix . '/([^/]+)/page/?([0-9]{1,})/?$',
                'index.php?' . $config['taxonomy'] . '=$matches[1]&paged=$matches[2]',
                'top'
            );

            add_rewrite_rule(
                '^' . $prefix . '/([^/]+)/?$',
                'index.php?' . $config['taxonomy'] . '=$matches[1]',
                'top'
            );
        }
    }

    public function maybeFlushRewriteRules(): void
    {
        if (get_option(self::REWRITE_OPTION) === self::REWRITE_VERSION) {
            return;
        }

        $this->registerRewriteRules();
        flush_rewrite_rules(false);
        update_option(self::REWRITE_OPTION, self::REWRITE_VERSION, false);
    }

    /**
     * @param array<string, string> $queryVars
     * @return array<string, string>
     */
    public function parseCategoryRequest(array $queryVars): array
    {
        if (is_admin()) {
            return $queryVars;
        }

        $path = $this->getRequestPath();

        foreach (PostTypeContent::getRoutedConfigs() as $config) {
            $pattern = '#^' . preg_quote($config['category_prefix'], '#') . '/([^/]+)(?:/page/([0-9]+))?/?$#';

            if (! preg_match($pattern, $path, $matches)) {
                continue;
            }

            $queryVars[$config['taxonomy']] = sanitize_title($matches[1]);

            if (! empty($matches[2])) {
                $queryVars['paged'] = (string) max(1, (int) $matches[2]);
            }

            unset($queryVars['name'], $queryVars['pagename'], $queryVars['page'], $queryVars['attachment']);

            return $queryVars;
        }

        return $queryVars;
    }

    public function primeTaxonomyMainQuery(WP_Query $query): void
    {
        if (is_admin() || ! $query->is_main_query()) {
            return;
        }

        foreach (PostTypeContent::getRoutedConfigs() as $config) {
            $slug = sanitize_title((string) get_query_var($config['taxonomy'], ''));

            if ($slug === '') {
                continue;
            }

            $term = get_term_by('slug', $slug, $config['taxonomy']);

            if (! $term instanceof WP_Term) {
                $query->set_404();
                status_header(404);

                return;
            }

            $query->set('post_type', $config['post_type']);
            $query->set(
                'tax_query',
                [
                    [
                        'taxonomy' => $config['taxonomy'],
                        'field' => 'slug',
                        'terms' => $slug,
                    ],
                ]
            );

            $query->is_tax = true;
            $query->is_archive = true;
            $query->is_home = false;
            $query->is_singular = false;
            $query->is_404 = false;
            $query->queried_object = $term;
            $query->queried_object_id = $term->term_id;

            return;
        }
    }

    public function resolveTemplate(string $template): string
    {
        if (is_admin() || is_404()) {
            return $template;
        }

        foreach (PostTypeContent::getRoutedConfigs() as $config) {
            $slug = sanitize_title((string) get_query_var($config['taxonomy'], ''));

            if ($slug === '') {
                continue;
            }

            $term = get_term_by('slug', $slug, $config['taxonomy']);

            if (! $term instanceof WP_Term) {
                continue;
            }

            $taxonomyTemplate = THEME_PATH . '/taxonomy-' . $config['taxonomy'] . '.php';

            return file_exists($taxonomyTemplate) ? $taxonomyTemplate : $template;
        }

        return $template;
    }

    private function getRequestPath(): string
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        $path = trim($path, '/');

        $homePath = trim((string) parse_url(home_url('/'), PHP_URL_PATH), '/');

        if ($homePath !== '' && str_starts_with($path, $homePath . '/')) {
            $path = substr($path, strlen($homePath) + 1);
        }

        return trim($path, '/');
    }
}
