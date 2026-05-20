<?php

declare(strict_types=1);

namespace Theme\Helpers;

final class Debug
{
    public static function enabled(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
}

