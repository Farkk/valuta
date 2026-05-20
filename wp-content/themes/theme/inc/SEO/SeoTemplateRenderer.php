<?php

declare(strict_types=1);

namespace Theme\SEO;

use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyManager;
use Theme\Helpers\Text;

final class SeoTemplateRenderer
{
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_CITY_CURRENCY = 'city_currency';
    public const TYPE_CONVERSION = 'conversion';
    public const TYPE_BANK = 'bank';

    private const CURRENCY_FORMS = [
        'usd' => ['currency' => 'доллар', 'currency_genitive' => 'доллара', 'currency_plural_genitive' => 'долларов', 'currency_slug' => 'dollara', 'currency_amount_slug' => 'dollarov', 'aliases' => ['usd', 'dollar', 'dollara', 'dollarov']],
        'eur' => ['currency' => 'евро', 'currency_genitive' => 'евро', 'currency_plural_genitive' => 'евро', 'currency_slug' => 'evro', 'currency_amount_slug' => 'evro', 'aliases' => ['eur', 'euro', 'evro']],
        'cny' => ['currency' => 'юань', 'currency_genitive' => 'юаня', 'currency_plural_genitive' => 'юаней', 'currency_slug' => 'yuanya', 'currency_amount_slug' => 'yuaney', 'aliases' => ['cny', 'yuan', 'yuanya', 'yuaney']],
        'gbp' => ['currency' => 'фунт', 'currency_genitive' => 'фунта', 'currency_plural_genitive' => 'фунтов', 'currency_slug' => 'funta', 'currency_amount_slug' => 'funtov', 'aliases' => ['gbp', 'funt', 'funta', 'funtov']],
        'chf' => ['currency' => 'франк', 'currency_genitive' => 'франка', 'currency_plural_genitive' => 'франков', 'currency_slug' => 'franka', 'currency_amount_slug' => 'frankov', 'aliases' => ['chf', 'frank', 'franka', 'frankov']],
        'jpy' => ['currency' => 'иена', 'currency_genitive' => 'иены', 'currency_plural_genitive' => 'иен', 'currency_slug' => 'ieny', 'currency_amount_slug' => 'ien', 'aliases' => ['jpy', 'iena', 'ieny', 'ien']],
        'kzt' => ['currency' => 'тенге', 'currency_genitive' => 'тенге', 'currency_plural_genitive' => 'тенге', 'currency_slug' => 'tenge', 'currency_amount_slug' => 'tenge', 'aliases' => ['kzt', 'tenge']],
        'aud' => ['currency' => 'австралийский доллар', 'currency_genitive' => 'австралийского доллара', 'currency_plural_genitive' => 'австралийских долларов', 'currency_slug' => 'avstraliyskogo-dollara', 'currency_amount_slug' => 'avstraliyskih-dollarov', 'aliases' => ['aud', 'avstraliyskogo-dollara', 'avstraliyskih-dollarov']],
    ];

    private const DEFAULT_TEMPLATES = [
        self::TYPE_CURRENCY => [
            'title' => 'Курс {currency_genitive} сегодня — актуальный курс',
            'description' => 'Актуальный курс {currency_genitive} к рублю на сегодня. Сравнение банков и обменников, где выгодно купить валюту.',
            'h1' => 'Курс {currency_genitive} на сегодня',
            'h2' => 'Подобрали лучший курс {currency_genitive}',
            'h2_bottom' => 'Где выгодно обменять {currency} в {city_prepositional} в 2026 году',
        ],
        self::TYPE_CITY_CURRENCY => [
            'title' => 'Курс {currency_genitive} в {city_prepositional} сегодня — лучший курс',
            'description' => 'Актуальный курс {currency_genitive} в {city_prepositional}. Сравнение банков и обменников, где выгодно купить валюту.',
            'h1' => 'Курс {currency_genitive} в {city_prepositional}',
            'h2' => 'Подобрали лучший курс {currency_genitive} в {city_prepositional}',
            'h2_bottom' => 'Где выгодно обменять {currency} в {city_prepositional} в 2026 году',
        ],
        self::TYPE_CONVERSION => [
            'title' => '{amount} {currency_genitive} в рублях на сегодня',
            'description' => 'Сколько стоит {amount} {currency_genitive} в рублях. Онлайн конвертер валют по актуальному курсу.',
            'h1' => '{amount} {currency_genitive} в рублях',
            'h2' => 'Подобрали лучший курс {amount} {currency_genitive}',
            'h2_bottom' => 'Где выгодно обменять {amount} {currency_genitive} в рублях',
        ],
        self::TYPE_BANK => [
            'title' => 'Курс валют в {bank}',
            'description' => 'Актуальные курсы валют в {bank}. Сравнение условий обмена и лучшие предложения.',
            'h1' => 'Курс валют в {bank}',
            'h2' => 'Подобрали лучший курс в {bank}',
            'h2_bottom' => 'Курсы валют в {bank} на сегодня',
        ],
    ];

    private const SEO_TEXT_VARIANTS = [
        self::TYPE_CURRENCY => [
            'Valutium — удобный сервис для поиска выгодного курса {currency_genitive} в России. Сравнивайте предложения банков и обменников и выбирайте лучший курс для обмена валюты.',
            'На странице представлен актуальный курс {currency_genitive} к рублю. Сравните предложения банков и выберите наиболее выгодные условия обмена валюты.',
        ],
        self::TYPE_CITY_CURRENCY => [
            'Valutium — сервис для поиска выгодного курса {currency_genitive} в {city_prepositional}. Сравнивайте предложения банков и обменников и выбирайте лучший вариант обмена.',
            'На странице представлены актуальные курсы {currency_genitive} в {city_prepositional}. Сравните предложения банков и выберите наиболее выгодный курс обмена валют.',
        ],
        self::TYPE_CONVERSION => [
            'Узнайте, сколько стоит {amount} {currency_genitive} в рублях на сегодня. Онлайн-конвертер показывает актуальный курс и помогает быстро рассчитать сумму.',
            'На странице вы можете быстро рассчитать, сколько стоит {amount} {currency_genitive} в рублях. Используйте актуальный курс для точного расчета.',
        ],
        self::TYPE_BANK => [
            'На странице представлены актуальные курсы валют в {bank}. Сравните условия обмена и выберите наиболее выгодное предложение.',
            'Valutium показывает актуальные курсы валют в {bank}. Узнайте, по какому курсу можно купить или продать валюту.',
        ],
    ];

    public static function normalizeType(string $type): string
    {
        $type = str_replace(['-', ' ', '+'], ['_', '_', '_'], trim(mb_strtolower($type, 'UTF-8')));
        $map = [
            'currency' => self::TYPE_CURRENCY,
            'valyuta' => self::TYPE_CURRENCY,
            'валюта' => self::TYPE_CURRENCY,
            'city_currency' => self::TYPE_CITY_CURRENCY,
            'город_валюта' => self::TYPE_CITY_CURRENCY,
            'город_и_валюта' => self::TYPE_CITY_CURRENCY,
            'conversion' => self::TYPE_CONVERSION,
            'converter' => self::TYPE_CONVERSION,
            'конвертация' => self::TYPE_CONVERSION,
            'bank' => self::TYPE_BANK,
            'банк' => self::TYPE_BANK,
        ];

        return $map[$type] ?? $type;
    }

    public static function detectTypeFromUrl(string $url): string
    {
        $path = SeoPageManager::normalizeUrl($url);

        if (preg_match('~^/banki/[^/]+/?$~', $path)) {
            return self::TYPE_BANK;
        }

        if (preg_match('~^/\d+-[^/]+-v-rublyah/?$~', $path)) {
            return self::TYPE_CONVERSION;
        }

        if (preg_match('~^/[^/]+/kurs-[^/]+/?$~', $path)) {
            return self::TYPE_CITY_CURRENCY;
        }

        return self::TYPE_CURRENCY;
    }

    /**
     * @param array<string, mixed> $existing
     * @return array<string, mixed>
     */
    public static function inferVariables(string $type, string $url, array $existing = []): array
    {
        $type = self::normalizeType($type);
        $path = SeoPageManager::normalizeUrl($url);
        $vars = $existing;

        if ($type === self::TYPE_CITY_CURRENCY && preg_match('~^/([^/]+)/kurs-([^/]+)$~', $path, $matches)) {
            $vars['city_slug'] = ! empty($vars['city_slug']) ? $vars['city_slug'] : sanitize_title($matches[1]);
            $vars['currency_slug'] = ! empty($vars['currency_slug']) ? $vars['currency_slug'] : sanitize_title($matches[2]);
        } elseif ($type === self::TYPE_CURRENCY && preg_match('~^/kurs-([^/]+)$~', $path, $matches)) {
            $vars['currency_slug'] = ! empty($vars['currency_slug']) ? $vars['currency_slug'] : sanitize_title($matches[1]);
        } elseif ($type === self::TYPE_CONVERSION && preg_match('~^/(\d+)-([^/]+)-v-rublyah$~', $path, $matches)) {
            $vars['amount'] = ! empty($vars['amount']) ? (int) $vars['amount'] : (int) $matches[1];
            $vars['currency_slug'] = ! empty($vars['currency_slug']) ? $vars['currency_slug'] : sanitize_title($matches[2]);
        } elseif ($type === self::TYPE_BANK && preg_match('~^/banki/([^/]+)$~', $path, $matches)) {
            $vars['bank_slug'] = ! empty($vars['bank_slug']) ? $vars['bank_slug'] : sanitize_title($matches[1]);
        }

        if (empty($vars['currency_code']) && ! empty($vars['currency_slug'])) {
            $vars['currency_code'] = self::getCurrencyCodeBySlug((string) $vars['currency_slug']);
        }

        $vars['currency_code'] = ! empty($vars['currency_code']) ? $vars['currency_code'] : 'usd';
        $vars['city_slug'] = ! empty($vars['city_slug']) ? $vars['city_slug'] : CityManager::DEFAULT_CITY_SLUG;
        $vars['amount'] = ! empty($vars['amount']) ? (int) $vars['amount'] : 100;
        $vars['bank_slug'] = $vars['bank_slug'] ?? '';

        $currency = self::getCurrencyForms((string) $vars['currency_code'], (string) ($vars['currency_slug'] ?? ''));
        $city = self::getCityVariables((string) $vars['city_slug']);
        $bank = self::getBankVariables((string) $vars['bank_slug']);
        $currencyGenitive = $type === self::TYPE_CONVERSION && (int) $vars['amount'] !== 1
            ? $currency['currency_plural_genitive']
            : $currency['currency_genitive'];

        return array_merge($vars, [
            'currency_code' => $currency['code'],
            'currency' => $currency['currency'],
            'currency_genitive' => $currencyGenitive,
            'currency_singular_genitive' => $currency['currency_genitive'],
            'currency_plural_genitive' => $currency['currency_plural_genitive'],
            'currency_slug' => $currency['currency_slug'],
            'amount' => (int) $vars['amount'],
            'city' => $city['city'],
            'city_slug' => $city['city_slug'],
            'city_prepositional' => $city['city_prepositional'],
            'bank' => $bank['bank'],
            'bank_slug' => $bank['bank_slug'],
            'bank_code' => $bank['bank_code'],
        ]);
    }

    /**
     * @param array<string, mixed> $variables
     */
    public static function render(string $template, array $variables): string
    {
        $replace = [];

        foreach ($variables as $key => $value) {
            if (is_scalar($value)) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr(str_replace(['в {city_prepositional}', 'В {city_prepositional}'], '{city_prepositional}', $template), $replace);
    }

    public static function getDefaultTemplate(string $type, string $field): string
    {
        return self::DEFAULT_TEMPLATES[self::normalizeType($type)][$field] ?? '';
    }

    /**
     * @param array<string, mixed> $variables
     */
    public static function getDefaultSeoText(string $type, string $seed, array $variables): string
    {
        $type = self::normalizeType($type);
        $variants = self::SEO_TEXT_VARIANTS[$type] ?? self::SEO_TEXT_VARIANTS[self::TYPE_CURRENCY];
        $index = abs((int) crc32($seed)) % count($variants);

        return self::render($variants[$index], $variables);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getCurrencyForms(string $code = 'usd', string $slug = ''): array
    {
        $code = strtolower($code ?: 'usd');

        if (! isset(self::CURRENCY_FORMS[$code]) && $slug !== '') {
            $code = self::getCurrencyCodeBySlug($slug);
        }

        $forms = self::CURRENCY_FORMS[$code] ?? null;

        if (! $forms) {
            $currency = CurrencyManager::getCurrencyByCode($code);
            $name = mb_strtolower((string) ($currency['name_ru'] ?? strtoupper($code)), 'UTF-8');
            $forms = [
                'currency' => $name,
                'currency_genitive' => $name,
                'currency_plural_genitive' => $name,
                'currency_slug' => $slug ?: $code,
                'currency_amount_slug' => $slug ?: $code,
            ];
        }

        $forms['code'] = $code;

        return $forms;
    }

    public static function getCurrencyCodeBySlug(string $slug): string
    {
        $slug = sanitize_title($slug);

        foreach (self::CURRENCY_FORMS as $code => $forms) {
            if ($slug === $forms['currency_slug'] || $slug === $forms['currency_amount_slug'] || in_array($slug, $forms['aliases'], true)) {
                return $code;
            }
        }

        return strtolower($slug ?: 'usd');
    }

    /**
     * @return array{city: string, city_slug: string, city_prepositional: string}
     */
    public static function getCityVariables(string $citySlug): array
    {
        $citySlug = sanitize_title($citySlug ?: CityManager::DEFAULT_CITY_SLUG);
        $city = CityManager::getCityBySlug($citySlug);

        if (! $city) {
            $citySlug = CityManager::DEFAULT_CITY_SLUG;
            $city = CityManager::getCityBySlug($citySlug);
        }

        $cityName = (string) ($city['city_name'] ?? 'Москва');

        return [
            'city' => $cityName,
            'city_slug' => $citySlug,
            'city_prepositional' => 'в ' . Text::cityPrepositional($cityName),
        ];
    }

    /**
     * @return array{bank: string, bank_slug: string, bank_code: string}
     */
    public static function getBankVariables(string $bankSlug): array
    {
        return [
            'bank' => $bankSlug !== '' ? str_replace('-', ' ', sanitize_title($bankSlug)) : 'банке',
            'bank_slug' => sanitize_title($bankSlug),
            'bank_code' => '',
        ];
    }
}
