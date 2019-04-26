<?php

namespace Drupal\Tests\commerce_stock\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Base class for Commerce Stock kernel tests.
 */
abstract class CommerceStockKernelTestBase extends CommerceKernelTestBase {

  /**
   * Modules to enable.
   *
   * Note that when a child class declares its own $modules list, that list
   * doesn't override this one, it just extends it.
   *
   * @var array
   */
  public static $modules = [
    'commerce_stock',
  ];

}
