<?php

declare(strict_types=1);

namespace Theme\SEO;

use Theme\Core\Contracts\ServiceProvider;

final class SeoAdminService implements ServiceProvider
{
    public function register(): void
    {
        if (is_admin()) {
            SeoImportAdmin::register();
        }

        SeoSitemapManager::register();
        SeoWpSitemapProvider::register();
    }
}
