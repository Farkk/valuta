<?php

declare(strict_types=1);

namespace Theme\Banks;

use Theme\Core\Contracts\ServiceProvider;
use Theme\Currencies\CurrencyRouter;

final class BankRouter implements ServiceProvider
{
  private const REWRITE_OPTION = 'theme_bank_rewrite_version_v2';

  public function register(): void
  {
    add_action('init', [$this, 'registerRewriteRules']);
    add_action('init', [$this, 'maybeFlushRewriteRules'], 20);
    add_filter('query_vars', [$this, 'registerQueryVars']);
    add_action('template_redirect', [$this, 'handleBankPage']);
    add_action('after_switch_theme', [$this, 'flushRewriteRules']);
    add_filter('body_class', [$this, 'filterBodyClass']);
  }

  public function registerRewriteRules(): void
  {
    add_rewrite_rule(
      '^bank/([^/]+)/(\d+)([a-z]{3})_([a-z]{3})/?$',
      'index.php?bank_code=$matches[1]&currency_pair=$matches[2]$matches[3]_$matches[4]',
      'top'
    );
    add_rewrite_rule(
      '^bank/([^/]+)/?$',
      'index.php?bank_code=$matches[1]',
      'top'
    );
  }

  /**
   * @param list<string> $vars
   * @return list<string>
   */
  public function registerQueryVars(array $vars): array
  {
    $vars[] = 'bank_code';
    $vars[] = 'bank_tab';

    return $vars;
  }

  public function handleBankPage(): void
  {
    $bankCode = BankManager::normalizeCode((string) get_query_var('bank_code', ''));

    if ($bankCode === '') {
      return;
    }

    if (CurrencyRouter::isCurrencyPairPage() && CurrencyRouter::getCurrencyPairData() === null) {
      global $wp_query;

      $wp_query->set_404();
      status_header(404);
      nocache_headers();

      return;
    }

    $tab = BankManager::normalizeTab((string) ($_GET['tab'] ?? ''));
    if ($tab !== '') {
      set_query_var('bank_tab', $tab);
    }

    $context = BankManager::buildPageContext($bankCode);

    if ($context === null) {
      global $wp_query;

      $wp_query->set_404();
      status_header(404);
      nocache_headers();

      return;
    }

    BankManager::setCurrentBank($context);

    global $wp_query;

    if ($wp_query instanceof \WP_Query) {
      $wp_query->is_404 = false;
    }

    status_header(200);
    include THEME_PATH . '/bank-page.php';
    exit;
  }

  /**
   * @param list<string> $classes
   * @return list<string>
   */
  public function filterBodyClass(array $classes): array
  {
    if (BankManager::hasCurrentBank()) {
      $classes[] = 'bank-single-page';
    }

    return $classes;
  }

  public function maybeFlushRewriteRules(): void
  {
    if (get_option(self::REWRITE_OPTION) === THEME_VERSION) {
      return;
    }

    $this->flushRewriteRules();
    update_option(self::REWRITE_OPTION, THEME_VERSION, false);
  }

  public function flushRewriteRules(): void
  {
    $this->registerRewriteRules();
    flush_rewrite_rules(false);
  }
}
