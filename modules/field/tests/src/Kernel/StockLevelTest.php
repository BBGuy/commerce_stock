<?php

namespace Drupal\Tests\commerce_stock_field\Kernel;

use Drupal\commerce\Context;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\commerce_stock_field\Plugin\Field\FieldType\StockLevel;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;
use Drupal\Tests\commerce_stock_local\Kernel\StockTransactionQueryTrait;

/**
 * Ensure the stock level field works.
 *
 * @coversDefaultClass \Drupal\commerce_stock_field\Plugin\Field\FieldType\StockLevel
 *
 * @group commerce_stock
 */
class StockLevelTest extends CommerceStockKernelTestBase {

  use StockLevelFieldCreationTrait;
  use StockTransactionQueryTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'path',
    'commerce_product',
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
    $this->fieldName = 'test_stock_level';
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

    $location = StockLocation::create([
      'type' => 'default',
      'name' => $this->randomString(),
      'status' => 1,
    ]);
    $location->save();

    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->getEditable('commerce_stock.service_manager');
    $config->set('default_service_id', 'local_stock');
    $config->save();
    $this->stockServiceManager = \Drupal::service('commerce_stock.service_manager');

    $entity_type = 'commerce_product_variation';
    $bundle = 'default';
    $widget_id = 'commerce_stock_level_simple';
    $this->createStockLevelField($entity_type, $bundle, $widget_id);

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
    Product::create([
      'type' => 'default',
      'variations' => [$variation],
      'stores' => [$this->store],
    ])->save();
    $this->variation = $this->reloadEntity($variation);

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    $this->stockServiceManager->createTransaction($this->variation, 1, '', 55, 0, 'EUR', StockTransactionsInterface::STOCK_IN);

    $this->checker = $this->stockServiceManager->getService($this->variation)
      ->getStockChecker();
    $this->stockServiceConfiguration = $this->stockServiceManager->getService($this->variation)
      ->getConfiguration();
    $context = new Context($user, $this->store);

    $this->locations = $this->stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
  }

  /**
   * Test always in stock field is added to purchasable entities.
   *
   * Test that a commerce_stock_always_in_stock base field
   * is added to purchasable entities.
   */
  public function testBaseFieldisAddedtoPurchasableEntity() {

    $variation = ProductVariation::create([
      'type' => 'default',
    ]);
    $variation->save();

    // This would throw an Exception, if the field isn't there.
    $field = $variation->get('commerce_stock_always_in_stock');
    // Check the default value is set to FALSE.
    self::assertFalse($field->getValue()[0]['value']);
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
   * Whether a wrong value is throwing.
   */
  public function testInvalidArgumentThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->variation->set('test_stock_level', 'FAIL');
  }

  /**
   * Whether all data are correctly saved with the transaction.
   */
  public function testTransactionData() {

    $test_note = $this->randomString();
    $zone = 'TestZone';

    $mock_widget_values = [
      'adjustment' => '3.33',
      'stock_transaction_note' => $test_note,
      'user_id' => 7,
      'unit_cost' => [
        'amount' => 33,
        'currency_code' => 'USD',
      ],
      'zone' => $zone,
    ];

    $this->variation->set('test_stock_level', $mock_widget_values);
    $this->variation->save();
    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    $this->assertEquals($mock_widget_values['zone'], $transaction->location_zone);
    $this->assertEquals($mock_widget_values['adjustment'], $transaction->qty);
    $this->assertEquals($mock_widget_values['user_id'], $transaction->related_uid);
    $this->assertEquals($mock_widget_values['unit_cost']['amount'], $transaction->unit_cost);
    $this->assertEquals($mock_widget_values['unit_cost']['currency_code'], $transaction->currency_code);
    $this->assertEquals($mock_widget_values['stock_transaction_note'], $data['message']);
  }

  /**
   * @covers ::generateSampleValue().
   */
  public function testSampeValueGenerator() {
    $i = 0;
    $FieldDefinition = $this->createMock(FieldDefinitionInterface::class);
    while($i < 100) {
      $sampleValue = StockLevel::generateSampleValue($FieldDefinition);
      $value = $sampleValue['value'];
      $this->assertTrue(is_float($value));
      $this->assertTrue(is_float($value));
      $this->assertTrue(999 <= $value && -999 >= $value);
      $i++;
    }
  }

}
