<?php

declare(strict_types=1);

namespace Theme\Helpers;

use WP_Post;

final class News
{
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
    public static function getNewsCardData(WP_Post $post): array
    {
        return PostTypeContent::getCardData($post);
    }

    /**
     * @return array{slug: string, name: string, url: string, count: int}[]
     */
    public static function getCategoryTabs(): array
    {
        return PostTypeContent::getCategoryTabs('news');
    }

    public static function incrementViews(int $postId): int
    {
        return PostTypeContent::incrementViews($postId);
    }

    /**
     * @param list<int> $exclude
     *
     * @return list<WP_Post>
     */
    public static function getPopularPosts(int $limit = 4, array $exclude = []): array
    {
        return PostTypeContent::getPopularPosts('news', $limit, $exclude);
    }

    public static function getReadingTimeMinutes(WP_Post $post): int
    {
        return PostTypeContent::getReadingTimeMinutes($post);
    }
}
