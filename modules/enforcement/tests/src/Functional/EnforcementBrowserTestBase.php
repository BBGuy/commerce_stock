<?php

namespace Drupal\Tests\commerce_stock_enforcement\Functional;

use Drupal\Tests\commerce_stock\Functional\StockBrowserTestBase;

/**
 * Defines base class for commerce_stock_enforcement test cases.
 */
abstract class EnforcementBrowserTestBase extends StockBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'commerce_cart',
    'commerce_stock_enforcement',
    'commerce_stock_field',
    'commerce_stock_local',
  ];

}
