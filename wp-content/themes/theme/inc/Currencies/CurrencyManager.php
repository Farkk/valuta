<?php

declare(strict_types=1);

namespace Theme\Currencies;

final class CurrencyManager
{
    private const CACHE_KEY = 'theme_currencies_list';

    private const SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'CNY' => '¥',
        'JPY' => '¥',
        'CHF' => '₣',
        'RUB' => '₽',
    ];

    private const NAMES_RU = [
        'USD' => 'Доллар США',
        'EUR' => 'Евро',
        'GBP' => 'Фунт стерлингов',
        'CNY' => 'Китайский юань',
        'JPY' => 'Японская иена',
        'CHF' => 'Швейцарский франк',
        'RUB' => 'Российский рубль',
    ];

    /**
     * @return list<array{label: string, code: string, symbol: string, name_ru: string}>
     */
    public static function getCurrencies(bool $useCache = true): array
    {
        if ($useCache) {
            $cached = get_transient(self::CACHE_KEY);

            if (is_array($cached) && self::hasExchangeCurrencies($cached)) {
                return $cached;
            }

            if (is_array($cached)) {
                delete_transient(self::CACHE_KEY);
            }
        }

        $currencies = [];
        $result = (new CurrencyApiClient())->getCurrencies();

        if ($result['success'] && is_array($result['data'])) {
            foreach ($result['data'] as $currencyData) {
                if (! is_array($currencyData) || empty($currencyData['currency'])) {
                    continue;
                }

                $currencies[] = self::formatCurrency((string) $currencyData['currency']);
            }
        }

        $currencies[] = self::formatCurrency('RUB');
        $currencies = self::uniqueByCode($currencies);

        if (self::hasExchangeCurrencies($currencies)) {
            set_transient(self::CACHE_KEY, $currencies, 2 * MINUTE_IN_SECONDS);
        }

        return self::hasExchangeCurrencies($currencies) ? $currencies : self::fallbackCurrencies();
    }

    /**
     * @return array{label: string, code: string, symbol: string, name_ru: string}
     */
    public static function formatCurrency(string $code): array
    {
        $code = strtoupper($code);
        $symbol = self::SYMBOLS[$code] ?? '';

        return [
            'label'   => $code . ($symbol !== '' ? ' (' . $symbol . ')' : ''),
            'code'    => strtolower($code),
            'symbol'  => $symbol,
            'name_ru' => self::NAMES_RU[$code] ?? $code,
        ];
    }

    /**
     * @return array{label: string, code: string, symbol: string, name_ru: string}|null
     */
    public static function getCurrencyByCode(string $code): ?array
    {
        $code = strtolower($code);

        foreach (self::getCurrencies() as $currency) {
            if ($currency['code'] === $code) {
                return $currency;
            }
        }

        return null;
    }

    /**
     * @param list<array{label: string, code: string, symbol: string, name_ru: string}> $currencies
     * @return list<array{label: string, code: string, symbol: string, name_ru: string}>
     */
    private static function uniqueByCode(array $currencies): array
    {
        $result = [];
        $seen = [];

        foreach ($currencies as $currency) {
            if (isset($seen[$currency['code']])) {
                continue;
            }

            $seen[$currency['code']] = true;
            $result[] = $currency;
        }

        return $result;
    }

    /**
     * @param list<array{label: string, code: string, symbol: string, name_ru: string}> $currencies
     */
    private static function hasExchangeCurrencies(array $currencies): bool
    {
        foreach ($currencies as $currency) {
            if (($currency['code'] ?? '') !== 'rub') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{label: string, code: string, symbol: string, name_ru: string}>
     */
    private static function fallbackCurrencies(): array
    {
        return [
            self::formatCurrency('USD'),
            self::formatCurrency('EUR'),
            self::formatCurrency('GBP'),
            self::formatCurrency('CNY'),
            self::formatCurrency('RUB'),
        ];
    }
}
