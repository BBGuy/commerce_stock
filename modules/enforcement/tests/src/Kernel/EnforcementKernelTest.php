<?php

namespace Drupal\Tests\commerce_stock_enforcement\Functional;

use Drupal\commerce\Context;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests out of stock functionality.
 *
 * @group commerce_stock
 */
class EnforcementKernelTest extends EnforcementBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.service_manager');
    $config->set('default_service_id', 'local_stock');
    $config->save();

    $this->createProduct();
  }

  /**
   * Test the get stock level function.
   */
  public function testGetStockLevel() {
    $context = commerce_stock_enforcement_get_context($this->variation);

    $this->stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', 5, 4.20, 'USD', StockTransactionsInterface::STOCK_IN, []);
    $stock_level = commerce_stock_enforcement_get_stock_level($this->variation, $context);
    $this->assertEquals(15, $stock_level);

    $this->stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', -12, 4.20, 'USD', StockTransactionsInterface::STOCK_OUT, []);
    $stock_level = commerce_stock_enforcement_get_stock_level($this->variation, $context);
    $this->assertEquals(3, $stock_level);

    $this->stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', -5, 4.20, 'USD', StockTransactionsInterface::STOCK_OUT, []);
    $stock_level = commerce_stock_enforcement_get_stock_level($this->variation, $context);
    $this->assertEquals(-2, $stock_level);
  }

  /**
   * Create a product with stock for testing.
   */
  protected function createProduct() {
    $entity_type = 'commerce_product_variation';
    $bundle = 'default';
    $entity_manager = \Drupal::entityTypeManager();
    $entity_manager->clearCachedDefinitions();
    $field_name = 'field_stock_level_test';

    /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage */
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'type' => 'commerce_stock_level',
      'entity_type' => $entity_type,
    ])->save();

    FieldConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'bundle' => $bundle,
      'label' => 'StockLevel',
    ])->save();

    $this->product = $this->createEntity('commerce_product', [
      'type' => 'default',
    ]);

    $this->variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'product_id' => $this->product->id(),
      'sku' => strtolower($this->randomMachineName()),
      'status' => 1,
      'price' => [
        'number' => '4.20',
        'currency_code' => 'USD',
      ],
    ]);

    $stockServiceConfiguration = $this->stockServiceManager->getService($this->variation)
      ->getConfiguration();

    $context = new Context($this->adminUser, $this->store);
    $this->locations = $stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
    // Set initial Stock level.
    $this->stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', 10, 4.20, 'USD', StockTransactionsInterface::STOCK_IN, []);
  }

}
