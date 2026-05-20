<?php

declare(strict_types=1);

namespace Theme\Content;

use Theme\Core\Contracts\ServiceProvider;

final class ContentRegistrar implements ServiceProvider
{
    private const REWRITE_OPTION = 'theme_content_rewrite_version';

    public function register(): void
    {
        add_action('init', [$this, 'registerPostTypes']);
        add_action('init', [$this, 'registerTaxonomies']);
        add_action('init', [$this, 'maybeFlushRewriteRules'], 20);
        add_action('after_switch_theme', [$this, 'flushRewriteRules']);
    }

    public function registerPostTypes(): void
    {
        register_post_type('news', [
            'labels' => [
                'name' => __('Новости', 'theme'),
                'singular_name' => __('Новость', 'theme'),
                'menu_name' => __('Новости', 'theme'),
                'add_new' => __('Добавить новость', 'theme'),
                'add_new_item' => __('Добавить новость', 'theme'),
                'edit_item' => __('Редактировать новость', 'theme'),
                'new_item' => __('Новая новость', 'theme'),
                'view_item' => __('Посмотреть новость', 'theme'),
                'all_items' => __('Все новости', 'theme'),
                'search_items' => __('Искать новости', 'theme'),
                'not_found' => __('Новости не найдены.', 'theme'),
                'not_found_in_trash' => __('Новости не найдены в корзине.', 'theme'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'news'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'],
            'show_in_rest' => true,
        ]);

        register_post_type('articles', [
            'labels' => [
                'name' => __('Статьи', 'theme'),
                'singular_name' => __('Статья', 'theme'),
                'menu_name' => __('Статьи', 'theme'),
                'add_new' => __('Добавить статью', 'theme'),
                'add_new_item' => __('Добавить статью', 'theme'),
                'edit_item' => __('Редактировать статью', 'theme'),
                'new_item' => __('Новая статья', 'theme'),
                'view_item' => __('Посмотреть статью', 'theme'),
                'all_items' => __('Все статьи', 'theme'),
                'search_items' => __('Искать статьи', 'theme'),
                'not_found' => __('Статьи не найдены.', 'theme'),
                'not_found_in_trash' => __('Статьи не найдены в корзине.', 'theme'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'articles'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 6,
            'menu_icon' => 'dashicons-media-document',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'],
            'show_in_rest' => true,
        ]);

        register_post_type('reviews', [
            'labels' => [
                'name' => __('Отзывы', 'theme'),
                'singular_name' => __('Отзыв', 'theme'),
                'menu_name' => __('Отзывы', 'theme'),
                'add_new' => __('Добавить отзыв', 'theme'),
                'add_new_item' => __('Добавить отзыв', 'theme'),
                'edit_item' => __('Редактировать отзыв', 'theme'),
                'new_item' => __('Новый отзыв', 'theme'),
                'view_item' => __('Посмотреть отзыв', 'theme'),
                'all_items' => __('Все отзывы', 'theme'),
                'search_items' => __('Искать отзывы', 'theme'),
                'not_found' => __('Отзывы не найдены.', 'theme'),
                'not_found_in_trash' => __('Отзывы не найдены в корзине.', 'theme'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'reviews'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 7,
            'menu_icon' => 'dashicons-star-filled',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions', 'comments'],
            'show_in_rest' => true,
        ]);
    }

    public function registerTaxonomies(): void
    {
        register_taxonomy('news_tag', 'news', [
            'labels' => [
                'name' => __('Теги новостей', 'theme'),
                'singular_name' => __('Тег новости', 'theme'),
                'menu_name' => __('Теги', 'theme'),
                'all_items' => __('Все теги', 'theme'),
                'edit_item' => __('Редактировать тег', 'theme'),
                'view_item' => __('Посмотреть тег', 'theme'),
                'update_item' => __('Обновить тег', 'theme'),
                'add_new_item' => __('Добавить тег', 'theme'),
                'new_item_name' => __('Новый тег', 'theme'),
                'search_items' => __('Искать теги', 'theme'),
                'not_found' => __('Не найдено', 'theme'),
            ],
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'news/category',
                'with_front' => false,
            ],
        ]);

        register_taxonomy('articles_tag', 'articles', [
            'labels' => [
                'name' => __('Теги статей', 'theme'),
                'singular_name' => __('Тег статьи', 'theme'),
                'menu_name' => __('Теги', 'theme'),
                'all_items' => __('Все теги', 'theme'),
                'edit_item' => __('Редактировать тег', 'theme'),
                'view_item' => __('Посмотреть тег', 'theme'),
                'update_item' => __('Обновить тег', 'theme'),
                'add_new_item' => __('Добавить тег', 'theme'),
                'new_item_name' => __('Новый тег', 'theme'),
                'search_items' => __('Искать теги', 'theme'),
                'not_found' => __('Не найдено', 'theme'),
            ],
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'articles/category',
                'with_front' => false,
            ],
        ]);
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
        $this->registerPostTypes();
        $this->registerTaxonomies();
        flush_rewrite_rules(false);
    }
}
