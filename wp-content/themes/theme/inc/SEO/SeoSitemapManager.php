<?php

declare(strict_types=1);

namespace Theme\SEO;

use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyManager;
use WP_Post;

final class SeoSitemapManager
{
    public const OPTION_TEXT = 'theme_seo_sitemap_text';
    public const OPTION_UPDATED_AT = 'theme_seo_sitemap_updated_at';
    public const OPTION_SETTINGS = 'theme_seo_sitemap_settings';

    private const DEFAULT_CURRENCY_CODES = [
        'USD',
        'EUR',
        'CNY',
        'GBP',
        'CHF',
        'JPY',
        'KZT',
        'AUD',
    ];

    public static function register(): void
    {
        add_action('template_redirect', [self::class, 'maybeRenderPublicSitemap'], 1);
    }

    public static function maybeRenderPublicSitemap(): void
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($requestUri, PHP_URL_PATH);

        if ($path !== '/sitemap.xml') {
            return;
        }

        status_header(200);
        nocache_headers();
        header('Content-Type: application/xml; charset=' . get_bloginfo('charset'));

        echo self::renderXml(self::getActiveRecords());
        exit;
    }

    public static function getPublicUrl(): string
    {
        return home_url('/sitemap.xml');
    }

    public static function getSavedText(): string
    {
        return (string) get_option(self::OPTION_TEXT, '');
    }

    public static function getSavedUpdatedAt(): int
    {
        return (int) get_option(self::OPTION_UPDATED_AT, 0);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSettings(): array
    {
        $stored = get_option(self::OPTION_SETTINGS, []);

        return self::sanitizeSettings(is_array($stored) ? $stored : []);
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public static function saveSettings(array $settings): array
    {
        $sanitized = self::sanitizeSettings($settings);
        update_option(self::OPTION_SETTINGS, $sanitized, false);

        return $sanitized;
    }

    /**
     * @return array<string, string>
     */
    public static function getPostTypeChoices(): array
    {
        $choices = [];
        $postTypes = get_post_types(['public' => true], 'objects');

        if (! is_array($postTypes)) {
            return $choices;
        }

        foreach ($postTypes as $postType => $postTypeObject) {
            if ($postType === 'attachment') {
                continue;
            }

            $choices[$postType] = (string) ($postTypeObject->labels->name ?? $postType);
        }

        asort($choices);

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    public static function getPostTypeArchiveChoices(): array
    {
        $choices = [];
        $postTypes = get_post_types(['public' => true], 'objects');

        if (! is_array($postTypes)) {
            return $choices;
        }

        foreach ($postTypes as $postType => $postTypeObject) {
            if ($postType === 'attachment' || empty($postTypeObject->has_archive)) {
                continue;
            }

            $choices[$postType] = (string) ($postTypeObject->labels->name ?? $postType);
        }

        asort($choices);

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    public static function getTaxonomyChoices(): array
    {
        $choices = [];
        $taxonomies = get_taxonomies(['public' => true], 'objects');

        if (! is_array($taxonomies)) {
            return $choices;
        }

        foreach ($taxonomies as $taxonomy => $taxonomyObject) {
            $choices[$taxonomy] = (string) ($taxonomyObject->labels->name ?? $taxonomy);
        }

        asort($choices);

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    public static function getVirtualSourceChoices(): array
    {
        return [
            'cities' => 'Страницы городов `/city/{slug}/`',
            'currencies' => 'Страницы валют `/currency/{code}/`',
            'city_currencies' => 'Страницы валют по городам `/currency/{code}/{city}/`',
        ];
    }

    /**
     * @return array{text: string, count: int}
     */
    public static function saveText(string $text): array
    {
        $normalized = self::sanitizeEditableText($text);

        update_option(self::OPTION_TEXT, $normalized, false);
        update_option(self::OPTION_UPDATED_AT, time(), false);

        return [
            'text' => $normalized,
            'count' => self::countUrlsInText($normalized),
        ];
    }

    /**
     * @return array{text: string, count: int}
     */
    public static function generateAndSave(): array
    {
        return self::saveText(self::generateEditableText());
    }

    /**
     * @return list<array{loc: string, lastmod?: string}>
     */
    public static function getActiveRecords(): array
    {
        $savedRecords = self::parseRecordsFromText(self::getSavedText());

        if ($savedRecords !== []) {
            return $savedRecords;
        }

        return self::generateRecords();
    }

    /**
     * @return list<array{loc: string, lastmod?: string}>
     */
    public static function generateRecords(): array
    {
        $groups = self::collectGeneratedGroups();
        $records = [];

        foreach ($groups as $groupRecords) {
            foreach ($groupRecords as $record) {
                $records[self::normalizeUrlKey($record['loc'])] = $record;
            }
        }

        return array_values($records);
    }

    public static function generateEditableText(): string
    {
        $groups = self::collectGeneratedGroups();
        $lines = [
            '# Sitemap generated on ' . wp_date('Y-m-d H:i:s'),
            '# One URL per line. Lines starting with # are ignored.',
            '',
        ];

        foreach ($groups as $label => $records) {
            if ($records === []) {
                continue;
            }

            $lines[] = '# ' . $label;

            foreach ($records as $record) {
                $lines[] = $record['loc'];
            }

            $lines[] = '';
        }

        return trim(implode("\n", $lines)) . "\n";
    }

    public static function countUrlsInText(string $text): int
    {
        return count(self::parseRecordsFromText($text));
    }

    /**
     * @param list<array{loc: string, lastmod?: string}> $records
     */
    public static function renderXml(array $records): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($records as $record) {
            if (($record['loc'] ?? '') === '') {
                continue;
            }

            $lines[] = '  <url>';
            $lines[] = '    <loc>' . self::xmlEscape((string) $record['loc']) . '</loc>';

            if (! empty($record['lastmod'])) {
                $lines[] = '    <lastmod>' . self::xmlEscape((string) $record['lastmod']) . '</lastmod>';
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    private static function sanitizeEditableText(string $text): string
    {
        $rawLines = preg_split('/\R/u', $text) ?: [];
        $lines = [];
        $seen = [];
        $previousBlank = true;

        foreach ($rawLines as $rawLine) {
            $line = trim((string) $rawLine);

            if ($line === '') {
                if (! $previousBlank && $lines !== []) {
                    $lines[] = '';
                }

                $previousBlank = true;
                continue;
            }

            if (str_starts_with($line, '#')) {
                $comment = sanitize_text_field(trim(ltrim($line, '#')));

                if ($comment !== '') {
                    $lines[] = '# ' . $comment;
                    $previousBlank = false;
                }

                continue;
            }

            $url = self::canonicalizeUrlCandidate($line);

            if ($url === '') {
                continue;
            }

            $key = self::normalizeUrlKey($url);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $lines[] = $url;
            $previousBlank = false;
        }

        $normalized = trim(implode("\n", $lines));

        return $normalized === '' ? '' : $normalized . "\n";
    }

    /**
     * @return list<array{loc: string, lastmod?: string}>
     */
    private static function parseRecordsFromText(string $text): array
    {
        $records = [];
        $seen = [];
        $lines = preg_split('/\R/u', $text) ?: [];

        foreach ($lines as $rawLine) {
            $line = trim((string) $rawLine);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $url = self::canonicalizeUrlCandidate($line);

            if ($url === '') {
                continue;
            }

            $key = self::normalizeUrlKey($url);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $records[] = ['loc' => $url];
        }

        return $records;
    }

    /**
     * @return array<string, list<array{loc: string, lastmod?: string}>>
     */
    private static function collectGeneratedGroups(): array
    {
        $settings = self::getSettings();
        $groups = [];
        $seen = [];

        if (empty($settings['exclude_homepage'])) {
            self::addRecord($groups, $seen, 'Основные страницы', [
                'loc' => home_url('/'),
            ], $settings);
        }

        self::collectPublicPostTypeArchives($groups, $seen, $settings);
        self::collectPublicPosts($groups, $seen, $settings);
        self::collectPublicTerms($groups, $seen, $settings);
        self::collectSeoPages($groups, $seen, $settings);
        self::collectVirtualPages($groups, $seen, $settings);

        return array_filter($groups);
    }

    /**
     * @param array<string, list<array{loc: string, lastmod?: string}>> $groups
     * @param array<string, true> $seen
     * @param array<string, mixed> $settings
     */
    private static function collectPublicPostTypeArchives(array &$groups, array &$seen, array $settings): void
    {
        $postTypes = get_post_types(['public' => true], 'objects');

        if (! is_array($postTypes)) {
            return;
        }

        foreach ($postTypes as $postType => $postTypeObject) {
            if ($postType === 'attachment') {
                continue;
            }

            if (in_array($postType, $settings['exclude_post_type_archives'], true)) {
                continue;
            }

            if (! empty($postTypeObject->has_archive)) {
                $archiveUrl = get_post_type_archive_link($postType);

                if ($archiveUrl) {
                    self::addRecord($groups, $seen, 'Архивы', ['loc' => $archiveUrl], $settings);
                }
            }
        }
    }

    /**
     * @param array<string, list<array{loc: string, lastmod?: string}>> $groups
     * @param array<string, true> $seen
     * @param array<string, mixed> $settings
     */
    private static function collectPublicPosts(array &$groups, array &$seen, array $settings): void
    {
        $postTypes = get_post_types(['public' => true], 'objects');

        if (! is_array($postTypes)) {
            return;
        }

        foreach ($postTypes as $postType => $postTypeObject) {
            if ($postType === 'attachment') {
                continue;
            }

            if (in_array($postType, $settings['exclude_post_types'], true)) {
                continue;
            }

            $group = $postType === 'page'
                ? 'Страницы'
                : (string) ($postTypeObject->labels->name ?? $postType);

            $posts = get_posts([
                'post_type' => $postType,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'ID',
                'order' => 'ASC',
                'fields' => 'all',
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]);

            foreach ($posts as $post) {
                $permalink = get_permalink($post);

                if (! $permalink) {
                    continue;
                }

                self::addRecord($groups, $seen, $group, [
                    'loc' => $permalink,
                    'lastmod' => mysql2date('c', $post->post_modified_gmt ?: $post->post_date_gmt, false),
                ], $settings);
            }
        }
    }

    /**
     * @param array<string, list<array{loc: string, lastmod?: string}>> $groups
     * @param array<string, true> $seen
     * @param array<string, mixed> $settings
     */
    private static function collectPublicTerms(array &$groups, array &$seen, array $settings): void
    {
        $taxonomies = get_taxonomies(['public' => true], 'objects');

        if (! is_array($taxonomies)) {
            return;
        }

        foreach ($taxonomies as $taxonomy => $taxonomyObject) {
            if (in_array($taxonomy, $settings['exclude_taxonomies'], true)) {
                continue;
            }

            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
            ]);

            if (is_wp_error($terms) || $terms === []) {
                continue;
            }

            $group = 'Таксономии: ' . (string) ($taxonomyObject->labels->name ?? $taxonomy);

            foreach ($terms as $term) {
                $termUrl = get_term_link($term);

                if (is_wp_error($termUrl) || ! $termUrl) {
                    continue;
                }

                self::addRecord($groups, $seen, $group, ['loc' => $termUrl], $settings);
            }
        }
    }

    /**
     * @param array<string, list<array{loc: string, lastmod?: string}>> $groups
     * @param array<string, true> $seen
     * @param array<string, mixed> $settings
     */
    private static function collectSeoPages(array &$groups, array &$seen, array $settings): void
    {
        if (! empty($settings['exclude_seo_pages'])) {
            return;
        }

        $posts = get_posts([
            'post_type' => SeoPageManager::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'all',
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ]);

        foreach ($posts as $post) {
            if (! $post instanceof WP_Post) {
                continue;
            }

            $canonicalUrl = SeoPageManager::getCanonicalUrl($post);

            if ($canonicalUrl === '') {
                continue;
            }

            self::addRecord($groups, $seen, 'SEO страницы', [
                'loc' => $canonicalUrl,
                'lastmod' => mysql2date('c', $post->post_modified_gmt ?: $post->post_date_gmt, false),
            ], $settings);
        }
    }

    /**
     * @param array<string, list<array{loc: string, lastmod?: string}>> $groups
     * @param array<string, true> $seen
     * @param array<string, mixed> $settings
     */
    private static function collectVirtualPages(array &$groups, array &$seen, array $settings): void
    {
        $citySlugs = [];

        foreach (CityManager::getCities() as $city) {
            $slug = sanitize_title((string) ($city['slug'] ?? ''));

            if ($slug === '') {
                continue;
            }

            $citySlugs[] = $slug;

            if (! in_array('cities', $settings['exclude_virtual_sources'], true)) {
                self::addRecord($groups, $seen, 'Виртуальные страницы: города', [
                    'loc' => CityManager::getCityUrl($slug),
                ], $settings);
            }
        }

        foreach (self::getCurrencyCodes() as $currencyCode) {
            $code = strtolower($currencyCode);

            if (! in_array('currencies', $settings['exclude_virtual_sources'], true)) {
                self::addRecord($groups, $seen, 'Виртуальные страницы: валюты', [
                    'loc' => home_url('/currency/' . $code . '/'),
                ], $settings);
            }

            foreach ($citySlugs as $citySlug) {
                if (! in_array('city_currencies', $settings['exclude_virtual_sources'], true)) {
                    self::addRecord($groups, $seen, 'Виртуальные страницы: валюты по городам', [
                        'loc' => home_url('/currency/' . $code . '/' . $citySlug . '/'),
                    ], $settings);
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function getCurrencyCodes(): array
    {
        $codes = [];

        foreach (CurrencyManager::getCurrencies() as $currency) {
            $code = strtoupper(trim((string) ($currency['code'] ?? '')));

            if ($code !== '' && $code !== 'RUB') {
                $codes[] = $code;
            }
        }

        if ($codes === []) {
            $codes = self::DEFAULT_CURRENCY_CODES;
        }

        $codes = array_values(array_unique($codes));
        sort($codes);

        return $codes;
    }

    /**
     * @param array<string, list<array{loc: string, lastmod?: string}>> $groups
     * @param array<string, true> $seen
     * @param array{loc: string, lastmod?: string} $record
     * @param array<string, mixed> $settings
     */
    private static function addRecord(array &$groups, array &$seen, string $group, array $record, array $settings): void
    {
        if (($record['loc'] ?? '') === '') {
            return;
        }

        $loc = self::canonicalizeUrlCandidate((string) $record['loc']);

        if ($loc === '') {
            return;
        }

        if (self::isExcludedUrl($loc, $settings)) {
            return;
        }

        $key = self::normalizeUrlKey($loc);

        if (isset($seen[$key])) {
            return;
        }

        $seen[$key] = true;
        $record['loc'] = $loc;
        $groups[$group][] = $record;
    }

    private static function canonicalizeUrlCandidate(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (! preg_match('~^https?://~i', $value)) {
            $value = home_url('/' . ltrim($value, '/'));
        }

        $home = wp_parse_url(home_url('/'));
        $parsed = wp_parse_url($value);

        if (! is_array($home) || ! is_array($parsed) || empty($parsed['host'])) {
            return '';
        }

        if (strcasecmp((string) $parsed['host'], (string) ($home['host'] ?? '')) !== 0) {
            return '';
        }

        $scheme = (string) ($home['scheme'] ?? ($parsed['scheme'] ?? 'https'));
        $host = (string) ($home['host'] ?? $parsed['host']);
        $port = isset($home['port']) ? ':' . (int) $home['port'] : '';
        $path = (string) ($parsed['path'] ?? '/');
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';

        $path = '/' . ltrim($path, '/');
        $path = preg_replace('~/+~', '/', $path) ?: '/';

        if ($path !== '/' && ! preg_match('~/[^/]+\.[a-z0-9]{1,8}$~i', $path)) {
            $path = user_trailingslashit(untrailingslashit($path));
        }

        $url = $scheme . '://' . $host . $port . $path . $query;
        $url = esc_url_raw($url);

        return $url && wp_http_validate_url($url) ? $url : '';
    }

    private static function normalizeUrlKey(string $url): string
    {
        $parsed = wp_parse_url($url);

        if (! is_array($parsed)) {
            return strtolower(trim($url));
        }

        $scheme = strtolower((string) ($parsed['scheme'] ?? 'https'));
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $port = isset($parsed['port']) ? ':' . (int) $parsed['port'] : '';
        $path = (string) ($parsed['path'] ?? '/');
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';

        $path = '/' . ltrim($path, '/');
        $path = preg_replace('~/+~', '/', $path) ?: '/';

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $scheme . '://' . $host . $port . $path . $query;
    }

    private static function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /**
     * @return array<string, mixed>
     */
    private static function getDefaultSettings(): array
    {
        return [
            'exclude_homepage' => false,
            'exclude_seo_pages' => false,
            'exclude_post_types' => ['reviews'],
            'exclude_post_type_archives' => [],
            'exclude_taxonomies' => [],
            'exclude_virtual_sources' => [],
            'excluded_url_rules' => '',
        ];
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private static function sanitizeSettings(array $settings): array
    {
        $defaults = self::getDefaultSettings();
        $sanitized = $defaults;

        $sanitized['exclude_homepage'] = ! empty($settings['exclude_homepage']);
        $sanitized['exclude_seo_pages'] = ! empty($settings['exclude_seo_pages']);
        $sanitized['exclude_post_types'] = self::sanitizeChoiceList(
            $settings['exclude_post_types'] ?? [],
            array_keys(self::getPostTypeChoices())
        );
        $sanitized['exclude_post_type_archives'] = self::sanitizeChoiceList(
            $settings['exclude_post_type_archives'] ?? [],
            array_keys(self::getPostTypeArchiveChoices())
        );
        $sanitized['exclude_taxonomies'] = self::sanitizeChoiceList(
            $settings['exclude_taxonomies'] ?? [],
            array_keys(self::getTaxonomyChoices())
        );
        $sanitized['exclude_virtual_sources'] = self::sanitizeChoiceList(
            $settings['exclude_virtual_sources'] ?? [],
            array_keys(self::getVirtualSourceChoices())
        );
        $sanitized['excluded_url_rules'] = self::sanitizeExcludedUrlRules((string) ($settings['excluded_url_rules'] ?? ''));

        return $sanitized;
    }

    /**
     * @param mixed $values
     * @param list<string> $allowed
     * @return list<string>
     */
    private static function sanitizeChoiceList(mixed $values, array $allowed): array
    {
        $values = is_array($values) ? $values : [];
        $allowedMap = array_fill_keys($allowed, true);
        $sanitized = [];

        foreach ($values as $value) {
            $key = sanitize_key((string) $value);

            if ($key === '' || ! isset($allowedMap[$key])) {
                continue;
            }

            $sanitized[] = $key;
        }

        return array_values(array_unique($sanitized));
    }

    private static function sanitizeExcludedUrlRules(string $raw): string
    {
        $lines = preg_split('/\R/u', $raw) ?: [];
        $rules = [];
        $seen = [];

        foreach ($lines as $line) {
            $rule = trim((string) $line);

            if ($rule === '' || str_starts_with($rule, '#')) {
                continue;
            }

            if (! preg_match('~\*$~', $rule) && ! preg_match('~^https?://~i', $rule) && ! str_starts_with($rule, '/')) {
                $rule = '/' . ltrim($rule, '/');
            }

            if (isset($seen[$rule])) {
                continue;
            }

            $seen[$rule] = true;
            $rules[] = $rule;
        }

        return implode("\n", $rules);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function isExcludedUrl(string $url, array $settings): bool
    {
        $rules = preg_split('/\R/u', (string) ($settings['excluded_url_rules'] ?? '')) ?: [];

        if ($rules === []) {
            return false;
        }

        $normalizedUrl = self::normalizeUrlKey($url);

        foreach ($rules as $rawRule) {
            $rule = trim((string) $rawRule);

            if ($rule === '') {
                continue;
            }

            $isPrefix = str_ends_with($rule, '*');

            if ($isPrefix) {
                $rule = rtrim(substr($rule, 0, -1));
            }

            $candidate = self::canonicalizeUrlCandidate($rule);

            if ($candidate === '') {
                continue;
            }

            $normalizedRule = self::normalizeUrlKey($candidate);

            if ($isPrefix && str_starts_with($normalizedUrl, $normalizedRule)) {
                return true;
            }

            if (! $isPrefix && $normalizedUrl === $normalizedRule) {
                return true;
            }
        }

        return false;
    }
}
