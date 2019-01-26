<?php

namespace Drupal\Tests\commerce_stock_enforcement\Kernel;

use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTest;

/**
 * Defines base class for commerce_stock_enforcement test cases.
 */
abstract class EnforcementKernelTestBase extends CommerceStockKernelTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_cart',
    'commerce_stock_enforcement',
    'commerce_stock_field',
    'commerce_stock_local',
  ];

}
