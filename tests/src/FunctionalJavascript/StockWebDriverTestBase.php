<?php

namespace Drupal\Tests\commerce_stock\FunctionalJavascript;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\commerce_store\StoreCreationTrait;
use Drupal\FunctionalJavascriptTests\JSWebAssert;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\commerce\Traits\CommerceBrowserTestTrait;

/**
 * Defines base class for commerce stock test cases.
 */
abstract class StockWebDriverTestBase extends WebDriverTestBase {

  use StoreCreationTrait;
  use CommerceBrowserTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'field',
    'block',
    'commerce',
    'commerce_price',
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
   * A test user with administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

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
  protected function setUp() {
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
    ]);
    $product->save();
    $this->product = $product;
  }

  /**
   * Waits for the given time or until the given JS condition becomes TRUE.
   *
   * @param string $condition
   *   JS condition to wait until it becomes TRUE.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 1000.
   * @param string $message
   *   (optional) A message to display with the assertion. If left blank, a
   *   default message will be displayed.
   *
   * @see \Behat\Mink\Driver\DriverInterface::evaluateScript()
   */
  protected function assertJsCondition($condition, $timeout = 1000, $message = '') {
    $message = $message ?: "Javascript condition met:\n" . $condition;
    $result = $this->getSession()->getDriver()->wait($timeout, $condition);
    $this->assertNotEmpty($result, $message);
  }

  /**
   * Waits for jQuery to become active and animations to complete.
   */
  protected function waitForAjaxToFinish() {
    $condition = "(0 === jQuery.active && 0 === jQuery(':animated').length)";
    $this->assertJsCondition($condition, 10000);
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\FunctionalJavascriptTests\JSWebAssert
   *   A new web-assert option for asserting the presence of elements with.
   */
  public function assertSession($name = NULL) {
    return new JSWebAssert($this->getSession($name), $this->baseUrl);
  }

}
