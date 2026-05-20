<?php

declare(strict_types=1);

namespace Theme\Helpers;

use Theme\Cities\CityManager;
use WP_Post;

final class Reviews
{
    /**
     * @return array{name: string, text: string, full_text: string, date: string, bank: string, city: string, rating: int}
     */
    public static function getReviewCardData(WP_Post $post, string $fallbackCity = ''): array
    {
        $city = self::getReviewField('review_city', $post->ID);

        if ($city === '') {
            $city = $fallbackCity !== '' ? $fallbackCity : (string) (CityManager::getCurrentCity()['city_name'] ?? '');
        }

        $content = wp_strip_all_tags((string) $post->post_content);

        $text = get_the_excerpt($post);
        if ($text === '') {
            $text = wp_trim_words($content, 32);
        }

        $fullText = trim($content);

        $rating = (int) self::getReviewField('review_rating', $post->ID);
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }

        $date = self::getReviewField('review_date', $post->ID);
        if ($date === '') {
            $date = get_the_date('d.m.Y', $post);
        }

        return [
            'name' => self::getReviewField('review_author_name', $post->ID) ?: __('Аноним', 'theme'),
            'text' => $text,
            'full_text' => $fullText !== '' ? $fullText : trim($text),
            'date' => $date,
            'bank' => self::getReviewField('review_bank', $post->ID),
            'city' => $city,
            'rating' => $rating,
        ];
    }

    private static function getReviewField(string $name, int $postId): string
    {
        if (! function_exists('get_field')) {
            return '';
        }

        $value = get_field($name, $postId);

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
