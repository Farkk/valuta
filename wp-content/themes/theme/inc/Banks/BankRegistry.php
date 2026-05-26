<?php

declare(strict_types=1);

namespace Theme\Banks;

use Theme\Currencies\CurrencyApiClient;
use Theme\Helpers\Template;

final class BankRegistry
{
    private const CACHE_KEY = 'theme_bank_registry_map_v2';
    private const LOGO_CACHE_KEY = 'theme_bank_logos_v1';
    private const ACF_FIELD = 'bank_list';
    private const ACF_POST_ID = 'option';

    private static bool $syncedThisRequest = false;

    /** @var array<string, array{name: string, url: string, description: string}>|null */
    private static ?array $mapRequestCache = null;

    /** @var array<string, string>|null */
    private static ?array $logoMapRequestCache = null;

    /**
     * @return array<string, array{name: string, url: string, description: string}>
     */
    public static function getMap(): array
    {
        if (self::$mapRequestCache !== null) {
            return self::$mapRequestCache;
        }

        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            self::$mapRequestCache = $cached;

            return $cached;
        }

        $map = [];

        if (function_exists('get_field')) {
            $rows = get_field(self::ACF_FIELD, self::ACF_POST_ID);

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $code = BankManager::normalizeCode((string) ($row['bank_code'] ?? ''));
                    if ($code === '') {
                        continue;
                    }

                    $map[$code] = [
                        'name' => (string) ($row['bank_name'] ?? ''),
                        'url'  => (string) ($row['bank_url'] ?? ''),
                        'description' => (string) ($row['bank_description'] ?? ''),
                    ];
                }
            }
        }

        set_transient(self::CACHE_KEY, $map, 2 * HOUR_IN_SECONDS);
        self::$mapRequestCache = $map;

        return $map;
    }

    /**
     * @return array<string, string>
     */
    public static function getLogoMap(): array
    {
        if (self::$logoMapRequestCache !== null) {
            return self::$logoMapRequestCache;
        }

        $map = get_transient(self::LOGO_CACHE_KEY);

        self::$logoMapRequestCache = is_array($map) ? $map : [];

        return self::$logoMapRequestCache;
    }

    public static function getBankUrl(string $bankCode): string
    {
        $entry = self::resolveEntry($bankCode);

        return (string) ($entry['url'] ?? '');
    }

    public static function getBankName(string $bankCode): string
    {
        $entry = self::resolveEntry($bankCode);

        return (string) ($entry['name'] ?? '');
    }

    public static function getBankDescription(string $bankCode): string
    {
        $entry = self::resolveEntry($bankCode);

        return (string) ($entry['description'] ?? '');
    }

    public static function getBankLogo(string $bankCode, string $apiLogo = ''): string
    {
        $apiLogo = trim($apiLogo);

        if ($apiLogo !== '') {
            return self::absoluteBankLogo($apiLogo);
        }

        $cached = self::getCachedLogo($bankCode);
        if ($cached !== '') {
            return $cached;
        }

        return self::placeholderLogo();
    }

    public static function absoluteBankLogo(string $logo): string
    {
        $logo = trim($logo);

        if ($logo === '') {
            return self::placeholderLogo();
        }

        if (preg_match('~^https?://~i', $logo)) {
            return $logo;
        }

        if (str_starts_with($logo, '//')) {
            return 'https:' . $logo;
        }

        $base = rtrim(CurrencyApiClient::getBaseUrl(), '/');

        if (str_starts_with($logo, '/')) {
            return $base . $logo;
        }

        return $base . '/' . ltrim($logo, '/');
    }

    /**
     * @param list<array<string, mixed>> $rawBanks
     */
    public static function updateLogosFromApi(array $rawBanks): void
    {
        if ($rawBanks === []) {
            return;
        }

        $map = get_transient(self::LOGO_CACHE_KEY);
        if (! is_array($map)) {
            $map = [];
        }

        $changed = false;

        foreach ($rawBanks as $bank) {
            if (! is_array($bank)) {
                continue;
            }

            $code = BankManager::normalizeCode((string) ($bank['bank_code'] ?? ''));
            $logo = trim((string) ($bank['bank_logo'] ?? ''));

            if ($code === '' || $logo === '') {
                continue;
            }

            $absolute = self::absoluteBankLogo($logo);

            if (($map[$code] ?? '') !== $absolute) {
                $map[$code] = $absolute;
                $changed = true;
            }
        }

        if ($changed) {
            set_transient(self::LOGO_CACHE_KEY, $map, 2 * HOUR_IN_SECONDS);
            self::$logoMapRequestCache = $map;
        }
    }

    public static function syncFromApi(array $rawBanks): void
    {
        if (self::$syncedThisRequest || ! function_exists('get_field') || ! function_exists('add_row')) {
            return;
        }

        $existingRows = get_field(self::ACF_FIELD, self::ACF_POST_ID);
        $existingCodes = [];

        if (is_array($existingRows)) {
            foreach ($existingRows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $code = trim((string) ($row['bank_code'] ?? ''));
                if ($code !== '') {
                    $existingCodes[$code] = true;
                }
            }
        }

        $changed = false;

        foreach ($rawBanks as $bank) {
            if (! is_array($bank)) {
                continue;
            }

            $code = trim((string) ($bank['bank_code'] ?? ''));
            if ($code === '' || isset($existingCodes[$code])) {
                continue;
            }

            add_row(self::ACF_FIELD, [
                'bank_code' => $code,
                'bank_name' => (string) ($bank['bank_name'] ?? ''),
                'bank_url'  => '',
            ], self::ACF_POST_ID);

            $existingCodes[$code] = true;
            $changed = true;
        }

        if ($changed) {
            delete_transient(self::CACHE_KEY);
        }

        self::$syncedThisRequest = true;
    }

    public static function invalidateCache(): void
    {
        delete_transient(self::CACHE_KEY);
        delete_transient(self::LOGO_CACHE_KEY);
        self::$mapRequestCache = null;
        self::$logoMapRequestCache = null;
    }

    /**
     * @return array{name: string, url: string, description: string}
     */
    private static function resolveEntry(string $bankCode): array
    {
        $bankCode = BankManager::normalizeCode($bankCode);
        if ($bankCode === '') {
            return ['name' => '', 'url' => '', 'description' => ''];
        }

        $map = self::getMap();

        return is_array($map[$bankCode] ?? null)
            ? $map[$bankCode]
            : ['name' => '', 'url' => '', 'description' => ''];
    }

    private static function getCachedLogo(string $bankCode): string
    {
        $bankCode = BankManager::normalizeCode($bankCode);
        if ($bankCode === '') {
            return '';
        }

        $map = self::getLogoMap();

        return (string) ($map[$bankCode] ?? '');
    }

    private static function placeholderLogo(): string
    {
        return Template::asset('assets/images/bank.png');
    }
}
