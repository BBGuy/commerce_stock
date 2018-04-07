<?php

namespace Drupal\Tests\commerce_stock_field\Kernel;

use Drupal\commerce\Context;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;

/**
 * Ensure the stock level field works.
 *
 * @coversDefaultClass \Drupal\commerce_stock_field\Plugin\Field\FieldType\StockLevel
 *
 * @group commerce_stock
 */
class StockLevelTest extends CommerceStockKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'path',
    'commerce_product',
    'commerce_stock',
    'commerce_stock_field',
    'commerce_stock_local',
  ];

  /**
   * A test variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation;

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $checker;

  /**
   * The stock service configuration.
   *
   * @var \Drupal\commerce_stock\stockServiceConfiguration
   */
  protected $stockServiceConfiguration;

  /**
   * An array of location ids for variation1.
   *
   * @var int[]
   */
  protected $locations;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_stock_location');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installConfig([
      'commerce_product',
      'commerce_stock',
      'commerce_stock_local',
    ]);
    $this->installSchema(
      'commerce_stock_local',
      [
        'commerce_stock_transaction',
        'commerce_stock_transaction_type',
        'commerce_stock_location_level',
      ]);

    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->getEditable('commerce_stock.service_manager');
    $config->set('default_service_id', 'local_stock');
    $config->save();
    $this->stockServiceManager = \Drupal::service('commerce_stock.service_manager');

    $location = StockLocation::create([
      'type' => 'default',
      'name' => $this->randomString(),
      'status' => 1,
    ]);
    $location->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_stock_level',
      'entity_type' => 'commerce_product_variation',
      'type' => 'commerce_stock_level',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'test_stock_level',
      'entity_type' => 'commerce_product_variation',
      'bundle' => 'default',
    ]);
    $field->save();

    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => 'TEST_' . strtolower($this->randomMachineName()),
      'status' => 1,
      'price' => [
        'number' => '12.00',
        'currency_code' => 'USD',
      ],
    ]);
    $variation->save();
    $this->variation = $variation;

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    $this->stockServiceManager->createTransaction($variation, 1, '', 55, 0, StockTransactionsInterface::STOCK_IN);

    $this->checker = $this->stockServiceManager->getService($this->variation)
      ->getStockChecker();
    $this->stockServiceConfiguration = $this->stockServiceManager->getService($this->variation)
      ->getConfiguration();
    $context = new Context($user, $this->store);

    $this->locations = $this->stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
  }

  /**
   * Whether setting a plain value results in increased stock level.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStockLevelStockIn() {
    $this->variation->set('test_stock_level', 10);
    $this->variation->save();
    $this->assertEquals(65, $this->checker->getTotalStockLevel($this->variation, $this->locations));

  }

  /**
   * Whether setting a plain negative results in reduced stock level.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStockLevelStockOut() {
    $this->variation->set('test_stock_level', -10);
    $this->variation->save();
    $this->assertEquals(45, $this->checker->getTotalStockLevel($this->variation, $this->locations));

  }

  /**
   * Whether setting via simple entry system works and sets the absolute stock
   * level.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStockLevelSimpleEntry() {
    $stubSimpleEntry = [
      'stock' => [
        'value' => 22,
        'entry_system' => 'simple',
      ],
    ];
    $this->variation->set('test_stock_level', $stubSimpleEntry);
    $this->variation->save();
    $this->assertEquals(22, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

  /**
   * Whether setting via basic entry system works. Positiv value should increase
   * stock level.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStockLevelBasicEntryStockIn() {
    $stubBasicEntry = [
      'stock' => [
        'adjustment' => 33,
        'entry_system' => 'basic',
      ],
    ];
    $this->variation->set('test_stock_level', $stubBasicEntry);
    $this->variation->save();
    $this->assertEquals(88, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

  /**
   * Whether negative values via basic entry system works. Negative values
   * should reduce the stock level.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStockLevelBasicEntryStockOut() {
    $stubBasicEntry = [
      'stock' => [
        'adjustment' => -33,
        'entry_system' => 'basic',
      ],
    ];
    $this->variation->set('test_stock_level', $stubBasicEntry);
    $this->variation->save();
    $this->assertEquals(22, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

}
