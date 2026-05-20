<?php

declare(strict_types=1);

namespace Theme\Core;

final class Autoloader
{
    private const NAMESPACE_PREFIX = 'Theme\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $class): void
    {
        if (! str_starts_with($class, self::NAMESPACE_PREFIX)) {
            return;
        }

        $relativeClass = substr($class, strlen(self::NAMESPACE_PREFIX));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        $file = THEME_PATH . '/inc/' . $relativePath;

        if (file_exists($file)) {
            require_once $file;
        }
    }
}

