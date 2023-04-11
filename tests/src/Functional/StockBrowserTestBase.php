<?php

namespace Drupal\Tests\commerce_stock\Functional;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\commerce_store\StoreCreationTrait;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Tests\commerce\Traits\CommerceBrowserTestTrait;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;

/**
 * Defines base class for commerce stock test cases.
 */
abstract class StockBrowserTestBase extends CommerceBrowserTestBase {

  /**
   * The testing profile.
   *
   * @var string
   */
  protected $profile = 'testing';

  use EntityReferenceTestTrait;
  use StoreCreationTrait;
  use CommerceBrowserTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
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
      'administer commerce_product',
      'administer commerce_product_type',
      'administer commerce_product fields',
      'administer commerce_product_variation fields',
      'administer commerce_product_variation display',
      'access commerce_product overview',
      'view the administration theme',
      'access administration pages',
      'access commerce administration pages',
      'administer commerce_currency',
      'administer commerce_store',
      'administer commerce_store_type',
      'administer commerce_order',
      'administer commerce_stock_location',
      'administer commerce_stock_location_type',
    ], []);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->stockServiceManager = $this->container->get('commerce_stock.service_manager');

    $this->store = $this->createStore();
    $this->placeBlock('local_tasks_block');
    $this->placeBlock('local_actions_block');
    $this->placeBlock('page_title_block');

    $this->adminUser = $this->drupalCreateUser($this->getAdministratorPermissions());
    $this->drupalLogin($this->adminUser);

    $location = StockLocation::create([
      'type' => 'default',
      'name' => 'TESTLOCATION',
    ]);
    $location->save();

    $this->stores = [];
    for ($i = 0; $i < 3; $i++) {
      $this->stores[] = $this->createStore();
    }

    // Turn off title generation to allow explicit values to be used.
    $variation_type = ProductVariationType::load('default');
    $variation_type->setGenerateTitle(FALSE);
    $variation_type->save();

    $variations = [];
    for ($i = 1; $i <= 3; $i++) {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => strtolower($this->randomMachineName()),
        'status' => $i % 2,
        'title' => $this->randomString(),
      ]);
      $variation->save();
      $variations[] = $this->reloadEntity($variation);
    }
    $this->variations = array_reverse($variations);
    $product = Product::create([
      'type' => 'default',
      'variations' => $variations,
      'stores' => $this->stores,
      'title' => $this->randomMachineName(),
    ]);
    $product->save();
    $this->product = $product;
  }

  /**
   * Waits for jQuery to become active and animations to complete.
   */
  protected function waitForAjaxToFinish() {
    $condition = "(0 === jQuery.active && 0 === jQuery(':animated').length)";
    $this->assertJsCondition($condition, 10000);
  }

}
