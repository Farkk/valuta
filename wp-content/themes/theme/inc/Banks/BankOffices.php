<?php

declare(strict_types=1);

namespace Theme\Banks;

final class BankOffices
{
  private const KKB_BANK_CODE = 'kamkombank';

  /**
   * @param list<array<string, mixed>> $rawBanks
   * @param list<string> $filters
   * @return list<array<string, mixed>>
   */
  public static function collect(
    array $rawBanks,
    string $bankCode,
    array $filters,
    bool $requireCoordinates,
    string $cityName
  ): array {
    $offices = [];
    $seenCoordinates = [];

    foreach ($rawBanks as $bank) {
      if (! is_array($bank) || BankManager::normalizeCode((string) ($bank['bank_code'] ?? '')) !== $bankCode) {
        continue;
      }

      foreach (($bank['offices'] ?? []) as $office) {
        if (! is_array($office)) {
          continue;
        }

        $currencyData = $office['currencies'][0] ?? null;
        if (! is_array($currencyData)) {
          continue;
        }

        $buy = (float) ($currencyData['buy'] ?? 0);
        $sell = (float) ($currencyData['sell'] ?? 0);
        if ($buy <= 0 || $sell <= 0 || ! self::passesFilters($currencyData, $filters)) {
          continue;
        }

        $latitude = ! empty($office['latitude']) ? (float) $office['latitude'] : null;
        $longitude = ! empty($office['longitude']) ? (float) $office['longitude'] : null;

        if ($requireCoordinates && ($latitude === null || $longitude === null)) {
          continue;
        }

        if ($latitude !== null && $longitude !== null) {
          $coordinateKey = $latitude . '_' . $longitude;
          if (isset($seenCoordinates[$coordinateKey])) {
            continue;
          }
          $seenCoordinates[$coordinateKey] = true;
        }

        $address = trim((string) ($office['address'] ?? ''));
        if ($address === '') {
          continue;
        }

        $metroStation = trim((string) ($office['metro_station'] ?? ''));
        if ($metroStation !== '' && in_array($cityName, ['Москва', 'Санкт-Петербург'], true)) {
          $address = 'м. ' . $metroStation . ', ' . $address;
        }

        $offices[] = [
          'office_id' => $office['office_id'] ?? null,
          'address' => $address,
          'latitude' => $latitude,
          'longitude' => $longitude,
          'buy' => $buy,
          'sell' => $sell,
          'is_open' => (bool) ($currencyData['is_open'] ?? true),
          'work_status' => (string) ($office['work_status'] ?? ''),
          'phone' => (string) ($office['phone'] ?? ''),
        ];
      }

      break;
    }

    return $offices;
  }

  /**
   * @param list<array<string, mixed>> $offices
   * @return list<array<string, mixed>>
   */
  public static function sort(array $offices, string $tab, string $bankCode): array
  {
    if ($tab === 'sell') {
      usort($offices, static fn(array $a, array $b): int => ($b['buy'] ?? 0) <=> ($a['buy'] ?? 0));
    } elseif ($tab === 'buy') {
      usort($offices, static fn(array $a, array $b): int => ($a['sell'] ?? 0) <=> ($b['sell'] ?? 0));
    } else {
      usort(
        $offices,
        static fn(array $a, array $b): int => ((($a['buy'] ?? 0) + ($a['sell'] ?? 0)) / 2) <=> ((($b['buy'] ?? 0) + ($b['sell'] ?? 0)) / 2)
      );
    }

    if ($bankCode === self::KKB_BANK_CODE) {
      usort($offices, static function (array $a, array $b): int {
        $aOpen = ($a['is_open'] ?? true) !== false;
        $bOpen = ($b['is_open'] ?? true) !== false;

        return $aOpen === $bOpen ? 0 : ($aOpen ? -1 : 1);
      });
    }

    return $offices;
  }

  /**
   * @return list<array{label: string, value: string, primary: bool}>
   */
  public static function formatRates(array $office, string $tab, float $amount): array
  {
    $buy = (float) ($office['buy'] ?? 0);
    $sell = (float) ($office['sell'] ?? 0);
    $hasAmount = $amount > 0;

    if ($tab === 'sell') {
      return [
        ['label' => __('Курс банка', 'theme'), 'value' => self::formatRate($buy) . ' ₽', 'primary' => false],
        [
          'label' => __('Вы получите', 'theme'),
          'value' => ($hasAmount ? self::formatAmount($buy * $amount) : '0') . ' ₽',
          'primary' => true,
        ],
      ];
    }

    if ($tab === 'buy') {
      return [
        ['label' => __('Курс банка', 'theme'), 'value' => self::formatRate($sell) . ' ₽', 'primary' => false],
        [
          'label' => __('Расчёт суммы', 'theme'),
          'value' => ($hasAmount ? self::formatAmount($sell * $amount) : '0') . ' ₽',
          'primary' => true,
        ],
      ];
    }

    return [
      ['label' => __('Покупка', 'theme'), 'value' => self::formatRate($buy) . ' ₽', 'primary' => false],
      ['label' => __('Продажа', 'theme'), 'value' => self::formatRate($sell) . ' ₽', 'primary' => true],
    ];
  }

  public static function getMapUrl(array $office): string
  {
    $latitude = $office['latitude'] ?? null;
    $longitude = $office['longitude'] ?? null;

    if ($latitude === null || $longitude === null) {
      return '';
    }

    return sprintf(
      'https://yandex.ru/maps/?pt=%s,%s&z=17&l=map',
      rawurlencode((string) $longitude),
      rawurlencode((string) $latitude)
    );
  }

  public static function formatRate(float $value): string
  {
    return number_format($value, 2, ',', '');
  }

  public static function formatAmount(float $value): string
  {
    $formatted = number_format($value, 2, ',', ' ');

    return rtrim(rtrim($formatted, '0'), ',');
  }

  /**
   * @param array<string, mixed> $currencyData
   * @param list<string> $filters
   */
  private static function passesFilters(array $currencyData, array $filters): bool
  {
    foreach ($filters as $filterSlug) {
      if (array_key_exists($filterSlug, $currencyData) && empty($currencyData[$filterSlug])) {
        return false;
      }
    }

    return true;
  }
}
