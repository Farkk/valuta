<?php

declare(strict_types=1);

namespace Theme\Helpers;

/**
 * URL пунктов навигации (шапка, fallback-меню).
 */
final class MenuLinks
{
    private static ?string $aboutPageUrl = null;

    public static function postTypeArchiveUrl(string $postType): string
    {
        $link = get_post_type_archive_link($postType);

        $fallbacks = [
            'news' => '/news/',
            'articles' => '/articles/',
            'reviews' => '/reviews/',
        ];

        if (is_string($link) && $link !== '') {
            return $link;
        }

        return home_url($fallbacks[$postType] ?? '/');
    }

    public static function aboutPageUrl(): string
    {
        if (self::$aboutPageUrl !== null) {
            return self::$aboutPageUrl;
        }

        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'meta_key'       => '_wp_page_template',
                'meta_value'     => 'page-templates/about.php',
                'fields'         => 'ids',
            ]
        );

        if ($pages !== []) {
            self::$aboutPageUrl = (string) get_permalink((int) $pages[0]);

            return self::$aboutPageUrl;
        }

        foreach (['o-proekte', 'about', 'o-nas'] as $slug) {
            $page = get_page_by_path($slug);

            if ($page instanceof \WP_Post && $page->post_status === 'publish') {
                self::$aboutPageUrl = (string) get_permalink($page);

                return self::$aboutPageUrl;
            }
        }

        self::$aboutPageUrl = home_url('/');

        return self::$aboutPageUrl;
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function headerItems(): array
    {
        return [
            [
                'label' => __('Статьи', 'theme'),
                'url'   => self::postTypeArchiveUrl('articles'),
            ],
            [
                'label' => __('Новости', 'theme'),
                'url'   => self::postTypeArchiveUrl('news'),
            ],
            [
                'label' => __('Отзывы', 'theme'),
                'url'   => self::postTypeArchiveUrl('reviews'),
            ],
            [
                'label' => __('О проекте', 'theme'),
                'url'   => self::aboutPageUrl(),
            ],
        ];
    }

    /**
     * Подставляет корректные URL в меню шапки из админки WordPress.
     *
     * @param list<\WP_Post> $items
     * @param \stdClass|array<string, mixed> $args
     *
     * @return list<\WP_Post>
     */
    public static function normalizeHeaderMenuItems(array $items, $args): array
    {
        $themeLocation = '';

        if (is_object($args) && isset($args->theme_location)) {
            $themeLocation = (string) $args->theme_location;
        } elseif (is_array($args) && isset($args['theme_location'])) {
            $themeLocation = (string) $args['theme_location'];
        }

        if ($themeLocation !== 'header') {
            return $items;
        }

        $urlByTitle = [];

        foreach (self::headerItems() as $headerItem) {
            $urlByTitle[mb_strtolower($headerItem['label'])] = $headerItem['url'];
        }

        foreach ($items as $item) {
            if (! $item instanceof \WP_Post) {
                continue;
            }

            if ($item->type === 'post_type_archive' && in_array($item->object, ['news', 'articles', 'reviews'], true)) {
                $item->url = self::postTypeArchiveUrl((string) $item->object);

                continue;
            }

            if ($item->type === 'post_type' && $item->object === 'page') {
                $template = (string) get_page_template_slug((int) $item->object_id);

                if ($template === 'page-templates/about.php') {
                    $item->url = self::aboutPageUrl();
                }

                continue;
            }

            $titleKey = mb_strtolower(trim(wp_strip_all_tags((string) $item->title)));

            if ($titleKey !== '' && isset($urlByTitle[$titleKey])) {
                $item->url = $urlByTitle[$titleKey];
            }
        }

        return $items;
    }
}
