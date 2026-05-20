<?php

declare(strict_types=1);

namespace Theme\Cities;

use Theme\Core\Contracts\ServiceProvider;

final class CityRouter implements ServiceProvider
{
    private const REWRITE_OPTION = 'theme_city_rewrite_version';

    public function register(): void
    {
        add_action('init', [$this, 'registerRewriteRules']);
        add_action('init', [$this, 'maybeFlushRewriteRules'], 20);
        add_filter('query_vars', [$this, 'registerQueryVars']);
        add_action('template_redirect', [$this, 'handleCityPage']);
        add_action('after_switch_theme', [$this, 'flushRewriteRules']);
    }

    public function registerRewriteRules(): void
    {
        add_rewrite_rule(
            '^city/([^/]+)/?$',
            'index.php?city=$matches[1]',
            'top'
        );
    }

    /**
     * @param list<string> $vars
     * @return list<string>
     */
    public function registerQueryVars(array $vars): array
    {
        $vars[] = 'city';

        return $vars;
    }

    public function handleCityPage(): void
    {
        $citySlug = (string) get_query_var('city', '');

        if ($citySlug === '') {
            return;
        }

        $normalized = CityManager::normalizeSlug($citySlug);

        if ($normalized !== $citySlug) {
            wp_safe_redirect(CityManager::getCityUrl($normalized), 301);
            exit;
        }

        if (! CityManager::isValidSlug($normalized)) {
            global $wp_query;

            $wp_query->set_404();
            status_header(404);
            nocache_headers();

            return;
        }

        CityManager::setCityCookie($normalized);
        include THEME_PATH . '/front-page.php';
        exit;
    }

    public function maybeFlushRewriteRules(): void
    {
        if (get_option(self::REWRITE_OPTION) === THEME_VERSION) {
            return;
        }

        $this->flushRewriteRules();
        update_option(self::REWRITE_OPTION, THEME_VERSION, false);
    }

    public function flushRewriteRules(): void
    {
        $this->registerRewriteRules();
        flush_rewrite_rules(false);
    }
}
