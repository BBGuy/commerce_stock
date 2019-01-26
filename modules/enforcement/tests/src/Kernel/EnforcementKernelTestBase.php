<?php

namespace Drupal\Tests\commerce_stock_enforcement\Kernel;

use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;

/**
 * Defines base class for commerce_stock_enforcement test cases.
 */
abstract class EnforcementKernelTestBase extends CommerceStockKernelTestBase {

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
    'commerce_order',
    'entity_reference_revisions',
    'profile',
    'state_machine',
    'path',
    'commerce_product',
  ];

}
