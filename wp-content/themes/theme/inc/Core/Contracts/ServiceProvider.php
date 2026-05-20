<?php

declare(strict_types=1);

namespace Theme\Core\Contracts;

interface ServiceProvider
{
    public function register(): void;
}

