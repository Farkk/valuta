<?php

declare(strict_types=1);

namespace Theme\Ajax;

use Theme\Cities\CityManager;
use Theme\Core\Contracts\ServiceProvider;
use Theme\Banks\BankOffices;
use Theme\Banks\BankRegistry;
use Theme\Helpers\PostTypeContent;
use Theme\Helpers\Reviews;
use Theme\Rates\RatesManager;

final class AjaxManager implements ServiceProvider
{
    public function register(): void
    {
        add_action('wp_ajax_theme_ping', [$this, 'handlePing']);
        add_action('wp_ajax_nopriv_theme_ping', [$this, 'handlePing']);
        add_action('wp_ajax_filter_banks_by_currency', [$this, 'filterBanksByCurrency']);
        add_action('wp_ajax_nopriv_filter_banks_by_currency', [$this, 'filterBanksByCurrency']);
        add_action('wp_ajax_load_more_banks', [$this, 'loadMoreBanks']);
        add_action('wp_ajax_nopriv_load_more_banks', [$this, 'loadMoreBanks']);
        add_action('wp_ajax_get_bank_map_offices', [$this, 'getBankMapOffices']);
        add_action('wp_ajax_nopriv_get_bank_map_offices', [$this, 'getBankMapOffices']);
        add_action('wp_ajax_get_bank_offices', [$this, 'getBankOffices']);
        add_action('wp_ajax_nopriv_get_bank_offices', [$this, 'getBankOffices']);
        add_action('wp_ajax_load_more_reviews', [$this, 'loadMoreReviews']);
        add_action('wp_ajax_nopriv_load_more_reviews', [$this, 'loadMoreReviews']);
        add_action('wp_ajax_load_more_news', [$this, 'loadMoreNews']);
        add_action('wp_ajax_nopriv_load_more_news', [$this, 'loadMoreNews']);
        add_action('wp_ajax_load_more_articles', [$this, 'loadMoreArticles']);
        add_action('wp_ajax_nopriv_load_more_articles', [$this, 'loadMoreArticles']);
    }

    public function handlePing(): void
    {
        check_ajax_referer('theme_nonce', 'nonce');

        wp_send_json_success(
            [
                'message' => __('Theme AJAX endpoint is ready.', 'theme'),
            ]
        );
    }

    public function filterBanksByCurrency(): void
    {
        $currency = $this->postString('currency', 'usd');
        $tab = $this->sanitizeTab($this->postString('tab', 'all'));
        $amount = $this->postAmount();
        $filters = $this->postFilters();
        $sort = $this->sanitizeSort($this->postString('sort', 'default'));
        $city = $this->getAjaxCity();

        $rates = RatesManager::getRates($city['city_name'], $currency);
        $rawBanks = RatesManager::getRawBanks($rates);

        if ($rawBanks === []) {
            wp_send_json_success(['html' => '', 'total_pages' => 0, 'total_banks' => 0, 'map_offices' => []]);
        }

        $banks = $this->applySort(RatesManager::sortAndGroupBanks($rawBanks, $tab, $filters), $sort);
        $totalBanks = count($banks);
        $totalPages = $this->totalPages($totalBanks);
        $timeUpdate = RatesManager::formatUpdateTime($rates);

        wp_send_json_success([
            'html'        => $this->renderBanks(array_slice($banks, 0, 8), $tab, $amount, $timeUpdate, $currency, 0),
            'total_pages' => $totalPages,
            'total_banks' => $totalBanks,
            'updated_at'  => $timeUpdate,
            'map_offices' => RatesManager::getMapOffices($rawBanks, $tab, $filters),
        ]);
    }

    public function loadMoreBanks(): void
    {
        $page = max(1, (int) $this->postString('page', '1'));
        if ($page === 1) {
            wp_send_json_error(['message' => 'First page is rendered on initial load.']);
        }

        $currency = $this->postString('currency', 'usd');
        $tab = $this->sanitizeTab($this->postString('tab', 'all'));
        $amount = $this->postAmount();
        $filters = $this->postFilters();
        $sort = $this->sanitizeSort($this->postString('sort', 'default'));
        $city = $this->getAjaxCity();

        $rates = RatesManager::getRates($city['city_name'], $currency);
        $banks = $this->applySort(RatesManager::sortAndGroupBanks(RatesManager::getRawBanks($rates), $tab, $filters), $sort);

        $offset = 8 + (($page - 2) * 5);
        $banksPage = array_slice($banks, $offset, 5);

        if ($banksPage === []) {
            wp_send_json_error(['message' => 'No more banks.']);
        }

        wp_send_json_success([
            'html'     => $this->renderBanks($banksPage, $tab, $amount, RatesManager::formatUpdateTime($rates), $currency, $offset),
            'page'     => $page,
            'has_more' => count($banks) > ($offset + 5),
        ]);
    }

    public function getBankMapOffices(): void
    {
        $bankCode = $this->postString('bank_code', '');
        if ($bankCode === '') {
            wp_send_json_error(['message' => 'bank_code required']);
        }

        $currency = $this->postString('currency', 'usd');
        $tab = $this->sanitizeTab($this->postString('tab', 'all'));
        $filters = $this->postFilters();
        $city = $this->getAjaxCity();
        $rates = RatesManager::getRates($city['city_name'], $currency);
        $offices = BankOffices::collect(RatesManager::getRawBanks($rates), $bankCode, $filters, true, $city['city_name']);

        if ($offices === []) {
            wp_send_json_error(['message' => 'No offices with coordinates found']);
        }

        wp_send_json_success([
            'offices' => BankOffices::sort($offices, $tab, $bankCode),
            'bank_code' => $bankCode,
            'bank_url' => BankRegistry::getBankUrl($bankCode),
            'city' => $city['city_name'],
        ]);
    }

    public function getBankOffices(): void
    {
        $bankCode = $this->postString('bank_code', '');
        if ($bankCode === '') {
            wp_send_json_error(['message' => 'bank_code required']);
        }

        $currency = $this->postString('currency', 'usd');
        $tab = $this->sanitizeTab($this->postString('tab', 'all'));
        $filters = $this->postFilters();
        $city = $this->getAjaxCity();
        $rates = RatesManager::getRates($city['city_name'], $currency);
        $offices = BankOffices::collect(RatesManager::getRawBanks($rates), $bankCode, $filters, false, $city['city_name']);

        if ($offices === []) {
            wp_send_json_error(['message' => 'No offices found']);
        }

        wp_send_json_success([
            'offices' => BankOffices::sort($offices, $tab, $bankCode),
            'bank_code' => $bankCode,
            'bank_url' => BankRegistry::getBankUrl($bankCode),
            'city' => $city['city_name'],
        ]);
    }

    public function loadMoreNews(): void
    {
        $this->loadMoreContent('news');
    }

    public function loadMoreArticles(): void
    {
        $this->loadMoreContent('articles');
    }

    private function loadMoreContent(string $contentType): void
    {
        if (! PostTypeContent::isSupported($contentType)) {
            wp_send_json_error(['message' => 'Unsupported content type.']);
        }

        $config = PostTypeContent::getConfig($contentType);
        $page = max(1, (int) $this->postString('page', '1'));
        $tag = sanitize_title($this->postString('tag', ''));

        $queryArgs = [
            'post_type' => $config['post_type'],
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => $page,
        ];

        if ($tag !== '') {
            $queryArgs['tax_query'] = [
                [
                    'taxonomy' => $config['taxonomy'],
                    'field' => 'slug',
                    'terms' => $tag,
                ],
            ];
        }

        $query = new \WP_Query($queryArgs);

        if (! $query->have_posts()) {
            wp_send_json_error(['message' => 'No more posts.']);
        }

        ob_start();

        while ($query->have_posts()) {
            $query->the_post();
            echo '<li class="news-archive__item">';
            get_template_part(
                'template-parts/components/news-card',
                null,
                PostTypeContent::getCardData(get_post())
            );
            echo '</li>';
        }

        wp_reset_postdata();

        wp_send_json_success([
            'html' => (string) ob_get_clean(),
            'page' => $page,
            'has_more' => $page < (int) $query->max_num_pages,
        ]);
    }

    public function loadMoreReviews(): void
    {
        $page = max(1, (int) $this->postString('page', '1'));
        $city = CityManager::getCurrentCity();
        $cityName = (string) ($city['city_name'] ?? '');

        $query = new \WP_Query([
            'post_type' => 'reviews',
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => $page,
        ]);

        if (! $query->have_posts()) {
            wp_send_json_error(['message' => 'No more reviews.']);
        }

        ob_start();

        while ($query->have_posts()) {
            $query->the_post();
            get_template_part(
                'template-parts/components/review-card',
                null,
                array_merge(
                    Reviews::getReviewCardData(get_post(), $cityName),
                    ['modifier' => 'archive']
                )
            );
        }

        wp_reset_postdata();

        wp_send_json_success([
            'html' => (string) ob_get_clean(),
            'page' => $page,
            'has_more' => $page < (int) $query->max_num_pages,
        ]);
    }

    private function renderBanks(array $banks, string $tab, float $amount, string $timeUpdate, string $currency, int $offset): string
    {
        ob_start();

        foreach ($banks as $index => $bank) {
            $globalIndex = $offset + $index;
            $badges = $this->getBankBadges($bank, $tab, $currency, $globalIndex === 0);

            echo '<li class="banks-list-layout__list-item">';
            get_template_part('template-parts/components/bank-rate-card', null, array_merge(
                RatesManager::formatCardParams($bank, $tab, $amount),
                [
                    'bank_code'    => $bank['bank_code'] ?? '',
                    'bank_name'    => $bank['bank_name'] ?? '',
                    'logo_url'     => $bank['bank_logo'] ?? '',
                    'bank_url'     => $bank['bank_url'] ?? '',
                    'updated_at'   => $timeUpdate,
                    'badges'       => $badges,
                    'cta_variant'  => 'details',
                    'cta_url'      => ($bank['bank_url'] ?? '') !== '' ? $bank['bank_url'] : '#',
                    'link_amount'  => $amount,
                    'link_currency' => $currency,
                    'link_tab'     => $tab,
                    'show_disclaimer' => $tab === 'sell' || $tab === 'buy',
                ]
            ));
            echo '</li>';
        }

        return (string) ob_get_clean();
    }

    private function getBankBadges(array $bank, string $tab, string $currency, bool $isBest): array
    {
        $badges = $isBest ? [__('Лучший курс', 'theme')] : [];

        if (($bank['bank_code'] ?? '') !== 'kamkombank') {
            return $badges;
        }

        foreach (\Theme\Filters\FilterRegistry::getFiltersForTab($tab, $currency) as $filter) {
            $slug = (string) ($filter['slug'] ?? '');
            if ($slug !== '' && ! empty($bank[$slug])) {
                $badges[] = (string) ($filter['name'] ?? '');
            }
        }

        return array_values(array_unique(array_filter($badges)));
    }

    private function totalPages(int $totalBanks): int
    {
        return $totalBanks <= 8 ? 1 : 1 + (int) ceil(($totalBanks - 8) / 5);
    }

    private function getAjaxCity(): array
    {
        $citySlug = $this->postString('city_slug', '');
        if ($citySlug !== '') {
            $city = CityManager::getCityBySlug($citySlug);
            if ($city !== null) {
                return $city;
            }
        }

        $cityName = $this->postString('city_name', '');
        if ($cityName !== '') {
            foreach (CityManager::getCities() as $city) {
                if (($city['city_name'] ?? '') === $cityName) {
                    return $city;
                }
            }
        }

        return CityManager::getCurrentCity();
    }

    private function postString(string $key, string $default): string
    {
        return isset($_POST[$key]) ? sanitize_text_field(wp_unslash((string) $_POST[$key])) : $default;
    }

    private function postAmount(): float
    {
        $amount = $this->postString('amount', '0');
        $amount = str_replace([' ', ','], ['', '.'], $amount);

        return max(0.0, (float) $amount);
    }

    private function postFilters(): array
    {
        if (empty($_POST['filters'])) {
            return [];
        }

        $filters = json_decode(stripslashes((string) $_POST['filters']), true);

        return is_array($filters) ? array_values(array_filter(array_map('sanitize_key', $filters))) : [];
    }

    private function sanitizeTab(string $tab): string
    {
        return in_array($tab, ['all', 'sell', 'buy'], true) ? $tab : 'all';
    }

    private function sanitizeSort(string $sort): string
    {
        return in_array($sort, ['', 'default', 'buy_desc', 'sell_asc'], true) ? $sort : '';
    }

    private function applySort(array $banks, string $sort): array
    {
        if ($sort === 'buy_desc') {
            usort($banks, static fn(array $a, array $b): int => ($b['buy_raw'] ?? 0) <=> ($a['buy_raw'] ?? 0));
        }

        if ($sort === 'sell_asc') {
            usort($banks, static fn(array $a, array $b): int => ($a['sell_raw'] ?? 0) <=> ($b['sell_raw'] ?? 0));
        }

        return $banks;
    }
}
