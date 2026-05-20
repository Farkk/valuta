<?php

declare(strict_types=1);

namespace Theme\SEO;

final class SeoPageFields
{
    public static function register(): void
    {
        add_action('acf/init', [self::class, 'registerFields']);
        add_filter('acf/validate_value/name=' . SeoPageManager::META_URL, [self::class, 'validateUniqueUrl'], 10, 4);
    }

    public static function registerFields(): void
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_theme_seo_page',
            'title' => 'Данные SEO страницы',
            'fields' => [
                [
                    'key' => 'field_theme_seo_page_type',
                    'label' => 'Тип страницы',
                    'name' => SeoPageManager::META_TYPE,
                    'type' => 'select',
                    'choices' => [
                        SeoTemplateRenderer::TYPE_CURRENCY => 'Валюта',
                        SeoTemplateRenderer::TYPE_CITY_CURRENCY => 'Город + валюта',
                        SeoTemplateRenderer::TYPE_CONVERSION => 'Конвертация',
                        SeoTemplateRenderer::TYPE_BANK => 'Банк',
                    ],
                    'return_format' => 'value',
                    'wrapper' => ['width' => '33'],
                ],
                ['key' => 'field_theme_seo_page_query', 'label' => 'Запрос', 'name' => SeoPageManager::META_QUERY, 'type' => 'text', 'wrapper' => ['width' => '33']],
                ['key' => 'field_theme_seo_page_url', 'label' => 'URL', 'name' => SeoPageManager::META_URL, 'type' => 'text', 'instructions' => 'Например: /moskva/kurs-dollara', 'required' => 1, 'wrapper' => ['width' => '34']],
                ['key' => 'field_theme_seo_page_title', 'label' => 'Title', 'name' => SeoPageManager::META_TITLE, 'type' => 'text'],
                ['key' => 'field_theme_seo_page_description', 'label' => 'Description', 'name' => SeoPageManager::META_DESCRIPTION, 'type' => 'textarea', 'rows' => 2],
                ['key' => 'field_theme_seo_page_h1', 'label' => 'H1', 'name' => SeoPageManager::META_H1, 'type' => 'text', 'wrapper' => ['width' => '50']],
                ['key' => 'field_theme_seo_page_h2', 'label' => 'H2 TOP', 'name' => SeoPageManager::META_H2, 'type' => 'text', 'wrapper' => ['width' => '50']],
                ['key' => 'field_theme_seo_page_h2_bottom', 'label' => 'H2 Bottom', 'name' => SeoPageManager::META_H2_BOTTOM, 'type' => 'text'],
                ['key' => 'field_theme_seo_page_text', 'label' => 'SEO текст', 'name' => SeoPageManager::META_TEXT, 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0],
                ['key' => 'field_theme_seo_page_vars_tab', 'label' => 'Переменные', 'name' => 'seo_vars_tab', 'type' => 'tab', 'placement' => 'top'],
                ['key' => 'field_theme_seo_page_currency_code', 'label' => 'Код валюты', 'name' => SeoPageManager::META_CURRENCY_CODE, 'type' => 'text', 'wrapper' => ['width' => '25']],
                ['key' => 'field_theme_seo_page_currency_slug', 'label' => 'Slug валюты', 'name' => SeoPageManager::META_CURRENCY_SLUG, 'type' => 'text', 'wrapper' => ['width' => '25']],
                ['key' => 'field_theme_seo_page_city_slug', 'label' => 'Slug города', 'name' => SeoPageManager::META_CITY_SLUG, 'type' => 'text', 'wrapper' => ['width' => '25']],
                ['key' => 'field_theme_seo_page_amount', 'label' => 'Сумма', 'name' => SeoPageManager::META_AMOUNT, 'type' => 'number', 'wrapper' => ['width' => '25']],
                ['key' => 'field_theme_seo_page_bank_slug', 'label' => 'Slug банка', 'name' => SeoPageManager::META_BANK_SLUG, 'type' => 'text', 'wrapper' => ['width' => '33']],
                ['key' => 'field_theme_seo_page_bank_code', 'label' => 'Код банка', 'name' => SeoPageManager::META_BANK_CODE, 'type' => 'text', 'readonly' => 1, 'wrapper' => ['width' => '33']],
                ['key' => 'field_theme_seo_page_import_batch', 'label' => 'ID импорта', 'name' => SeoPageManager::META_IMPORT_BATCH, 'type' => 'text', 'readonly' => 1, 'wrapper' => ['width' => '34']],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => SeoPageManager::POST_TYPE,
                    ],
                ],
            ],
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ]);
    }

    public static function validateUniqueUrl(mixed $valid, mixed $value, mixed $field, mixed $input): mixed
    {
        unset($field, $input);

        if ($valid !== true || trim((string) $value) === '') {
            return $valid;
        }

        $existing = SeoPageManager::getPageByUrl((string) $value);

        if (! $existing) {
            return $valid;
        }

        $currentId = isset($_POST['post_ID']) ? (int) $_POST['post_ID'] : 0;

        return $currentId > 0 && (int) $existing->ID === $currentId
            ? $valid
            : 'SEO страница с таким URL уже существует.';
    }
}
