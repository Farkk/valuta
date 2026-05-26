<?php

declare(strict_types=1);

namespace Theme\Rates;

use Theme\Banks\BankRegistry;
use Theme\Currencies\CurrencyApiClient;
use Theme\Filters\FilterRegistry;

final class RatesManager
{
    private const CACHE_LIFETIME = 10 * MINUTE_IN_SECONDS;
    private const KKB_BANK_CODE = 'kamkombank';

    public static function getRates(string $cityName, string $currency, bool $useCache = true): array
    {
        $cacheKey = 'theme_rates_' . md5($cityName . '_' . strtolower($currency));

        if ($useCache) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $result = (new CurrencyApiClient())->getRates($cityName, $currency);
        if (! $result['success'] || ! is_array($result['data'])) {
            error_log('Theme RatesManager: failed to get rates - ' . $result['error']);

            return [];
        }

        $rates = $result['data'];

        if (! empty($rates['cities']) && is_array($rates['cities'])) {
            $cityData = null;

            foreach ($rates['cities'] as $city) {
                if (is_array($city) && ($city['city_name'] ?? '') === $cityName) {
                    $cityData = $city;
                    break;
                }
            }

            if ($cityData === null && ! empty($rates['cities'][0]) && is_array($rates['cities'][0])) {
                $cityData = $rates['cities'][0];
            }

            if ($cityData !== null) {
                $rates['cities'] = [$cityData];
            }
        }

        if (! empty($rates['cities'][0]['banks']) && is_array($rates['cities'][0]['banks'])) {
            $rawBanks = $rates['cities'][0]['banks'];
            BankRegistry::syncFromApi($rawBanks);
            BankRegistry::updateLogosFromApi($rawBanks);
        }

        if ($useCache) {
            set_transient($cacheKey, $rates, self::CACHE_LIFETIME);
        }

        return $rates;
    }

    public static function getRawBanks(array $rates): array
    {
        $banks = $rates['cities'][0]['banks'] ?? [];

        return is_array($banks) ? $banks : [];
    }

    public static function getMapOffices(array $rawBanks, string $tab = 'all', array $filters = []): array
    {
        $kkbReferenceRate = self::getKkbReferenceRate($rawBanks, $tab);
        $offices = [];
        /** @var array<string, array{bank_name: string, bank_logo: string, bank_url: string}> $bankMeta */
        $bankMeta = [];

        foreach ($rawBanks as $bank) {
            if (! is_array($bank)) {
                continue;
            }

            $bankCode = (string) ($bank['bank_code'] ?? '');

            if ($filters !== [] && $bankCode !== self::KKB_BANK_CODE) {
                continue;
            }

            if ($bankCode !== '' && ! isset($bankMeta[$bankCode])) {
                $bankMeta[$bankCode] = [
                    'bank_name' => (string) ($bank['bank_name'] ?? ''),
                    'bank_logo' => BankRegistry::getBankLogo($bankCode, (string) ($bank['bank_logo'] ?? '')),
                    'bank_url'  => BankRegistry::getBankUrl($bankCode),
                ];
            }

            foreach (($bank['offices'] ?? []) as $office) {
                if (! is_array($office) || empty($office['latitude']) || empty($office['longitude'])) {
                    continue;
                }

                $currencyData = $office['currencies'][0] ?? null;
                if (! is_array($currencyData)) {
                    continue;
                }

                $buy = (float) ($currencyData['buy'] ?? 0);
                $sell = (float) ($currencyData['sell'] ?? 0);

                if ($buy <= 0 || $sell <= 0 || ! self::passesFilters($currencyData, $filters)) {
                    continue;
                }

                if ($filters === [] && $kkbReferenceRate !== null && $bankCode !== self::KKB_BANK_CODE) {
                    if ($tab === 'buy' && $sell < $kkbReferenceRate) {
                        continue;
                    }

                    if ($tab === 'sell' && $buy > $kkbReferenceRate) {
                        continue;
                    }
                }

                $meta = $bankMeta[$bankCode] ?? [
                    'bank_name' => (string) ($bank['bank_name'] ?? ''),
                    'bank_logo' => BankRegistry::getBankLogo($bankCode, (string) ($bank['bank_logo'] ?? '')),
                    'bank_url'  => BankRegistry::getBankUrl($bankCode),
                ];

                $offices[] = [
                    'bank_code' => $bankCode,
                    'bank_name' => $meta['bank_name'],
                    'bank_logo' => $meta['bank_logo'],
                    'bank_url'  => $meta['bank_url'],
                    'latitude'  => (float) $office['latitude'],
                    'longitude' => (float) $office['longitude'],
                    'address'   => (string) ($office['address'] ?? ''),
                    'buy'       => $buy,
                    'sell'      => $sell,
                    'phone'     => (string) ($office['phone'] ?? ''),
                ];
            }
        }

        return $offices;
    }

    public static function sortAndGroupBanks(array $banks, string $tab = 'all', array $filters = []): array
    {
        $allFilterSlugs = array_column(FilterRegistry::getFilters(), 'slug');
        $kkbBestOfficeData = self::getKkbBestOfficeData($banks, $tab);
        $kkbReferenceRate = self::getKkbReferenceRate($banks, $tab);
        $grouped = [];

        foreach ($banks as $bank) {
            if (! is_array($bank)) {
                continue;
            }

            $bankName = (string) ($bank['bank_name'] ?? '');
            $bankCode = (string) ($bank['bank_code'] ?? '');
            if ($bankName === '') {
                continue;
            }

            $bankKey = $bankCode !== '' ? $bankCode : $bankName;
            $isKkb = $bankCode === self::KKB_BANK_CODE;

            $grouped[$bankKey] = [
                'bank_code'      => $bankCode,
                'bank_name'      => $bankName,
                'bank_logo'      => BankRegistry::getBankLogo($bankCode, (string) ($bank['bank_logo'] ?? '')),
                'bank_url'       => BankRegistry::getBankUrl($bankCode),
                'buy'            => '',
                'sell'           => '',
                'spread'         => null,
                'buy_raw'        => 0.0,
                'sell_raw'       => 0.0,
                'active_filters' => [],
                '_has_address'   => false,
                '_is_kkb'        => $isKkb,
                '_better_than_kkb' => false,
                '_best_office'   => null,
            ];

            foreach ($allFilterSlugs as $filterSlug) {
                $grouped[$bankKey][$filterSlug] = false;
            }

            foreach (($bank['offices'] ?? []) as $office) {
                if (! is_array($office)) {
                    continue;
                }

                $currencyData = $office['currencies'][0] ?? null;
                if (! is_array($currencyData)) {
                    continue;
                }

                $buy = (float) ($currencyData['buy'] ?? 0);
                $sell = (float) ($currencyData['sell'] ?? 0);
                if ($buy <= 0 || $sell <= 0) {
                    continue;
                }

                $activeFilters = [];
                foreach ($allFilterSlugs as $filterSlug) {
                    $hasFilter = ! empty($currencyData[$filterSlug]);
                    if ($hasFilter) {
                        $activeFilters[] = $filterSlug;
                    }

                    $grouped[$bankKey][$filterSlug] = ($grouped[$bankKey][$filterSlug] ?? false) || $hasFilter;
                }

                $grouped[$bankKey]['active_filters'] = array_values(array_unique(array_merge(
                    $grouped[$bankKey]['active_filters'],
                    $activeFilters
                )));

                $hasAddress = ! empty($office['address']);
                $passesKkbFilter = true;
                $betterThanKkb = false;

                if (! $isKkb && $kkbBestOfficeData !== null) {
                    if ($tab === 'buy' && $kkbReferenceRate !== null && $sell < $kkbReferenceRate) {
                        $passesKkbFilter = false;
                    } elseif ($tab === 'sell' && $kkbReferenceRate !== null && $buy > $kkbReferenceRate) {
                        $passesKkbFilter = false;
                    } elseif ($tab === 'all') {
                        $betterThanKkb = $sell < $kkbBestOfficeData['sell_raw'] || $buy > $kkbBestOfficeData['buy_raw'];
                    }
                }

                $isValidOffice = $hasAddress && $passesKkbFilter;
                $grouped[$bankKey]['_has_address'] = $grouped[$bankKey]['_has_address'] || $isValidOffice;

                if ($tab === 'all' && $isValidOffice) {
                    $grouped[$bankKey]['_better_than_kkb'] = $grouped[$bankKey]['_better_than_kkb'] || $betterThanKkb;
                }

                if (! $isValidOffice) {
                    continue;
                }

                if ($isKkb && $tab === 'all') {
                    if ($grouped[$bankKey]['buy_raw'] <= 0 || $buy > $grouped[$bankKey]['buy_raw']) {
                        $grouped[$bankKey]['buy'] = self::formatRate($buy);
                        $grouped[$bankKey]['buy_raw'] = $buy;
                    }

                    if ($grouped[$bankKey]['sell_raw'] <= 0 || $sell < $grouped[$bankKey]['sell_raw']) {
                        $grouped[$bankKey]['sell'] = self::formatRate($sell);
                        $grouped[$bankKey]['sell_raw'] = $sell;
                    }

                    if ($grouped[$bankKey]['buy_raw'] > 0 && $grouped[$bankKey]['sell_raw'] > 0) {
                        $grouped[$bankKey]['spread'] = $grouped[$bankKey]['sell_raw'] - $grouped[$bankKey]['buy_raw'];
                        $grouped[$bankKey]['_best_office'] = [
                            'buy_raw' => $grouped[$bankKey]['buy_raw'],
                            'sell_raw' => $grouped[$bankKey]['sell_raw'],
                            'spread' => $grouped[$bankKey]['spread'],
                        ];
                    }

                    continue;
                }

                if (! self::isOfficeBetterForTab($grouped[$bankKey]['_best_office'], $buy, $sell, $tab)) {
                    continue;
                }

                $grouped[$bankKey]['buy'] = self::formatRate($buy);
                $grouped[$bankKey]['sell'] = self::formatRate($sell);
                $grouped[$bankKey]['spread'] = $sell - $buy;
                $grouped[$bankKey]['buy_raw'] = $buy;
                $grouped[$bankKey]['sell_raw'] = $sell;
                $grouped[$bankKey]['_best_office'] = [
                    'buy_raw' => $buy,
                    'sell_raw' => $sell,
                    'spread' => $sell - $buy,
                ];
            }
        }

        $grouped = array_filter($grouped, static function (array $bank) use ($filters): bool {
            if (empty($bank['_has_address']) || $bank['_best_office'] === null) {
                return false;
            }

            if ($filters !== []) {
                if (empty($bank['_is_kkb'])) {
                    return false;
                }

                foreach ($filters as $filterSlug) {
                    if (! in_array($filterSlug, $bank['active_filters'] ?? [], true)) {
                        return false;
                    }
                }
            }

            return true;
        });

        $list = array_values($grouped);
        self::sortBanks($list, $tab);

        foreach ($list as $idx => $bank) {
            unset($list[$idx]['_has_address'], $list[$idx]['_is_kkb'], $list[$idx]['_better_than_kkb'], $list[$idx]['_best_office']);
        }

        return $list;
    }

    public static function formatCardParams(array $bank, string $tab, float $amount): array
    {
        $hasAmount = $amount > 0;

        if ($tab === 'sell') {
            return [
                'buy_label'     => 'Курс банка',
                'sell_label'    => 'Вы получите',
                'buy_rate'      => $bank['buy'] . ' ₽',
                'sell_rate'     => $hasAmount ? self::formatAmount((float) $bank['buy_raw'] * $amount) . ' ₽' : '0 ₽',
                'buy_rate_raw'  => $bank['buy_raw'],
                'sell_rate_raw' => $bank['sell_raw'],
            ];
        }

        if ($tab === 'buy') {
            return [
                'buy_label'     => 'Курс банка',
                'sell_label'    => 'Расчёт суммы',
                'buy_rate'      => $bank['sell'] . ' ₽',
                'sell_rate'     => $hasAmount ? self::formatAmount((float) $bank['sell_raw'] * $amount) . ' ₽' : '0 ₽',
                'buy_rate_raw'  => $bank['buy_raw'],
                'sell_rate_raw' => $bank['sell_raw'],
            ];
        }

        return [
            'buy_label'     => 'Покупка',
            'sell_label'    => 'Продажа',
            'buy_rate'      => $bank['buy'] . ' ₽',
            'sell_rate'     => $bank['sell'] . ' ₽',
            'buy_rate_raw'  => $bank['buy_raw'],
            'sell_rate_raw' => $bank['sell_raw'],
        ];
    }

    public static function formatUpdateTime(array $rates): string
    {
        if (empty($rates['timestamp'])) {
            return '';
        }

        $timestamp = is_numeric($rates['timestamp']) ? (int) $rates['timestamp'] : strtotime((string) $rates['timestamp']);

        return $timestamp ? 'Обновление: ' . wp_date('d.m.Y H:i', $timestamp) : '';
    }

    private static function getKkbReferenceRate(array $banks, string $tab): ?float
    {
        if ($tab === 'all') {
            return null;
        }

        $best = self::getKkbBestOfficeData($banks, $tab);
        if ($best === null) {
            return null;
        }

        return $tab === 'buy' ? $best['sell_raw'] : $best['buy_raw'];
    }

    private static function getKkbBestOfficeData(array $banks, string $tab): ?array
    {
        $best = null;
        $bestBuy = null;
        $bestSell = null;

        foreach ($banks as $bank) {
            if (! is_array($bank) || ($bank['bank_code'] ?? '') !== self::KKB_BANK_CODE) {
                continue;
            }

            foreach (($bank['offices'] ?? []) as $office) {
                $currencyData = is_array($office) ? ($office['currencies'][0] ?? null) : null;
                if (! is_array($currencyData) || empty($office['address'])) {
                    continue;
                }

                $buy = (float) ($currencyData['buy'] ?? 0);
                $sell = (float) ($currencyData['sell'] ?? 0);
                if ($buy <= 0 || $sell <= 0) {
                    continue;
                }

                $bestBuy = $bestBuy === null ? $buy : max($bestBuy, $buy);
                $bestSell = $bestSell === null ? $sell : min($bestSell, $sell);

                if (self::isOfficeBetterForTab($best, $buy, $sell, $tab)) {
                    $best = ['buy_raw' => $buy, 'sell_raw' => $sell, 'spread' => $sell - $buy];
                }
            }

            break;
        }

        if ($tab === 'all' && $bestBuy !== null && $bestSell !== null) {
            return ['buy_raw' => $bestBuy, 'sell_raw' => $bestSell, 'spread' => $bestSell - $bestBuy];
        }

        return $best;
    }

    private static function isOfficeBetterForTab(?array $bestOffice, float $buy, float $sell, string $tab): bool
    {
        if ($bestOffice === null) {
            return true;
        }

        if ($tab === 'buy') {
            return $sell < $bestOffice['sell_raw'];
        }

        if ($tab === 'sell') {
            return $buy > $bestOffice['buy_raw'];
        }

        return ($sell - $buy) < $bestOffice['spread'];
    }

    private static function passesFilters(array $currencyData, array $filters): bool
    {
        foreach ($filters as $filterSlug) {
            if (empty($currencyData[$filterSlug])) {
                return false;
            }
        }

        return true;
    }

    private static function sortBanks(array &$banks, string $tab): void
    {
        if ($tab === 'sell') {
            usort($banks, static fn(array $a, array $b): int => $b['buy_raw'] <=> $a['buy_raw']);
            return;
        }

        if ($tab === 'buy') {
            usort($banks, static fn(array $a, array $b): int => $a['sell_raw'] <=> $b['sell_raw']);
            return;
        }

        usort($banks, static function (array $a, array $b): int {
            if (! empty($a['_is_kkb']) && empty($b['_is_kkb'])) {
                return -1;
            }

            if (empty($a['_is_kkb']) && ! empty($b['_is_kkb'])) {
                return 1;
            }

            if (! empty($a['_better_than_kkb']) && empty($b['_better_than_kkb'])) {
                return 1;
            }

            if (empty($a['_better_than_kkb']) && ! empty($b['_better_than_kkb'])) {
                return -1;
            }

            return (($a['buy_raw'] + $a['sell_raw']) / 2) <=> (($b['buy_raw'] + $b['sell_raw']) / 2);
        });
    }

    private static function formatRate(float $value): string
    {
        return number_format($value, 2, ',', '');
    }

    private static function formatAmount(float $value): string
    {
        $formatted = number_format($value, 2, ',', ' ');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}
