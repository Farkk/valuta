<?php

declare(strict_types=1);

namespace Theme\Helpers;

final class Template
{
    /**
     * @var list<array<string, mixed>>
     */
    private static array $partArgsStack = [];

    /**
     * @param array<string, mixed> $args
     */
    public static function part(string $slug, ?string $name = null, array $args = []): void
    {
        get_template_part($slug, $name, $args);
    }

    public static function asset(string $path): string
    {
        return trailingslashit(THEME_URI) . ltrim($path, '/');
    }

    public static function breadcrumbs(string $class = ''): void
    {
        if (! Breadcrumbs::shouldRender()) {
            return;
        }

        self::part('template-parts/components/breadcrumbs', null, [
            'class' => $class,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function currentPartArgs(): array
    {
        if (self::$partArgsStack === []) {
            return [];
        }

        $lastKey = array_key_last(self::$partArgsStack);

        return self::$partArgsStack[$lastKey];
    }

    /**
     * @param array<string, mixed> $args
     */
    public static function pushPartArgs(string $file, bool $loadOnce, array $args): void
    {
        self::$partArgsStack[] = $args;
    }

    public static function popPartArgs(string $file = '', bool $loadOnce = true, array $args = []): void
    {
        array_pop(self::$partArgsStack);
    }
}

