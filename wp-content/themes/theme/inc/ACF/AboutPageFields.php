<?php

declare(strict_types=1);

namespace Theme\ACF;

use Theme\Core\Contracts\ServiceProvider;

final class AboutPageFields implements ServiceProvider
{
    public function register(): void
    {
        add_action('acf/init', [$this, 'registerFieldGroups']);
    }

    public function registerFieldGroups(): void
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_theme_about_page',
            'title' => 'О проекте',
            'fields' => [
                [
                    'key' => 'field_about_hero_image',
                    'label' => 'Изображение в шапке',
                    'name' => 'about_hero_image',
                    'type' => 'image',
                    'instructions' => 'Рекомендуемый размер: 663×323 px.',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'page_template',
                        'operator' => '==',
                        'value' => 'page-templates/about.php',
                    ],
                ],
            ],
        ]);
    }
}
