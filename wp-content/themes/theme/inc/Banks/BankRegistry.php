<?php

declare(strict_types=1);

namespace Theme\Banks;

use Theme\Helpers\Template;

final class BankRegistry
{
    private const CACHE_KEY = 'theme_bank_registry_map_v2';
    private const ACF_FIELD = 'bank_list';
    private const ACF_POST_ID = 'option';

    private static bool $syncedThisRequest = false;

    /**
     * @return array<string, array{name: string, logo: string, url: string, description: string}>
     */
    public static function getMap(): array
    {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
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
                        'logo' => (string) ($row['bank_logo'] ?? ''),
                        'url'  => (string) ($row['bank_url'] ?? ''),
                        'description' => (string) ($row['bank_description'] ?? ''),
                    ];
                }
            }
        }

        set_transient(self::CACHE_KEY, $map, 2 * HOUR_IN_SECONDS);

        return $map;
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
        $entry = self::resolveEntry($bankCode);
        $logo = (string) ($entry['logo'] ?? '');

        if ($logo !== '') {
            return $logo;
        }

        return self::absoluteBankLogo($apiLogo);
    }

    public static function absoluteBankLogo(string $logo): string
    {
        $logo = trim($logo);

        if ($logo !== '' && preg_match('~^https?://~i', $logo)) {
            return $logo;
        }

        if ($logo !== '' && str_starts_with($logo, '//')) {
            return 'https:' . $logo;
        }

        return Template::asset('assets/images/bank.png');
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
                'bank_logo' => '',
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
    }

    /**
     * @return array{name: string, logo: string, url: string, description: string}
     */
    private static function resolveEntry(string $bankCode): array
    {
        $bankCode = BankManager::normalizeCode($bankCode);
        if ($bankCode === '') {
            return ['name' => '', 'logo' => '', 'url' => '', 'description' => ''];
        }

        $map = self::getMap();

        return is_array($map[$bankCode] ?? null) ? $map[$bankCode] : ['name' => '', 'logo' => '', 'url' => '', 'description' => ''];
    }
}
