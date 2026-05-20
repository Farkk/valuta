<?php

declare(strict_types=1);

namespace Theme\Helpers;

use Theme\Banks\BankManager;
use Theme\SEO\SeoPageManager;

final class Breadcrumbs
{
    public static function shouldRender(): bool
    {
        return ! is_front_page() || SeoPageManager::hasCurrentPage();
    }

    /**
     * @return list<array{title: string, url: string}>
     */
    public static function get(): array
    {
        if (! self::shouldRender()) {
            return [];
        }

        $breadcrumbs = [
            [
                'title' => __('Главная', 'theme'),
                'url' => home_url('/'),
            ],
        ];

        if (SeoPageManager::hasCurrentPage()) {
            $breadcrumbs[] = [
                'title' => (string) SeoPageManager::getCurrentValue('h1', get_bloginfo('name')),
                'url' => '',
            ];

            return $breadcrumbs;
        }

        if (BankManager::hasCurrentBank()) {
            $bank = BankManager::getCurrentBank();
            $breadcrumbs[] = [
                'title' => __('Банки', 'theme'),
                'url' => home_url('/'),
            ];
            $breadcrumbs[] = [
                'title' => (string) ($bank['bank_name'] ?? ''),
                'url' => '',
            ];

            return $breadcrumbs;
        }

        if (is_post_type_archive('news')) {
            $breadcrumbs[] = [
                'title' => __('Новости', 'theme'),
                'url' => PostTypeContent::getArchiveUrl('news'),
            ];

            return $breadcrumbs;
        }

        if (is_tax('news_tag')) {
            $term = get_queried_object();
            $breadcrumbs[] = [
                'title' => __('Новости', 'theme'),
                'url' => PostTypeContent::getArchiveUrl('news'),
            ];

            if ($term instanceof \WP_Term) {
                $link = get_term_link($term);
                $breadcrumbs[] = [
                    'title' => $term->name,
                    'url' => is_wp_error($link) ? '' : (string) $link,
                ];
            }

            return $breadcrumbs;
        }

        if (is_singular('news')) {
            $breadcrumbs[] = [
                'title' => __('Новости', 'theme'),
                'url' => PostTypeContent::getArchiveUrl('news'),
            ];
            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => (string) get_permalink(),
            ];

            return $breadcrumbs;
        }

        if (is_post_type_archive('articles')) {
            $breadcrumbs[] = [
                'title' => __('Статьи', 'theme'),
                'url' => PostTypeContent::getArchiveUrl('articles'),
            ];

            return $breadcrumbs;
        }

        if (is_tax('articles_tag')) {
            $term = get_queried_object();
            $breadcrumbs[] = [
                'title' => __('Статьи', 'theme'),
                'url' => PostTypeContent::getArchiveUrl('articles'),
            ];

            if ($term instanceof \WP_Term) {
                $link = get_term_link($term);
                $breadcrumbs[] = [
                    'title' => $term->name,
                    'url' => is_wp_error($link) ? '' : (string) $link,
                ];
            }

            return $breadcrumbs;
        }

        if (is_singular('articles')) {
            $breadcrumbs[] = [
                'title' => __('Статьи', 'theme'),
                'url' => PostTypeContent::getArchiveUrl('articles'),
            ];
            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => (string) get_permalink(),
            ];

            return $breadcrumbs;
        }

        if (is_post_type_archive('reviews')) {
            $breadcrumbs[] = [
                'title' => __('Отзывы', 'theme'),
                'url' => (string) (get_post_type_archive_link('reviews') ?: home_url('/reviews/')),
            ];

            return $breadcrumbs;
        }

        if (is_singular('reviews')) {
            $breadcrumbs[] = [
                'title' => __('Отзывы', 'theme'),
                'url' => (string) (get_post_type_archive_link('reviews') ?: home_url('/reviews/')),
            ];
            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => (string) get_permalink(),
            ];

            return $breadcrumbs;
        }

        if (is_page()) {
            $ancestors = array_reverse(get_post_ancestors(get_the_ID()));

            foreach ($ancestors as $ancestorId) {
                $breadcrumbs[] = [
                    'title' => get_the_title($ancestorId),
                    'url' => (string) get_permalink($ancestorId),
                ];
            }

            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => (string) get_permalink(),
            ];

            return $breadcrumbs;
        }

        if (is_category()) {
            $category = get_queried_object();

            if ($category instanceof \WP_Term) {
                $breadcrumbs[] = [
                    'title' => $category->name,
                    'url' => (string) get_category_link($category->term_id),
                ];
            }

            return $breadcrumbs;
        }

        if (is_single()) {
            $categories = get_the_category();

            if (! empty($categories)) {
                $category = $categories[0];
                $breadcrumbs[] = [
                    'title' => $category->name,
                    'url' => (string) get_category_link($category->term_id),
                ];
            }

            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => (string) get_permalink(),
            ];

            return $breadcrumbs;
        }

        if (is_search()) {
            $breadcrumbs[] = [
                'title' => sprintf(__('Результаты поиска: %s', 'theme'), get_search_query()),
                'url' => '',
            ];

            return $breadcrumbs;
        }

        if (is_404()) {
            $breadcrumbs[] = [
                'title' => __('Страница не найдена', 'theme'),
                'url' => '',
            ];
        }

        return $breadcrumbs;
    }
}
