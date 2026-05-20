<?php

declare(strict_types=1);

namespace Theme\Helpers;

use WP_Post;

final class PostTypeContent
{
    private const READING_WORDS_PER_MINUTE = 200;

    /**
     * @var array<string, array<string, string>>
     */
    private const TYPES = [
        'news' => [
            'post_type' => 'news',
            'taxonomy' => 'news_tag',
            'category_prefix' => 'news/category',
            'archive_path' => '/news/',
            'archive_title' => 'Новости',
            'popular_title' => 'Популярные новости',
            'tabs_aria_label' => 'Категории новостей',
            'empty_message' => 'Новостей пока нет.',
            'ajax_action' => 'load_more_news',
        ],
        'articles' => [
            'post_type' => 'articles',
            'taxonomy' => 'articles_tag',
            'category_prefix' => 'articles/category',
            'archive_path' => '/articles/',
            'archive_title' => 'Статьи',
            'popular_title' => 'Популярные статьи',
            'tabs_aria_label' => 'Категории статей',
            'empty_message' => 'Статей пока нет.',
            'ajax_action' => 'load_more_articles',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function getConfig(string $type): array
    {
        return self::TYPES[$type] ?? self::TYPES['news'];
    }

    public static function isSupported(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    /**
     * @return list<array<string, string>>
     */
    public static function getRoutedConfigs(): array
    {
        return array_values(self::TYPES);
    }

    public static function getArchiveUrl(string $type): string
    {
        $config = self::getConfig($type);

        return (string) (get_post_type_archive_link($config['post_type']) ?: home_url($config['archive_path']));
    }

    /**
     * @return array{
     *     title: string,
     *     url: string,
     *     date: string,
     *     date_iso: string,
     *     views: int,
     *     image_url: string,
     *     image_alt: string
     * }
     */
    public static function getCardData(WP_Post $post): array
    {
        $imageId = get_post_thumbnail_id($post);
        $image = $imageId ? wp_get_attachment_image_src($imageId, 'medium') : null;

        return [
            'title' => get_the_title($post),
            'url' => get_permalink($post),
            'date' => get_the_date('d.m.Y', $post),
            'date_iso' => get_the_date('Y-m-d', $post),
            'views' => (int) get_post_meta($post->ID, 'views', true),
            'image_url' => $image[0] ?? '',
            'image_alt' => get_the_title($post),
        ];
    }

    /**
     * @return array{slug: string, name: string, url: string, count: int}[]
     */
    public static function getCategoryTabs(string $type): array
    {
        $config = self::getConfig($type);
        $terms = get_terms([
            'taxonomy' => $config['taxonomy'],
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        if (is_wp_error($terms) || ! is_array($terms)) {
            return [];
        }

        $tabs = [];

        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $link = get_term_link($term);

            if (is_wp_error($link)) {
                continue;
            }

            $tabs[] = [
                'slug' => $term->slug,
                'name' => $term->name,
                'url' => (string) $link,
                'count' => (int) $term->count,
            ];
        }

        return $tabs;
    }

    public static function incrementViews(int $postId): int
    {
        $views = (int) get_post_meta($postId, 'views', true);
        $views++;
        update_post_meta($postId, 'views', $views);

        return $views;
    }

    /**
     * @param list<int> $exclude
     *
     * @return list<WP_Post>
     */
    public static function getPopularPosts(string $type, int $limit = 4, array $exclude = []): array
    {
        $config = self::getConfig($type);
        $exclude = array_values(array_filter(array_map('intval', $exclude)));
        $posts = [];
        $postIds = [];

        $popularQuery = new \WP_Query([
            'post_type' => $config['post_type'],
            'posts_per_page' => $limit + count($exclude),
            'post_status' => 'publish',
            'meta_key' => 'views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'post__not_in' => $exclude,
            'meta_query' => [
                [
                    'key' => 'views',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ],
            ],
        ]);

        if ($popularQuery->have_posts()) {
            while ($popularQuery->have_posts() && count($posts) < $limit) {
                $popularQuery->the_post();
                $post = get_post();

                if ($post instanceof WP_Post) {
                    $posts[] = $post;
                    $postIds[] = $post->ID;
                }
            }

            wp_reset_postdata();
        }

        if (count($posts) >= $limit) {
            return $posts;
        }

        $remaining = $limit - count($posts);
        $randomQuery = new \WP_Query([
            'post_type' => $config['post_type'],
            'posts_per_page' => $remaining,
            'post_status' => 'publish',
            'orderby' => 'rand',
            'post__not_in' => array_merge($postIds, $exclude),
        ]);

        if ($randomQuery->have_posts()) {
            while ($randomQuery->have_posts()) {
                $randomQuery->the_post();
                $post = get_post();

                if ($post instanceof WP_Post) {
                    $posts[] = $post;
                }
            }

            wp_reset_postdata();
        }

        return $posts;
    }

    public static function getReadingTimeMinutes(WP_Post $post): int
    {
        $content = (string) get_post_field('post_content', $post);
        $text = wp_strip_all_tags($content);
        $wordCount = str_word_count($text, 0, 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ');

        if ($wordCount <= 0) {
            return 1;
        }

        return max(1, (int) ceil($wordCount / self::READING_WORDS_PER_MINUTE));
    }
}
