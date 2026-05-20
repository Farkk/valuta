<?php

declare(strict_types=1);

namespace Theme\ACF;

use Theme\Banks\BankRegistry;
use Theme\Core\Contracts\ServiceProvider;
use Theme\Filters\FilterRegistry;

final class RegistryFields implements ServiceProvider
{
    public function register(): void
    {
        add_action('acf/init', [$this, 'registerOptionsPages']);
        add_action('acf/init', [$this, 'registerFieldGroups']);
        add_action('acf/save_post', [$this, 'invalidateCaches']);
    }

    public function registerOptionsPages(): void
    {
        if (! function_exists('acf_add_options_sub_page')) {
            return;
        }

        acf_add_options_sub_page([
            'page_title'  => 'Реестр банков',
            'menu_title'  => 'Банки',
            'menu_slug'   => 'theme-banks',
            'parent_slug' => 'theme-settings',
            'capability'  => 'edit_theme_options',
            'redirect'    => false,
        ]);

        acf_add_options_sub_page([
            'page_title'  => 'Реестр фильтров',
            'menu_title'  => 'Фильтры',
            'menu_slug'   => 'theme-filters',
            'parent_slug' => 'theme-settings',
            'capability'  => 'edit_theme_options',
            'redirect'    => false,
        ]);

        acf_add_options_sub_page([
            'page_title'  => 'Реестр валют',
            'menu_title'  => 'Валюты',
            'menu_slug'   => 'theme-currencies',
            'parent_slug' => 'theme-settings',
            'capability'  => 'edit_theme_options',
            'redirect'    => false,
        ]);

        acf_add_options_sub_page([
            'page_title'  => 'Главная страница',
            'menu_title'  => 'Главная',
            'menu_slug'   => 'theme-homepage',
            'parent_slug' => 'theme-settings',
            'capability'  => 'edit_theme_options',
            'redirect'    => false,
        ]);
    }

    public function registerFieldGroups(): void
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        $this->registerBankFields();
        $this->registerFilterFields();
        $this->registerCurrencyFields();
        $this->registerHomepageFields();
        $this->registerReviewFields();
    }

    public function invalidateCaches(mixed $postId): void
    {
        if ($postId !== 'options') {
            return;
        }

        BankRegistry::invalidateCache();
        FilterRegistry::invalidateCache();
        delete_transient('theme_currencies_list');
    }

    private function registerBankFields(): void
    {
        acf_add_local_field_group([
            'key' => 'group_theme_bank_registry',
            'title' => 'Реестр банков',
            'fields' => [
                [
                    'key' => 'field_theme_bank_list',
                    'label' => 'Список банков',
                    'name' => 'bank_list',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Добавить банк',
                    'sub_fields' => [
                        ['key' => 'field_theme_bank_code', 'label' => 'Код', 'name' => 'bank_code', 'type' => 'text', 'wrapper' => ['width' => '20']],
                        ['key' => 'field_theme_bank_name', 'label' => 'Название', 'name' => 'bank_name', 'type' => 'text', 'wrapper' => ['width' => '30']],
                        [
                            'key' => 'field_theme_bank_logo',
                            'label' => 'Логотип',
                            'name' => 'bank_logo',
                            'type' => 'image',
                            'return_format' => 'url',
                            'preview_size' => 'thumbnail',
                            'wrapper' => ['width' => '25'],
                        ],
                        ['key' => 'field_theme_bank_url', 'label' => 'Ссылка на банк', 'name' => 'bank_url', 'type' => 'url', 'wrapper' => ['width' => '25']],
                        [
                            'key' => 'field_theme_bank_description',
                            'label' => 'Описание',
                            'name' => 'bank_description',
                            'type' => 'wysiwyg',
                            'tabs' => 'all',
                            'toolbar' => 'basic',
                            'media_upload' => 0,
                            'wrapper' => ['width' => '100'],
                        ],
                    ],
                ],
            ],
            'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'theme-banks']]],
            'active' => true,
        ]);
    }

    private function registerFilterFields(): void
    {
        acf_add_local_field_group([
            'key' => 'group_theme_filter_registry',
            'title' => 'Реестр фильтров',
            'fields' => [
                [
                    'key' => 'field_theme_default_tab',
                    'label' => 'Таб по умолчанию',
                    'name' => 'default_tab',
                    'type' => 'select',
                    'instructions' => 'Выберите, какая вкладка будет активна при загрузке страницы.',
                    'choices' => [
                        'buy' => 'Я покупаю',
                        'sell' => 'Я продаю',
                        'all' => 'Все курсы',
                    ],
                    'default_value' => 'buy',
                    'return_format' => 'value',
                    'wrapper' => ['width' => '50'],
                ],
                [
                    'key' => 'field_theme_filter_list',
                    'label' => 'Список фильтров',
                    'name' => 'filter_list',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Добавить фильтр',
                    'sub_fields' => [
                        ['key' => 'field_theme_filter_name', 'label' => 'Название', 'name' => 'filter_name', 'type' => 'text', 'required' => 1, 'wrapper' => ['width' => '25']],
                        ['key' => 'field_theme_filter_slug', 'label' => 'Slug для API', 'name' => 'filter_slug', 'type' => 'text', 'required' => 1, 'wrapper' => ['width' => '18']],
                        ['key' => 'field_theme_filter_is_active', 'label' => 'Активен', 'name' => 'is_active', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'wrapper' => ['width' => '10']],
                        ['key' => 'field_theme_filter_show_in_buy', 'label' => 'Я покупаю', 'name' => 'show_in_buy', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => '12']],
                        ['key' => 'field_theme_filter_show_in_sell', 'label' => 'Я продаю', 'name' => 'show_in_sell', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => '12']],
                        ['key' => 'field_theme_filter_checked_by_default', 'label' => 'По умолчанию', 'name' => 'checked_by_default', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => '13']],
                        [
                            'key' => 'field_theme_filter_currency_codes',
                            'label' => 'Валюты',
                            'name' => 'currency_codes',
                            'type' => 'text',
                            'instructions' => 'Коды через запятую, например: usd, eur. Если пусто, фильтр показывается для всех валют.',
                            'wrapper' => ['width' => '100'],
                        ],
                    ],
                ],
            ],
            'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'theme-filters']]],
            'active' => true,
        ]);
    }

    private function registerCurrencyFields(): void
    {
        acf_add_local_field_group([
            'key' => 'group_theme_currency_registry',
            'title' => 'Реестр валют',
            'fields' => [
                [
                    'key' => 'field_theme_currency_list',
                    'label' => 'Список валют',
                    'name' => 'currency_list',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Добавить валюту',
                    'sub_fields' => [
                        ['key' => 'field_theme_currency_code', 'label' => 'Код', 'name' => 'currency_code', 'type' => 'text', 'wrapper' => ['width' => '25']],
                        [
                            'key' => 'field_theme_currency_flag',
                            'label' => 'Иконка / флаг',
                            'name' => 'currency_flag',
                            'type' => 'image',
                            'return_format' => 'url',
                            'preview_size' => 'thumbnail',
                            'wrapper' => ['width' => '75'],
                        ],
                    ],
                ],
            ],
            'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'theme-currencies']]],
            'active' => true,
        ]);
    }

    private function registerHomepageFields(): void
    {
        acf_add_local_field_group([
            'key' => 'group_theme_homepage_content',
            'title' => 'Контент главной страницы',
            'fields' => [
                [
                    'key' => 'field_theme_template_title',
                    'label' => 'Шаблон H1 для страницы города',
                    'name' => 'template_title',
                    'type' => 'text',
                    'instructions' => 'Используйте `$` для города.',
                    'default_value' => 'Найди лучший курс<br />валют <span>в $</span>',
                ],
                [
                    'key' => 'field_theme_template_title_value',
                    'label' => 'Шаблон H1 для страницы валюты',
                    'name' => 'template_title_value',
                    'type' => 'text',
                    'instructions' => 'Используйте `$` для кода валюты и `$city` для города.',
                    'default_value' => 'Курсы обмена $ в банках <span>в $city</span>',
                ],
                [
                    'key' => 'field_theme_template_title_usd',
                    'label' => 'Шаблон H1 для страницы пары валют',
                    'name' => 'template_title_usd',
                    'type' => 'text',
                    'instructions' => 'Используйте `$` для суммы и валюты, `$value` для кода валюты и `$city` для города.',
                    'default_value' => '$ в российских рублях (RUB) <span>в $city</span>',
                ],
                [
                    'key' => 'field_theme_exchange_rate_group',
                    'label' => 'Нижний SEO блок',
                    'name' => 'exchange-rate',
                    'type' => 'group',
                    'layout' => 'block',
                    'sub_fields' => [
                        [
                            'key' => 'field_theme_exchange_rate_title',
                            'label' => 'Заголовок',
                            'name' => 'exchange-rate_title',
                            'type' => 'text',
                            'default_value' => 'Где выгодно обменять валюту в России в 2026 году',
                        ],
                        [
                            'key' => 'field_theme_exchange_rate_desc',
                            'label' => 'Описание',
                            'name' => 'exchange-rate_desc',
                            'type' => 'wysiwyg',
                            'tabs' => 'all',
                            'toolbar' => 'basic',
                            'media_upload' => 0,
                        ],
                    ],
                ],
                [
                    'key' => 'field_theme_faq_group',
                    'label' => 'FAQ',
                    'name' => 'faq',
                    'type' => 'group',
                    'layout' => 'block',
                    'sub_fields' => [
                        [
                            'key' => 'field_theme_faq_title',
                            'label' => 'Заголовок',
                            'name' => 'title',
                            'type' => 'text',
                            'default_value' => 'Ответы на часто задаваемые вопросы',
                        ],
                        [
                            'key' => 'field_theme_faq_items',
                            'label' => 'Вопросы',
                            'name' => 'items',
                            'type' => 'repeater',
                            'layout' => 'row',
                            'button_label' => 'Добавить вопрос',
                            'sub_fields' => [
                                [
                                    'key' => 'field_theme_faq_question',
                                    'label' => 'Вопрос',
                                    'name' => 'question',
                                    'type' => 'text',
                                    'required' => 1,
                                ],
                                [
                                    'key' => 'field_theme_faq_answer',
                                    'label' => 'Ответ',
                                    'name' => 'answer',
                                    'type' => 'wysiwyg',
                                    'tabs' => 'all',
                                    'toolbar' => 'basic',
                                    'media_upload' => 0,
                                    'required' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'theme-homepage']]],
            'active' => true,
        ]);
    }

    private function registerReviewFields(): void
    {
        acf_add_local_field_group([
            'key' => 'group_theme_reviews',
            'title' => 'Информация об отзыве',
            'fields' => [
                [
                    'key' => 'field_theme_review_author_name',
                    'label' => 'Имя автора',
                    'name' => 'review_author_name',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_theme_review_rating',
                    'label' => 'Рейтинг',
                    'name' => 'review_rating',
                    'type' => 'range',
                    'required' => 1,
                    'default_value' => 5,
                    'min' => 1,
                    'max' => 5,
                    'step' => 1,
                    'prepend' => '★',
                ],
                [
                    'key' => 'field_theme_review_bank',
                    'label' => 'Банк',
                    'name' => 'review_bank',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_theme_review_city',
                    'label' => 'Город',
                    'name' => 'review_city',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_theme_review_date',
                    'label' => 'Дата отзыва',
                    'name' => 'review_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'display_format' => 'd.m.Y',
                    'return_format' => 'd.m.Y',
                    'first_day' => 1,
                ],
            ],
            'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'reviews']]],
            'active' => true,
        ]);
    }
}
