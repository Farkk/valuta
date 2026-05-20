<?php

declare(strict_types=1);

namespace Theme\SEO;

final class SeoPagePostType
{
    public static function register(): void
    {
        add_action('init', [self::class, 'registerPostType']);
        add_filter('manage_' . SeoPageManager::POST_TYPE . '_posts_columns', [self::class, 'columns']);
        add_action('manage_' . SeoPageManager::POST_TYPE . '_posts_custom_column', [self::class, 'columnContent'], 10, 2);
    }

    public static function registerPostType(): void
    {
        register_post_type(SeoPageManager::POST_TYPE, [
            'labels' => [
                'name' => 'SEO страницы',
                'singular_name' => 'SEO страница',
                'menu_name' => 'SEO страницы',
                'add_new_item' => 'Добавить SEO страницу',
                'edit_item' => 'Редактировать SEO страницу',
                'not_found' => 'SEO страницы не найдены',
            ],
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'menu_position' => 26,
            'menu_icon' => 'dashicons-chart-area',
            'supports' => ['title'],
            'show_in_rest' => true,
        ]);
    }

    /**
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public static function columns(array $columns): array
    {
        $newColumns = [];

        foreach ($columns as $key => $label) {
            $newColumns[$key] = $label;

            if ($key === 'title') {
                $newColumns['seo_url'] = 'URL';
                $newColumns['seo_type'] = 'Тип';
                $newColumns['seo_query'] = 'Запрос';
            }
        }

        return $newColumns;
    }

    public static function columnContent(string $column, int $postId): void
    {
        if ($column === 'seo_url') {
            $url = (string) get_post_meta($postId, SeoPageManager::META_URL_NORMALIZED, true);
            $link = $url !== '' ? home_url(user_trailingslashit(ltrim($url, '/'))) : '';
            echo $link !== '' ? '<a href="' . esc_url($link) . '" target="_blank" rel="noopener">' . esc_html($url) . '</a>' : '';
            return;
        }

        if ($column === 'seo_type') {
            echo esc_html((string) get_post_meta($postId, SeoPageManager::META_TYPE, true));
            return;
        }

        if ($column === 'seo_query') {
            echo esc_html((string) get_post_meta($postId, SeoPageManager::META_QUERY, true));
        }
    }
}
