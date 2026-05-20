<?php

declare(strict_types=1);

namespace Theme\Filters;

final class FilterRegistry
{
    private const CACHE_KEY = 'theme_filter_registry_list';
    private const ACF_FIELD = 'filter_list';
    private const ACF_POST_ID = 'option';

    public static function getFilters(): array
    {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $filters = [];

        if (function_exists('get_field')) {
            $rows = get_field(self::ACF_FIELD, self::ACF_POST_ID);

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (! is_array($row) || empty($row['is_active'])) {
                        continue;
                    }

                    $currencyCodes = [];
                    if (! empty($row['currency_codes'])) {
                        $currencyCodes = array_values(array_filter(array_map(
                            static fn(string $code): string => trim(strtolower($code)),
                            explode(',', (string) $row['currency_codes'])
                        )));
                    }

                    $filters[] = [
                        'name'               => (string) ($row['filter_name'] ?? ''),
                        'slug'               => (string) ($row['filter_slug'] ?? ''),
                        'show_in_buy'        => (bool) ($row['show_in_buy'] ?? false),
                        'show_in_sell'       => (bool) ($row['show_in_sell'] ?? false),
                        'checked_by_default' => (bool) ($row['checked_by_default'] ?? false),
                        'currency_codes'     => $currencyCodes,
                    ];
                }
            }
        }

        set_transient(self::CACHE_KEY, $filters, 2 * HOUR_IN_SECONDS);

        return $filters;
    }

    public static function getFiltersForTab(string $tab, string $currency = ''): array
    {
        $currency = strtolower($currency);
        $result = [];

        foreach (self::getFilters() as $filter) {
            if (! empty($filter['currency_codes']) && ($currency === '' || ! in_array($currency, $filter['currency_codes'], true))) {
                continue;
            }

            if ($tab === 'all') {
                $result[] = $filter;
                continue;
            }

            if ($tab === 'buy' && ! empty($filter['show_in_buy'])) {
                $result[] = $filter;
            }

            if ($tab === 'sell' && ! empty($filter['show_in_sell'])) {
                $result[] = $filter;
            }
        }

        return $result;
    }

    public static function getDefaultTab(): string
    {
        if (function_exists('get_field')) {
            $defaultTab = get_field('default_tab', 'option');

            if (in_array($defaultTab, ['all', 'buy', 'sell'], true)) {
                return $defaultTab;
            }
        }

        return 'buy';
    }

    public static function invalidateCache(): void
    {
        delete_transient(self::CACHE_KEY);
    }
}
