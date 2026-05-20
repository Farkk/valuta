<?php

declare(strict_types=1);

namespace Theme\Helpers;

/**
 * Вывод меню шапки, если к расположению «header» ещё не привязано меню WordPress.
 *
 * @see wp_nav_menu() — при срабатывании fallback_cb ядро не делает echo, колбэк обязан вывести HTML при echo=true.
 */
final class HeaderMenuFallback
{
    /**
     * @param array<string, mixed> $args аргументы {@see wp_nav_menu()}
     *
     * @return string|null HTML при echo=false, иначе null после вывода
     */
    public static function render(array $args): ?string
    {
        $menu_class = isset($args['menu_class']) ? (string) $args['menu_class'] : 'header__menu';
        $menu_id = isset($args['menu_id']) ? (string) $args['menu_id'] : 'header-menu';
        $echo = (bool) ($args['echo'] ?? true);

        /** @var list<array{label: string, url: string}> $items */
        $items = apply_filters('theme/header_menu_fallback_links', MenuLinks::headerItems());

        $pieces = [];
        $pieces[] = '<ul';
        if ($menu_id !== '') {
            $pieces[] = ' id="' . esc_attr($menu_id) . '"';
        }
        $pieces[] = ' class="' . esc_attr($menu_class) . '">';

        foreach ($items as $item) {
            $label = isset($item['label']) ? (string) $item['label'] : '';
            if ($label === '') {
                continue;
            }
            $url = isset($item['url']) ? (string) $item['url'] : '';
            if ($url === '') {
                $url = home_url('/');
            }
            $pieces[] = '<li class="menu-item menu-item-type-custom menu-item-object-custom">';
            $pieces[] = '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
            $pieces[] = '</li>';
        }

        $pieces[] = '</ul>';
        $html = implode('', $pieces);

        if ($echo) {
            echo $html;

            return null;
        }

        return $html;
    }
}
