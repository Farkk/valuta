<?php

declare(strict_types=1);

namespace Theme\Helpers;

use Theme\ACF\AcfManager;
use Theme\Banks\BankRegistry;

final class About
{
    /**
     * @return list<array{icon: string, title: string, text: string}>
     */
    public static function getBenefits(): array
    {
        return [
            [
                'icon'  => 'icon-time',
                'title' => __('Экономим ваше время', 'theme'),
                'text'  => __(
                    'Вам не нужно обзванивать банки и сравнивать условия вручную — мы собираем все предложения на одной странице.',
                    'theme'
                ),
            ],
            [
                'icon'  => 'icon-document',
                'title' => __('Упрощаем процесс выбора предложений', 'theme'),
                'text'  => __(
                    'Все условия написаны простым языком, чтобы вы могли быстро понять, какое предложение подходит именно вам.',
                    'theme'
                ),
            ],
            [
                'icon'  => 'icon-shield',
                'title' => __('Ничего от вас не скрываем', 'theme'),
                'text'  => __(
                    'Показываем скрытые условия и даём доступ к реальным отзывам клиентов, чтобы вы принимали решение осознанно.',
                    'theme'
                ),
            ],
        ];
    }

    /**
     * @return list<array{code: string, name: string, logo: string, url: string}>
     */
    public static function getPartners(): array
    {
        $partners = [];
        $logoMap = BankRegistry::getLogoMap();

        foreach (BankRegistry::getMap() as $code => $bank) {
            $name = trim((string) ($bank['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $logo = (string) ($logoMap[$code] ?? '');

            $partners[] = [
                'code' => (string) $code,
                'name' => $name,
                'logo' => $logo !== '' ? $logo : BankRegistry::getBankLogo((string) $code),
                'url'  => trim((string) ($bank['url'] ?? '')),
            ];
        }

        usort(
            $partners,
            static fn(array $a, array $b): int => strcasecmp($a['name'], $b['name'])
        );

        return $partners;
    }

    public static function getHeroImageUrl(int $postId): string
    {
        $image = AcfManager::getField('about_hero_image', $postId);

        if (is_array($image) && ! empty($image['url'])) {
            return (string) $image['url'];
        }

        if (is_numeric($image) && (int) $image > 0) {
            $url = wp_get_attachment_image_url((int) $image, 'full');

            return is_string($url) ? $url : '';
        }

        if (is_string($image) && $image !== '') {
            return $image;
        }

        $thumbnail = get_the_post_thumbnail_url($postId, 'full');

        return is_string($thumbnail) ? $thumbnail : '';
    }
}
