<?php

declare(strict_types=1);

namespace Theme\Currencies;

use Theme\Cities\CityManager;
use Theme\Core\Contracts\ServiceProvider;

final class CurrencyRouter implements ServiceProvider
{
    public const DEFAULT_CURRENCY = 'usd';
    private const REWRITE_OPTION = 'theme_currency_rewrite_version';

    public function register(): void
    {
        add_action('init', [$this, 'registerRewriteRules']);
        add_action('init', [$this, 'maybeFlushRewriteRules'], 21);
        add_filter('query_vars', [$this, 'registerQueryVars']);
        add_action('template_redirect', [$this, 'handleCurrencyPage'], 9);
        add_action('after_switch_theme', [$this, 'flushRewriteRules']);
    }

    public function registerRewriteRules(): void
    {
        add_rewrite_rule('^currency/(\d+)([a-z]{3})_([a-z]{3})/?$', 'index.php?currency_pair=$matches[1]$matches[2]_$matches[3]', 'top');
        add_rewrite_rule('^currency/(\d+)([a-z]{3})_([a-z]{3})/([^/]+)/?$', 'index.php?currency_pair=$matches[1]$matches[2]_$matches[3]&city=$matches[4]', 'top');
        add_rewrite_rule('^currency/([^/]+)/?$', 'index.php?currency_code=$matches[1]', 'top');
        add_rewrite_rule('^currency/([^/]+)/([^/]+)/?$', 'index.php?currency_code=$matches[1]&city=$matches[2]', 'top');
    }

    /**
     * @param list<string> $vars
     * @return list<string>
     */
    public function registerQueryVars(array $vars): array
    {
        $vars[] = 'currency_code';
        $vars[] = 'currency_pair';

        return $vars;
    }

    public function handleCurrencyPage(): void
    {
        if (! self::isCurrencyPage()) {
            return;
        }

        if (self::isCurrencyPairPage() && self::getCurrencyPairData() === null) {
            $this->set404();
            return;
        }

        $citySlug = (string) get_query_var('city', '');
        if ($citySlug !== '') {
            $normalized = CityManager::normalizeSlug($citySlug);

            if ($normalized !== $citySlug) {
                wp_safe_redirect(home_url('/currency/' . self::getCurrentCurrencyPath() . '/' . $normalized . '/'), 301);
                exit;
            }

            if (! CityManager::isValidSlug($normalized)) {
                $this->set404();
                return;
            }

            CityManager::setCityCookie($normalized);
        }

        include THEME_PATH . '/front-page.php';
        exit;
    }

    public static function getCurrentCurrency(): string
    {
        $code = (string) get_query_var('currency_code', '');

        if ($code !== '') {
            return strtolower($code);
        }

        $pair = self::getCurrencyPairData();

        return $pair['from'] ?? self::DEFAULT_CURRENCY;
    }

    public static function isCurrencyPage(): bool
    {
        return (string) get_query_var('currency_code', '') !== '' || (string) get_query_var('currency_pair', '') !== '';
    }

    public static function isCurrencyPairPage(): bool
    {
        return (string) get_query_var('currency_pair', '') !== '';
    }

    /**
     * @return array{amount: int, from: string, to: string}|null
     */
    public static function getCurrencyPairData(): ?array
    {
        $pair = (string) get_query_var('currency_pair', '');

        if ($pair === '' || ! preg_match('/^(\d+)([a-z]{3})_([a-z]{3})$/', $pair, $matches)) {
            return null;
        }

        return [
            'amount' => (int) $matches[1],
            'from'   => strtolower($matches[2]),
            'to'     => strtolower($matches[3]),
        ];
    }

    public static function getCurrentCurrencyPath(): string
    {
        $pair = (string) get_query_var('currency_pair', '');

        return $pair !== '' ? $pair : self::getCurrentCurrency();
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

    private function set404(): void
    {
        global $wp_query;

        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}
