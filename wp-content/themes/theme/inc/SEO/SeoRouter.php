<?php

declare(strict_types=1);

namespace Theme\SEO;

use Theme\Core\Contracts\ServiceProvider;
use WP_Query;

final class SeoRouter implements ServiceProvider
{
    public function register(): void
    {
        SeoPagePostType::register();
        SeoPageFields::register();
        SeoPageManager::register();

        add_action('template_redirect', [$this, 'handleRequest'], 8);
    }

    public function handleRequest(): void
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        $path = $this->getRequestPath();

        if ($path === '/' || $this->isReservedPath($path)) {
            return;
        }

        $page = SeoPageManager::getPageByUrl($path);

        if (! $page) {
            return;
        }

        SeoPageManager::setCurrentPage($page);
        $this->primeQueryVars();

        global $wp_query;

        if ($wp_query instanceof WP_Query) {
            $wp_query->is_404 = false;
            $wp_query->is_page = false;
            $wp_query->is_singular = false;
            $wp_query->set(SeoPageManager::POST_TYPE, $page->ID);
        }

        status_header(200);

        include THEME_PATH . '/front-page.php';
        exit;
    }

    private function getRequestPath(): string
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

        return SeoPageManager::normalizeUrl((string) parse_url($requestUri, PHP_URL_PATH));
    }

    private function isReservedPath(string $path): bool
    {
        foreach (['/currency/', '/city/', '/bank/', '/news', '/articles', '/reviews', '/wp-json', '/wp-admin', '/wp-content'] as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function primeQueryVars(): void
    {
        $type = SeoPageManager::getCurrentType();
        $currency = SeoPageManager::getCurrentCurrencyCode();
        $citySlug = SeoPageManager::getCurrentCitySlug();
        $amount = SeoPageManager::getCurrentAmount();

        if ($citySlug !== '') {
            $this->setQueryVar('city', $citySlug);
        }

        if ($currency !== '') {
            $this->setQueryVar('currency_code', $currency);
        }

        if ($type === SeoTemplateRenderer::TYPE_CONVERSION && $amount > 0 && $currency !== '') {
            $this->setQueryVar('currency_pair', $amount . $currency . '_rub');
        }
    }

    private function setQueryVar(string $key, string $value): void
    {
        set_query_var($key, $value);

        global $wp_query;

        if ($wp_query instanceof WP_Query) {
            $wp_query->set($key, $value);
        }
    }
}
