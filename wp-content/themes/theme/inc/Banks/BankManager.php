<?php

declare(strict_types=1);

namespace Theme\Banks;

use Theme\Cities\CityManager;
use Theme\Currencies\CurrencyRouter;
use Theme\Filters\FilterRegistry;
use Theme\Rates\RatesManager;
use Theme\SEO\SeoPageManager;

final class BankManager
{
  private static ?array $currentBank = null;

  public static function setCurrentBank(array $bank): void
  {
    self::$currentBank = $bank;
  }

  public static function hasCurrentBank(): bool
  {
    return self::$currentBank !== null;
  }

  /**
   * @return array<string, mixed>
   */
  public static function getCurrentBank(): array
  {
    return self::$currentBank ?? [];
  }

  public static function normalizeTab(string $tab): string
  {
    $tab = strtolower(trim($tab));

    return in_array($tab, ['all', 'buy', 'sell'], true) ? $tab : '';
  }

  /**
   * @return array{amount: float, currency: string, tab: string}
   */
  public static function getAmountContext(): array
  {
    $pairData = CurrencyRouter::getCurrencyPairData();
    $currency = CurrencyRouter::getCurrentCurrency();
    $amount = 0.0;

    if ($pairData !== null) {
      $amount = (float) ($pairData['amount'] ?? 0);
      $currency = (string) ($pairData['from'] ?? $currency);
    } elseif (SeoPageManager::hasCurrentPage() && SeoPageManager::getCurrentAmount() > 0) {
      $amount = (float) SeoPageManager::getCurrentAmount();
      $currency = SeoPageManager::getCurrentCurrencyCode();
    }

    return [
      'amount' => $amount,
      'currency' => self::normalizeCode($currency),
      'tab' => self::resolveTab(),
    ];
  }

  public static function resolveTab(): string
  {
    $tab = self::normalizeTab((string) get_query_var('bank_tab', ''));

    if ($tab === '') {
      $tab = self::normalizeTab((string) ($_GET['tab'] ?? ''));
    }

    if ($tab === '') {
      return FilterRegistry::getDefaultTab();
    }

    return $tab;
  }

  public static function getPageUrl(
    string $bankCode,
    ?float $amount = null,
    ?string $currency = null,
    ?string $tab = null
  ): string {
    $bankCode = self::normalizeCode($bankCode);

    if ($bankCode === '') {
      return home_url('/');
    }

    $context = self::getAmountContext();
    $amount = $amount ?? $context['amount'];
    $currency = strtolower(trim($currency ?? $context['currency']));
    $tab = $tab ?? $context['tab'];

    if ($currency === 'rub') {
      $currency = '';
    }

    $path = 'bank/' . rawurlencode($bankCode) . '/';
    $amountInt = (int) round($amount);

    if ($amountInt > 0 && $currency !== '') {
      $path .= $amountInt . $currency . '_rub/';
    }

    $url = home_url('/' . $path);
    $defaultTab = FilterRegistry::getDefaultTab();

    if ($tab !== '' && $tab !== $defaultTab) {
      $url = add_query_arg('tab', $tab, $url);
    }

    return $url;
  }

  public static function normalizeCode(string $bankCode): string
  {
    return strtolower(trim($bankCode));
  }

  /**
   * @return array<string, mixed>|null
   */
  public static function findInRates(array $rates, string $bankCode): ?array
  {
    $bankCode = self::normalizeCode($bankCode);

    foreach (RatesManager::getRawBanks($rates) as $bank) {
      if (! is_array($bank)) {
        continue;
      }

      if (self::normalizeCode((string) ($bank['bank_code'] ?? '')) === $bankCode) {
        return $bank;
      }
    }

    return null;
  }

  /**
   * @return array{
   *   bank_code: string,
   *   bank_name: string,
   *   bank_logo: string,
   *   bank_url: string,
   *   bank_description: string,
   *   offices: list<array<string, mixed>>,
   *   update_time: string,
   *   tab: string,
   *   currency: string,
   *   amount: float,
   *   show_disclaimer: bool
   * }
   */
  public static function buildPageContext(string $bankCode): ?array
  {
    $bankCode = self::normalizeCode($bankCode);
    $city = CityManager::getCurrentCity();
    $amountContext = self::getAmountContext();
    $currency = $amountContext['currency'] !== ''
      ? $amountContext['currency']
      : CurrencyRouter::getCurrentCurrency();
    $tab = $amountContext['tab'];
    $amount = $amountContext['amount'];
    $filters = array_values(array_filter(array_map(
      static fn(array $filter): string => ! empty($filter['checked_by_default']) ? (string) $filter['slug'] : '',
      FilterRegistry::getFilters()
    )));
    $rates = RatesManager::getRates((string) ($city['city_name'] ?? ''), $currency);
    $bank = self::findInRates($rates, $bankCode);

    if ($bank === null) {
      return null;
    }

    $offices = BankOffices::sort(
      BankOffices::collect(
        RatesManager::getRawBanks($rates),
        $bankCode,
        $filters,
        false,
        (string) ($city['city_name'] ?? '')
      ),
      $tab,
      $bankCode
    );

    $registryName = BankRegistry::getBankName($bankCode);
    $bankName = $registryName !== '' ? $registryName : (string) ($bank['bank_name'] ?? '');

    return [
      'bank_code' => $bankCode,
      'bank_name' => $bankName,
      'bank_logo' => BankRegistry::getBankLogo($bankCode, (string) ($bank['bank_logo'] ?? '')),
      'bank_url' => BankRegistry::getBankUrl($bankCode),
      'bank_description' => BankRegistry::getBankDescription($bankCode),
      'offices' => $offices,
      'update_time' => RatesManager::formatUpdateTime($rates),
      'tab' => $tab,
      'currency' => $currency,
      'amount' => $amount,
      'show_disclaimer' => $tab === 'sell' || $tab === 'buy',
    ];
  }
}
