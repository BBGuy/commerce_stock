<?php

namespace Drupal\Tests\commerce_stock_field\Functional;

use Drupal\commerce\Context;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\Tests\commerce_stock\Functional\StockBrowserTestBase;
use Drupal\Tests\commerce_stock_field\Kernel\StockLevelFieldCreationTrait;

/**
 * Provides a base class for stock level fields functional tests.
 */
abstract class StockLevelFieldTestBase extends StockBrowserTestBase {

  use StockLevelFieldCreationTrait;

  /**
   * The test product variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_stock_field',
    'commerce_stock_local',
    'commerce_stock_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([], parent::getAdministratorPermissions());
  }

  /**
   * Setting up the test.
   */
  protected function setup() {
    parent::setUp();

    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $this->fieldName = 'stock_level_test';

    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.service_manager');
    $config->set('default_service_id', 'local_stock');
    $config->save();

    $widget_settings = [
      'step' => 1,
      'transaction_note' => FALSE,
    ];
    $this->createStockLevelField($entity_type, $bundle, 'commerce_stock_level_simple_transaction', [], [], $widget_settings);

    // Varations needs a fresh load to load the new fields.
    $this->variation = $entityTypeManager = \Drupal::entityTypeManager()->getStorage($entity_type)->load($this->variations[2]->id());
    self::assertTrue($this->variation->hasField($this->fieldName));
    $stockServiceConfiguration = $this->stockServiceManager->getService($this->variation)
      ->getConfiguration();
    $store = array_shift($this->stores);
    $context = new Context($this->adminUser, $store);
    $this->locations = $stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
    $this->stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', 10, 10.10, 'USD', StockTransactionsInterface::STOCK_IN, []);
    self::assertTrue($this->stockServiceManager->getStockLevel($this->variation) == 10);
  }

}
