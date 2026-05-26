<?php

declare(strict_types=1);

namespace Theme\SEO;

use WP_Post;

final class SeoPageManager
{
    public const POST_TYPE = 'seo_page';
    public const META_TYPE = 'seo_page_type';
    public const META_QUERY = 'seo_query';
    public const META_URL = 'seo_url';
    public const META_URL_NORMALIZED = 'seo_url_normalized';
    public const META_TITLE = 'seo_title';
    public const META_DESCRIPTION = 'seo_description';
    public const META_H1 = 'seo_h1';
    public const META_H2 = 'seo_h2';
    public const META_H2_BOTTOM = 'seo_h2_bottom';
    public const META_TEXT = 'seo_text';
    public const META_CURRENCY_CODE = 'seo_currency_code';
    public const META_CURRENCY_SLUG = 'seo_currency_slug';
    public const META_CITY_SLUG = 'seo_city_slug';
    public const META_AMOUNT = 'seo_amount';
    public const META_BANK_SLUG = 'seo_bank_slug';
    public const META_BANK_CODE = 'seo_bank_code';
    public const META_IMPORT_BATCH = 'seo_import_batch';

    private static ?WP_Post $currentPage = null;

    /**
     * @var array<string, mixed>
     */
    private static array $currentRendered = [];
    private static bool $syncingPost = false;

    public static function register(): void
    {
        add_action('save_post_' . self::POST_TYPE, [self::class, 'syncPageMeta'], 20, 3);
        add_action('acf/save_post', [self::class, 'syncAcfPageMeta'], 20);
        add_filter('pre_get_document_title', [self::class, 'filterDocumentTitle'], 99);
    }

    public static function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '/';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $path = $path !== '' ? $path : $url;
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('~/+~', '/', $path) ?: '/';
        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }

    public static function slugForUrl(string $url): string
    {
        return 'seo-' . md5(self::normalizeUrl($url));
    }

    public static function getPageByUrl(string $url): ?WP_Post
    {
        $normalized = self::normalizeUrl($url);
        $post = get_page_by_path(self::slugForUrl($normalized), OBJECT, self::POST_TYPE);

        if ($post instanceof WP_Post && $post->post_status === 'publish') {
            return $post;
        }

        $posts = get_posts([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'all',
            'meta_query' => [
                [
                    'key' => self::META_URL_NORMALIZED,
                    'value' => $normalized,
                    'compare' => '=',
                ],
            ],
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ]);

        return $posts[0] ?? null;
    }

    public static function setCurrentPage(WP_Post $post): void
    {
        self::$currentPage = $post;
        self::$currentRendered = self::renderPageData($post);
    }

    public static function hasCurrentPage(): bool
    {
        return self::$currentPage instanceof WP_Post;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getCurrentData(): array
    {
        return self::$currentRendered;
    }

    public static function getCurrentValue(string $key, string $fallback = ''): string
    {
        return (string) (self::$currentRendered[$key] ?? $fallback);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getCurrentVariables(): array
    {
        $variables = self::$currentRendered['variables'] ?? [];

        return is_array($variables) ? $variables : [];
    }

    public static function getCurrentType(): string
    {
        return self::getCurrentValue('type');
    }

    public static function getCurrentCurrencyCode(): string
    {
        return strtolower((string) (self::getCurrentVariables()['currency_code'] ?? 'usd'));
    }

    public static function getCurrentCitySlug(): string
    {
        return (string) (self::getCurrentVariables()['city_slug'] ?? '');
    }

    public static function getCurrentAmount(): int
    {
        return (int) (self::getCurrentVariables()['amount'] ?? 0);
    }

    public static function getCurrentBankCode(): string
    {
        return (string) (self::getCurrentVariables()['bank_code'] ?? '');
    }

    public static function getCanonicalUrl(?WP_Post $post = null): string
    {
        $post = $post ?: self::$currentPage;

        if (! $post) {
            return '';
        }

        $path = (string) get_post_meta($post->ID, self::META_URL_NORMALIZED, true);

        if ($path === '') {
            $path = self::normalizeUrl((string) get_post_meta($post->ID, self::META_URL, true));
        }

        return $path === '' || $path === '/' ? '' : home_url(user_trailingslashit(ltrim($path, '/')));
    }

    /**
     * @return array<string, mixed>
     */
    public static function renderPageData(WP_Post $post): array
    {
        $url = self::normalizeUrl((string) get_post_meta($post->ID, self::META_URL, true) ?: '/' . $post->post_name);
        $type = SeoTemplateRenderer::normalizeType((string) get_post_meta($post->ID, self::META_TYPE, true));

        if ($type === '') {
            $type = SeoTemplateRenderer::detectTypeFromUrl($url);
        }

        $variables = SeoTemplateRenderer::inferVariables($type, $url, [
            'currency_code' => strtolower((string) get_post_meta($post->ID, self::META_CURRENCY_CODE, true)),
            'currency_slug' => (string) get_post_meta($post->ID, self::META_CURRENCY_SLUG, true),
            'city_slug' => (string) get_post_meta($post->ID, self::META_CITY_SLUG, true),
            'amount' => (int) get_post_meta($post->ID, self::META_AMOUNT, true),
            'bank_slug' => (string) get_post_meta($post->ID, self::META_BANK_SLUG, true),
        ]);

        $rendered = [
            'id' => $post->ID,
            'type' => $type,
            'url' => $url,
            'canonical' => self::getCanonicalUrl($post),
            'variables' => $variables,
        ];

        $fields = [
            'title' => self::META_TITLE,
            'description' => self::META_DESCRIPTION,
            'h1' => self::META_H1,
            'h2' => self::META_H2,
            'h2_bottom' => self::META_H2_BOTTOM,
            'seo_text' => self::META_TEXT,
        ];

        foreach ($fields as $key => $metaKey) {
            $template = (string) get_post_meta($post->ID, $metaKey, true);

            if ($template === '' && $key !== 'seo_text') {
                $template = SeoTemplateRenderer::getDefaultTemplate($type, $key);
            }

            $rendered[$key] = $key === 'seo_text' && $template === ''
                ? SeoTemplateRenderer::getDefaultSeoText($type, $url, $variables)
                : SeoTemplateRenderer::render($template, $variables);
        }

        return $rendered;
    }

    public static function syncPageMeta(int $postId, WP_Post $post, bool $update): void
    {
        unset($update);

        if (self::$syncingPost || wp_is_post_revision($postId) || $post->post_type !== self::POST_TYPE) {
            return;
        }

        $url = self::normalizeUrl((string) get_post_meta($postId, self::META_URL, true));

        if ($url === '/') {
            return;
        }

        $type = SeoTemplateRenderer::normalizeType((string) get_post_meta($postId, self::META_TYPE, true));

        if ($type === '') {
            $type = SeoTemplateRenderer::detectTypeFromUrl($url);
            update_post_meta($postId, self::META_TYPE, $type);
        }

        $variables = SeoTemplateRenderer::inferVariables($type, $url, [
            'currency_code' => strtolower((string) get_post_meta($postId, self::META_CURRENCY_CODE, true)),
            'currency_slug' => (string) get_post_meta($postId, self::META_CURRENCY_SLUG, true),
            'city_slug' => (string) get_post_meta($postId, self::META_CITY_SLUG, true),
            'amount' => (int) get_post_meta($postId, self::META_AMOUNT, true),
            'bank_slug' => (string) get_post_meta($postId, self::META_BANK_SLUG, true),
        ]);

        update_post_meta($postId, self::META_URL_NORMALIZED, $url);
        update_post_meta($postId, self::META_CURRENCY_CODE, strtolower((string) ($variables['currency_code'] ?? '')));
        update_post_meta($postId, self::META_CURRENCY_SLUG, (string) ($variables['currency_slug'] ?? ''));
        update_post_meta($postId, self::META_CITY_SLUG, (string) ($variables['city_slug'] ?? ''));
        update_post_meta($postId, self::META_AMOUNT, (int) ($variables['amount'] ?? 0));
        update_post_meta($postId, self::META_BANK_SLUG, (string) ($variables['bank_slug'] ?? ''));
        update_post_meta($postId, self::META_BANK_CODE, (string) ($variables['bank_code'] ?? ''));

        $targetSlug = self::slugForUrl($url);

        if ($post->post_name !== $targetSlug) {
            self::$syncingPost = true;
            wp_update_post(['ID' => $postId, 'post_name' => $targetSlug]);
            self::$syncingPost = false;
        }
    }

    public static function syncAcfPageMeta(mixed $postId): void
    {
        if (! is_numeric($postId)) {
            return;
        }

        $post = get_post((int) $postId);

        if ($post instanceof WP_Post && $post->post_type === self::POST_TYPE) {
            self::syncPageMeta((int) $postId, $post, true);
        }
    }

    public static function filterDocumentTitle(string $title): string
    {
        return self::hasCurrentPage() ? self::getCurrentValue('title', $title) : $title;
    }

    /**
     * @param array{type?: string, query?: string, url?: string, title?: string, description?: string, h1?: string, h2?: string, seo_text?: string, h2_bottom?: string} $row
     * @return array{status: string, message?: string, post_id?: int, url?: string}
     */
    public static function upsertFromImportRow(array $row, string $batchId = ''): array
    {
        $type = SeoTemplateRenderer::normalizeType((string) ($row['type'] ?? ''));
        $url = self::normalizeUrl((string) ($row['url'] ?? ''));

        if ($url === '/') {
            return [
                'status' => 'error',
                'message' => 'Не указан URL.',
            ];
        }

        if ($type === '') {
            $type = SeoTemplateRenderer::detectTypeFromUrl($url);
        }

        $existing = self::getPageByUrl($url);
        $query = (string) ($row['query'] ?? '');
        $h1 = (string) ($row['h1'] ?? '');
        $title = (string) ($row['title'] ?? '');

        $postData = [
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => sanitize_text_field($query !== '' ? $query : ($h1 !== '' ? $h1 : ($title !== '' ? $title : $url))),
            'post_name' => self::slugForUrl($url),
        ];

        if ($existing instanceof WP_Post) {
            $postData['ID'] = $existing->ID;
            $postId = wp_update_post(wp_slash($postData), true);
            $created = false;
        } else {
            $postId = wp_insert_post(wp_slash($postData), true);
            $created = true;
        }

        if (is_wp_error($postId)) {
            return [
                'status' => 'error',
                'message' => $postId->get_error_message(),
            ];
        }

        $variables = SeoTemplateRenderer::inferVariables($type, $url);

        $meta = [
            self::META_TYPE => $type,
            self::META_QUERY => sanitize_text_field((string) ($row['query'] ?? '')),
            self::META_URL => $url,
            self::META_URL_NORMALIZED => $url,
            self::META_TITLE => sanitize_text_field((string) ($row['title'] ?? '')),
            self::META_DESCRIPTION => sanitize_text_field((string) ($row['description'] ?? '')),
            self::META_H1 => sanitize_text_field((string) ($row['h1'] ?? '')),
            self::META_H2 => sanitize_text_field((string) ($row['h2'] ?? '')),
            self::META_H2_BOTTOM => sanitize_text_field((string) ($row['h2_bottom'] ?? '')),
            self::META_TEXT => wp_kses_post((string) ($row['seo_text'] ?? '')),
            self::META_CURRENCY_CODE => strtolower((string) ($variables['currency_code'] ?? '')),
            self::META_CURRENCY_SLUG => (string) ($variables['currency_slug'] ?? ''),
            self::META_CITY_SLUG => (string) ($variables['city_slug'] ?? ''),
            self::META_AMOUNT => (int) ($variables['amount'] ?? 0),
            self::META_BANK_SLUG => (string) ($variables['bank_slug'] ?? ''),
            self::META_BANK_CODE => (string) ($variables['bank_code'] ?? ''),
            self::META_IMPORT_BATCH => $batchId,
        ];

        foreach ($meta as $key => $value) {
            update_post_meta((int) $postId, $key, $value);
        }

        return [
            'status' => $created ? 'created' : 'updated',
            'post_id' => (int) $postId,
            'url' => $url,
        ];
    }
}
