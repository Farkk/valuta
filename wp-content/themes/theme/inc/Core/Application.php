<?php

declare(strict_types=1);

namespace Theme\Core;

use Theme\ACF\AboutPageFields;
use Theme\ACF\AcfManager;
use Theme\ACF\RegistryFields;
use Theme\Ajax\AjaxManager;
use Theme\Assets\AssetsManager;
use Theme\Banks\BankRouter;
use Theme\Cities\CityRouter;
use Theme\Content\ContentRegistrar;
use Theme\Content\ContentTypeRouter;
use Theme\Core\Contracts\ServiceProvider;
use Theme\Currencies\CurrencyRouter;
use Theme\SEO\SeoManager;
use Theme\SEO\SeoRouter;
use Theme\Setup\ThemeSetup;
use Theme\VirtualPages\VirtualPageManager;

final class Application
{
    /**
     * @var list<class-string<ServiceProvider>>
     */
    private array $services = [
        ThemeSetup::class,
        AssetsManager::class,
        ContentRegistrar::class,
        ContentTypeRouter::class,
        SeoManager::class,
        AcfManager::class,
        RegistryFields::class,
        AboutPageFields::class,
        AjaxManager::class,
        SeoRouter::class,
        CurrencyRouter::class,
        CityRouter::class,
        BankRouter::class,
        VirtualPageManager::class,
    ];

    public function boot(): void
    {
        $services = apply_filters('theme/service_providers', $this->services);

        foreach ($services as $serviceClass) {
            if (! class_exists($serviceClass)) {
                continue;
            }

            $service = new $serviceClass();

            if ($service instanceof ServiceProvider) {
                $service->register();
            }
        }
    }
}
