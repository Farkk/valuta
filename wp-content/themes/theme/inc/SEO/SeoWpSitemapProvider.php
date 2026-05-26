<?php

declare(strict_types=1);

namespace Theme\SEO;

final class SeoWpSitemapProvider
{
    public static function register(): void
    {
        add_action('init', [self::class, 'registerProvider'], 20);
    }

    public static function registerProvider(): void
    {
        if (
            ! function_exists('wp_sitemaps_get_server')
            || ! class_exists(\WP_Sitemaps_Provider::class)
            || ! class_exists(ThemeSeoWpSitemapProvider::class)
        ) {
            return;
        }

        $server = wp_sitemaps_get_server();

        if (! $server || empty($server->registry)) {
            return;
        }

        $server->registry->add_provider('themeseopages', new ThemeSeoWpSitemapProvider());
    }
}

if (class_exists(\WP_Sitemaps_Provider::class)) {
    final class ThemeSeoWpSitemapProvider extends \WP_Sitemaps_Provider
    {
        private const PER_PAGE = 2000;

        public function __construct()
        {
            $this->name = 'themeseopages';
            $this->object_type = 'seo_page';
        }

        public function get_url_list($page_num, $object_subtype = '')
        {
            unset($object_subtype);

            $records = SeoSitemapManager::getActiveRecords();
            $offset = (max(1, (int) $page_num) - 1) * self::PER_PAGE;
            $pageRecords = array_slice($records, $offset, self::PER_PAGE);

            return array_map(static function (array $record): array {
                $url = [
                    'loc' => (string) ($record['loc'] ?? ''),
                ];

                if (! empty($record['lastmod'])) {
                    $url['lastmod'] = (string) $record['lastmod'];
                }

                return $url;
            }, $pageRecords);
        }

        public function get_max_num_pages($object_subtype = '')
        {
            unset($object_subtype);

            $count = count(SeoSitemapManager::getActiveRecords());

            return max(1, (int) ceil(max(1, $count) / self::PER_PAGE));
        }
    }
}
