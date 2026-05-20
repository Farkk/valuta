<?php

declare(strict_types=1);

namespace Theme\Cities;

final class CityApiClient
{
    private const BASE_URL = 'https://data.kkb-tech.ru';
    private const TOKEN = 'kFYqSwq9RDjFtAVbq5iPb0kyO3OTWmmKG1x2W9aiBN8YZztRh3y4k3pITU6rI3td';
    private const SOURCE = 'Banki.ru';

    /**
     * @return array{success: bool, data: mixed, error: string}
     */
    public function getCities(): array
    {
        $url = add_query_arg(
            [
                'source' => self::SOURCE,
            ],
            self::BASE_URL . '/meta/cities'
        );

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Bearer ' . self::TOKEN,
                    'Accept'        => 'application/json',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'data'    => null,
                'error'   => $response->get_error_message(),
            ];
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status < 200 || $status >= 300 || ! is_array($data)) {
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'Invalid city API response.',
            ];
        }

        return [
            'success' => true,
            'data'    => $data,
            'error'   => '',
        ];
    }
}
