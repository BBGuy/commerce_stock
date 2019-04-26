<?php

namespace Drupal\Tests\commerce_stock\Functional;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Defines base class for commerce stock test cases.
 */
abstract class StockBrowserTestBase extends CommerceBrowserTestBase {

  use EntityReferenceTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_store',
    'commerce_product',
    'commerce_order',
    'commerce_stock',
    'field_ui',
    'options',
    'taxonomy',
  ];

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManager
   */
  protected $stockServiceManager;

  /**
   * The product to test against.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $product;

  /**
   * Array of product variations.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface[]
   */
  protected $variations;

  /**
   * The stores to test against.
   *
   * @var \Drupal\commerce_store\Entity\StoreInterface[]
   */
  protected $stores;

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_order',
      'administer commerce_product',
      'administer commerce_product_type',
      'administer commerce_product fields',
      'administer commerce_product_variation fields',
      'administer commerce_product_variation display',
      'access commerce_product overview',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser($this->getAdministratorPermissions());

    $this->stockServiceManager = $this->container->get('commerce_stock.service_manager');

    $this->stores = [];
    for ($i = 0; $i < 3; $i++) {
      $this->stores[] = $this->createStore();
    }

    $variations = [];
    for ($i = 1; $i <= 3; $i++) {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => strtolower($this->randomMachineName()),
        'title' => $this->randomString(),
        'status' => $i % 2,
      ]);
      $variation->save();
      $variations[] = $variation;
    }
    $this->variations = array_reverse($variations);
    $product = Product::create([
      'type' => 'default',
      'variations' => $variations,
      'stores' => $this->stores,
    ]);
    $product->save();
    $this->product = $product;
  }

}
