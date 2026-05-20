<?php

declare(strict_types=1);

namespace Theme\SEO;

use Theme\Banks\BankManager;
use Theme\Cities\CityManager;
use Theme\Core\Contracts\ServiceProvider;
use Theme\Currencies\CurrencyRouter;
use Theme\Helpers\Text;

final class SeoManager implements ServiceProvider
{
    public function register(): void
    {
        add_filter('document_title_parts', [$this, 'filterDocumentTitle']);
        add_action('wp_head', [$this, 'renderMeta'], 1);
    }

    /**
     * @param array<string, string> $titleParts
     * @return array<string, string>
     */
    public function filterDocumentTitle(array $titleParts): array
    {
        if (SeoPageManager::hasCurrentPage()) {
            $titleParts['title'] = SeoPageManager::getCurrentValue('title', $titleParts['title'] ?? '');
            unset($titleParts['tagline']);

            return $titleParts;
        }

        if (BankManager::hasCurrentBank()) {
            $bank = BankManager::getCurrentBank();
            $titleParts['title'] = $this->getBankPageTitle($bank);
            unset($titleParts['tagline']);

            return $titleParts;
        }

        $routeTitle = $this->getRouteTitle();
        if ($routeTitle !== '') {
            $titleParts['title'] = $routeTitle;
            unset($titleParts['tagline']);

            return $titleParts;
        }

        if (is_front_page()) {
            $titleParts['title'] = get_bloginfo('name');
            $titleParts['tagline'] = get_bloginfo('description');
        }

        return $titleParts;
    }

    public function renderMeta(): void
    {
        if (is_admin()) {
            return;
        }

        $title = apply_filters('theme/seo/meta_title', wp_get_document_title());
        $description = apply_filters('theme/seo/meta_description', $this->getMetaDescription());
        $canonical = apply_filters('theme/seo/canonical', $this->getCanonicalUrl());
        $robots = apply_filters('theme/seo/robots', $this->getRobotsDirective());
        $schema = apply_filters('theme/seo/schema', $this->getSchema());

        printf("<meta name=\"description\" content=\"%s\">\n", esc_attr($description));
        printf("<link rel=\"canonical\" href=\"%s\">\n", esc_url($canonical));
        printf("<meta name=\"robots\" content=\"%s\">\n", esc_attr($robots));
        printf("<meta property=\"og:title\" content=\"%s\">\n", esc_attr($title));
        printf("<meta property=\"og:description\" content=\"%s\">\n", esc_attr($description));
        printf("<meta property=\"og:type\" content=\"website\">\n");
        printf("<meta property=\"og:url\" content=\"%s\">\n", esc_url($canonical));
        printf("<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr(get_bloginfo('name')));
        printf(
            "<script type=\"application/ld+json\">%s</script>\n",
            wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    private function getMetaDescription(): string
    {
        if (SeoPageManager::hasCurrentPage()) {
            return SeoPageManager::getCurrentValue('description', (string) get_bloginfo('description'));
        }

        if (BankManager::hasCurrentBank()) {
            $bank = BankManager::getCurrentBank();
            $description = wp_strip_all_tags((string) ($bank['bank_description'] ?? ''));

            if ($description !== '') {
                return wp_trim_words($description, 30);
            }

            return $this->getBankPageDescription($bank);
        }

        $routeDescription = $this->getRouteDescription();
        if ($routeDescription !== '') {
            return $routeDescription;
        }

        if (is_singular()) {
            $excerpt = trim(wp_strip_all_tags(get_the_excerpt()));

            if ($excerpt !== '') {
                return $excerpt;
            }
        }

        return (string) get_bloginfo('description');
    }

    private function getCanonicalUrl(): string
    {
        if (SeoPageManager::hasCurrentPage()) {
            return SeoPageManager::getCurrentValue('canonical', home_url('/'));
        }

        if (BankManager::hasCurrentBank()) {
            $bank = BankManager::getCurrentBank();

            return BankManager::getPageUrl(
                (string) ($bank['bank_code'] ?? ''),
                (float) ($bank['amount'] ?? 0),
                (string) ($bank['currency'] ?? ''),
                (string) ($bank['tab'] ?? '')
            );
        }

        if (is_singular()) {
            return (string) get_permalink();
        }

        if (is_home() || is_front_page()) {
            return home_url('/');
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';

        return home_url($requestUri);
    }

    private function getRobotsDirective(): string
    {
        if (is_search() || is_404()) {
            return 'noindex,follow';
        }

        return 'index,follow';
    }

    private function getRouteTitle(): string
    {
        $citySlug = (string) get_query_var('city', '');

        if ($citySlug !== '') {
            $city = CityManager::getCityBySlug($citySlug);

            if ($city !== null && function_exists('get_field')) {
                $template = (string) get_field('template_city', 'option');

                if ($template !== '') {
                    return str_replace('$', Text::cityPrepositional((string) $city['city_name']), $template);
                }
            }
        }

        if (CurrencyRouter::isCurrencyPage()) {
            $currency = strtoupper(CurrencyRouter::getCurrentCurrency());

            return $currency . ' - Обмен валют';
        }

        return '';
    }

    private function getRouteDescription(): string
    {
        $citySlug = (string) get_query_var('city', '');

        if ($citySlug === '' || ! function_exists('get_field')) {
            return '';
        }

        $city = CityManager::getCityBySlug($citySlug);
        $template = (string) get_field('template_city_desc', 'option');

        if ($city === null || $template === '') {
            return '';
        }

        return str_replace('$', Text::cityPrepositional((string) $city['city_name']), $template);
    }

    /**
     * @return array<string, mixed>
     */
    private function getSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
            'description' => get_bloginfo('description'),
        ];
    }

    /**
     * @param array<string, mixed> $bank
     */
    private function getBankPageTitle(array $bank): string
    {
        $bankName = (string) ($bank['bank_name'] ?? '');
        $amount = (int) round((float) ($bank['amount'] ?? 0));
        $currency = (string) ($bank['currency'] ?? '');

        if ($amount > 0 && $currency !== '') {
            $forms = SeoTemplateRenderer::getCurrencyForms($currency);
            $currencyLabel = $amount === 1
                ? (string) ($forms['currency_genitive'] ?? strtoupper($currency))
                : (string) ($forms['currency_plural_genitive'] ?? strtoupper($currency));

            return sprintf(
                __('%s — %d %s, курсы и отделения', 'theme'),
                $bankName,
                $amount,
                $currencyLabel
            );
        }

        return sprintf(
            __('%s — курсы валют и отделения', 'theme'),
            $bankName
        );
    }

    /**
     * @param array<string, mixed> $bank
     */
    private function getBankPageDescription(array $bank): string
    {
        $bankName = (string) ($bank['bank_name'] ?? '');
        $amount = (int) round((float) ($bank['amount'] ?? 0));
        $currency = (string) ($bank['currency'] ?? '');

        if ($amount > 0 && $currency !== '') {
            $forms = SeoTemplateRenderer::getCurrencyForms($currency);
            $currencyLabel = $amount === 1
                ? (string) ($forms['currency_genitive'] ?? strtoupper($currency))
                : (string) ($forms['currency_plural_genitive'] ?? strtoupper($currency));

            return sprintf(
                __('Курсы и отделения %1$s: расчёт обмена %2$d %3$s по актуальным котировкам.', 'theme'),
                $bankName,
                $amount,
                $currencyLabel
            );
        }

        return sprintf(
            __('Актуальные курсы валют и отделения %s. Сравнение условий обмена.', 'theme'),
            $bankName
        );
    }
}
