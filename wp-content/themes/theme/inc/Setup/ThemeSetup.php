<?php

declare(strict_types=1);

namespace Theme\Setup;

use Theme\Core\Contracts\ServiceProvider;
use Theme\Helpers\MenuLinks;
use Theme\Helpers\Template;

final class ThemeSetup implements ServiceProvider
{
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'setup']);
        add_action('init', [$this, 'cleanupHead']);
        add_action('wp_before_load_template', [Template::class, 'pushPartArgs'], 0, 3);
        add_action('wp_after_load_template', [Template::class, 'popPartArgs'], 999);
        add_filter('wp_nav_menu_objects', [MenuLinks::class, 'normalizeHeaderMenuItems'], 10, 2);
    }

    public function setup(): void
    {
        load_theme_textdomain('theme', THEME_PATH . '/languages');

        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo');
        add_theme_support('customize-selective-refresh-widgets');
        add_theme_support('responsive-embeds');
        add_theme_support('align-wide');
        add_theme_support('editor-styles');
        add_theme_support(
            'html5',
            [
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'search-form',
                'script',
                'style',
            ]
        );

        register_nav_menus(
            [
                'primary' => __('Primary Menu', 'theme'),
                'header'  => __('Header Menu', 'theme'),
                'footer'  => __('Footer Menu', 'theme'),
            ]
        );
    }

    public function cleanupHead(): void
    {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        add_filter('use_widgets_block_editor', '__return_false');
        add_filter('use_block_editor_for_post_type', [$this, 'useBlockEditor'], 10, 2);
        add_filter('should_load_remote_block_patterns', '__return_false');
    }

    public function useBlockEditor(bool $useBlockEditor, string $postType): bool
    {
        return apply_filters('theme/use_block_editor', $useBlockEditor, $postType);
    }
}

