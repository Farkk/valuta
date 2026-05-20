<?php

declare(strict_types=1);

namespace Theme\Cities;

final class CityManager
{
    private const COOKIE_NAME = 'user_city_slug';
    public const DEFAULT_CITY_SLUG = 'moskva';
    private const CACHE_KEY = 'theme_cities_list';

    /**
     * @return list<array{city_id?: int|string, city_name: string, slug: string}>
     */
    public static function getCities(bool $useCache = true): array
    {
        if ($useCache) {
            $cached = get_transient(self::CACHE_KEY);

            if (is_array($cached)) {
                return $cached;
            }
        }

        $result = (new CityApiClient())->getCities();

        if (! $result['success'] || ! is_array($result['data'])) {
            error_log('Theme CityManager: failed to get cities - ' . $result['error']);

            return self::fallbackCities();
        }

        $cities = [];

        foreach ($result['data'] as $city) {
            if (! is_array($city) || empty($city['city_name'])) {
                continue;
            }

            $cityName = (string) $city['city_name'];
            $cities[] = [
                'city_id'   => $city['city_id'] ?? '',
                'city_name' => $cityName,
                'slug'      => self::transliterate($cityName),
            ];
        }

        if ($cities === []) {
            return self::fallbackCities();
        }

        set_transient(self::CACHE_KEY, $cities, HOUR_IN_SECONDS);

        return $cities;
    }

    /**
     * @return array{city_id?: int|string, city_name: string, slug: string}|null
     */
    public static function getCityBySlug(string $slug): ?array
    {
        $slug = self::normalizeSlug($slug);

        foreach (self::getCities() as $city) {
            if ($city['slug'] === $slug) {
                return $city;
            }
        }

        return null;
    }

    /**
     * @return array{city_id?: int|string, city_name: string, slug: string}
     */
    public static function getCurrentCity(): array
    {
        $urlCity = (string) get_query_var('city', '');
        if ($urlCity !== '') {
            $city = self::getCityBySlug($urlCity);

            if ($city !== null) {
                return $city;
            }
        }

        $cookieCity = self::getCityFromCookie();
        if ($cookieCity !== null) {
            $city = self::getCityBySlug($cookieCity);

            if ($city !== null) {
                return $city;
            }
        }

        return self::getCityBySlug(self::DEFAULT_CITY_SLUG)
            ?? self::getCities()[0]
            ?? [
                'city_id'   => 0,
                'city_name' => 'Казань',
                'slug'      => 'kazan',
            ];
    }

    public static function setCityCookie(string $slug): bool
    {
        $slug = self::normalizeSlug($slug);

        if (self::getCityBySlug($slug) === null) {
            return false;
        }

        return setcookie(
            self::COOKIE_NAME,
            $slug,
            time() + (30 * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }

    public static function getCityFromCookie(): ?string
    {
        if (empty($_COOKIE[self::COOKIE_NAME])) {
            return null;
        }

        return sanitize_text_field(wp_unslash((string) $_COOKIE[self::COOKIE_NAME]));
    }

    public static function hasCityCookie(): bool
    {
        return ! empty($_COOKIE[self::COOKIE_NAME]);
    }

    /**
     * @return array<string, list<array{city_id?: int|string, city_name: string, slug: string}>>
     */
    public static function getCitiesGrouped(): array
    {
        $grouped = [];

        foreach (self::getCities() as $city) {
            $letter = mb_strtoupper(mb_substr($city['city_name'], 0, 1, 'UTF-8'), 'UTF-8');
            $grouped[$letter] ??= [];
            $grouped[$letter][] = $city;
        }

        ksort($grouped);

        return $grouped;
    }

    public static function getCityUrl(string $slug): string
    {
        return home_url('/city/' . self::normalizeSlug($slug) . '/');
    }

    public static function isValidSlug(string $slug): bool
    {
        return self::getCityBySlug($slug) !== null;
    }

    public static function normalizeSlug(string $slug): string
    {
        return strtolower(trim($slug));
    }

    public static function transliterate(string $cityName): string
    {
        $map = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
        ];

        $slug = strtr(mb_strtolower($cityName, 'UTF-8'), $map);
        $slug = (string) preg_replace('/[^a-z0-9]+/i', '-', $slug);

        return trim($slug, '-');
    }

    /**
     * @return list<array{city_id: int, city_name: string, slug: string}>
     */
    private static function fallbackCities(): array
    {
        return [
            ['city_id' => 0, 'city_name' => 'Москва', 'slug' => 'moskva'],
            ['city_id' => 0, 'city_name' => 'Казань', 'slug' => 'kazan'],
            ['city_id' => 0, 'city_name' => 'Омск', 'slug' => 'omsk'],
        ];
    }
}
