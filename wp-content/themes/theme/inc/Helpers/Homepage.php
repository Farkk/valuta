<?php

declare(strict_types=1);

namespace Theme\Helpers;

use Theme\ACF\AcfManager;
use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyManager;
use Theme\Currencies\CurrencyRouter;
use Theme\SEO\SeoPageManager;
use Theme\SEO\SeoTemplateRenderer;

final class Homepage
{
    /**
     * @return array{title: string, description: string}
     */
    public static function getExchangeText(): array
    {
        $data = AcfManager::getField('exchange-rate', 'option', []);
        $fallbackTitle = __('Где выгодно обменять валюту в России в 2026 году', 'theme');
        $fallbackDescription = __('При необходимости узнать в реальном времени выгодный курс обмена валют в банках необязательно их посещение. Наш сайт поможет сэкономить время и найти актуальные сведения по текущим котировкам валют мира.', 'theme');

        if (SeoPageManager::hasCurrentPage()) {
            return [
                'title' => SeoPageManager::getCurrentValue('h2_bottom', $fallbackTitle),
                'description' => SeoPageManager::getCurrentValue('seo_text', $fallbackDescription),
            ];
        }

        return [
            'title' => (string) ($data['exchange-rate_title'] ?? $fallbackTitle),
            'description' => (string) ($data['exchange-rate_desc'] ?? $fallbackDescription),
        ];
    }

    /**
     * @return array{title: string, items: list<array{question: string, answer: string}>}
     */
    public static function getFaq(): array
    {
        $data = AcfManager::getField('faq', 'option', []);
        $title = (string) ($data['title'] ?? __('Ответы на часто задаваемые вопросы', 'theme'));
        $items = [];

        foreach (($data['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return [
            'title' => $title,
            'items' => $items,
        ];
    }

    public static function getChartUrl(): string
    {
        $currentCity = CityManager::getCurrentCity();
        $currentCurrency = CurrencyRouter::getCurrentCurrency();

        if (SeoPageManager::hasCurrentPage()) {
            $citySlug = SeoPageManager::getCurrentCitySlug() ?: (string) ($currentCity['slug'] ?? CityManager::DEFAULT_CITY_SLUG);
            $currencyCode = SeoPageManager::getCurrentCurrencyCode() ?: $currentCurrency;
            $currencyForms = SeoTemplateRenderer::getCurrencyForms($currencyCode);

            return home_url('/' . $citySlug . '/kurs-' . ($currencyForms['currency_slug'] ?? $currencyCode) . '/');
        }

        $path = '/currency/' . $currentCurrency . '/';
        if (($currentCity['slug'] ?? CityManager::DEFAULT_CITY_SLUG) !== CityManager::DEFAULT_CITY_SLUG) {
            $path .= $currentCity['slug'] . '/';
        }

        return home_url($path);
    }

    public static function getCityUrlForCurrency(array $city): string
    {
        if (SeoPageManager::hasCurrentPage()) {
            $currencyForms = SeoTemplateRenderer::getCurrencyForms(SeoPageManager::getCurrentCurrencyCode());
            $currencySlug = (string) ($currencyForms['currency_slug'] ?? '');

            if ($currencySlug !== '') {
                return home_url('/' . ($city['slug'] ?? '') . '/kurs-' . $currencySlug . '/');
            }
        }

        return CityManager::getCityUrl((string) ($city['slug'] ?? ''));
    }

    public static function getCurrencyUrl(string $currencyCode, array $city): string
    {
        $currencyCode = strtolower($currencyCode);
        $citySlug = (string) ($city['slug'] ?? CityManager::DEFAULT_CITY_SLUG);

        if (SeoPageManager::hasCurrentPage()) {
            $currencyForms = SeoTemplateRenderer::getCurrencyForms($currencyCode);
            $currencySlug = (string) ($currencyForms['currency_slug'] ?? $currencyCode);

            if ($citySlug === CityManager::DEFAULT_CITY_SLUG) {
                return home_url('/kurs-' . $currencySlug . '/');
            }

            return home_url('/' . $citySlug . '/kurs-' . $currencySlug . '/');
        }

        if ($citySlug === CityManager::DEFAULT_CITY_SLUG) {
            return home_url('/currency/' . $currencyCode . '/');
        }

        return home_url('/currency/' . $currencyCode . '/' . $citySlug . '/');
    }

    public static function getDefaultSidebarCurrency(): array
    {
        $currentCurrency = CurrencyRouter::getCurrentCurrency();

        if ($currentCurrency !== 'rub') {
            $current = CurrencyManager::getCurrencyByCode($currentCurrency);
            if ($current !== null) {
                return $current;
            }
        }

        return CurrencyManager::getCurrencyByCode('usd') ?? CurrencyManager::formatCurrency('USD');
    }
}
