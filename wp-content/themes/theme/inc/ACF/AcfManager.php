<?php

declare(strict_types=1);

namespace Theme\ACF;

use Theme\Core\Contracts\ServiceProvider;

final class AcfManager implements ServiceProvider
{
    public function register(): void
    {
        add_action('acf/init', [$this, 'registerOptionsPages']);
        add_action('acf/init', [$this, 'registerFieldGroups']);
        add_action('acf/init', [$this, 'registerBlocks']);
    }

    public function registerOptionsPages(): void
    {
        if (! function_exists('acf_add_options_page')) {
            return;
        }

        acf_add_options_page(
            [
                'page_title' => __('Theme Settings', 'theme'),
                'menu_title' => __('Theme Settings', 'theme'),
                'menu_slug'  => 'theme-settings',
                'capability' => 'edit_theme_options',
                'redirect'   => false,
            ]
        );
    }

    public function registerFieldGroups(): void
    {
        do_action('theme/acf/register_field_groups');
    }

    public function registerBlocks(): void
    {
        do_action('theme/acf/register_blocks');
    }

    public static function getField(string $selector, int|string|false $postId = false, mixed $default = null): mixed
    {
        if (! function_exists('get_field')) {
            return $default;
        }

        $value = get_field($selector, $postId);

        return $value !== null && $value !== false ? $value : $default;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getFlexibleRows(string $selector, int|string|false $postId = false): array
    {
        $rows = self::getField($selector, $postId, []);

        return is_array($rows) ? $rows : [];
    }
}

