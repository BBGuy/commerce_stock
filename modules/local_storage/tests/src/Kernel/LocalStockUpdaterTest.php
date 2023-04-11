<?php

namespace Drupal\Tests\commerce_stock_local\Kernel;

use Drupal\commerce\Context;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Testing the local stock updater.
 *
 * @group commerce_stock
 */
class LocalStockUpdaterTest extends CommerceStockKernelTestBase {

  use LoggerChannelTrait;

  /**
   * A test variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation;

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $checker;

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceInterface
   */
  protected $stockService;

  /**
   * An array of location ids for variation1.
   *
   * @var int[]
   */
  protected $locations;

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'entity_reference_revisions',
    'path',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_store',
    'commerce_stock_local',
    'commerce_stock_field',
    'commerce_stock_local_test',
    'commerce_number_pattern',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_type');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product_variation_type');
    $this->installEntitySchema('commerce_stock_location_type');
    $this->installEntitySchema('commerce_stock_location');
    $this->installConfig(['commerce_product']);
    $this->installConfig(['commerce_stock']);
    $this->installConfig(['commerce_stock_field']);
    $this->installConfig(['commerce_stock_local']);
    $this->installConfig(['commerce_stock_local_test']);
    $this->installSchema('commerce_stock_local', [
      'commerce_stock_transaction_type',
      'commerce_stock_transaction',
      'commerce_stock_location_level',
    ]);

    $defaultStockLocation = StockLocation::create([
      'name' => 'Test',
      'status' => 1,
      'type' => "default",
    ]);
    $defaultStockLocation->save();

    $user = $this->createUser();
    $user = $this->reloadEntity($user);
    $this->user = $user;

    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.service_manager');
    $config->set('default_service_id', 'local_stock');
    $config->save();
    $stockServiceManager = \Drupal::service('commerce_stock.service_manager');

    // Turn off title generation to allow explicit values to be used.
    $variation_type = ProductVariationType::load('default');
    $variation_type->setGenerateTitle(FALSE);
    $variation_type->save();

    $this->variation = ProductVariation::create([
      'type' => 'default',
      'sku' => 'TEST_' . strtolower($this->randomMachineName()),
      'title' => $this->randomString(),
      'status' => 1,
      'price' => [
        'number' => '11.11',
        'currency_code' => 'USD',
      ],
    ]);
    $this->variation->save();
    $this->variation = $this->reloadEntity($this->variation);

    $this->checker = $stockServiceManager->getService($this->variation)
      ->getStockChecker();
    $this->stockService = $stockServiceManager->getService($this->variation);
    $stockServiceConfiguration = $stockServiceManager->getService($this->variation)
      ->getConfiguration();

    $context = new Context($user, $this->store);
    $this->locations = $stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
  }

  /**
   * Whether transactions are created.
   */
  public function testTransactionCreation() {

    // Whether the transaction events work.
    $logger = $this->prophesize(LoggerInterface::class);
    $logger->log(Argument::is(7), Argument::containingString('LOCAL_STOCK_TRANSACTION_CREATE'), Argument::type('array'))
      ->shouldBeCalled();
    $logger->log(Argument::is(7), Argument::containingString('LOCAL_STOCK_TRANSACTION_INSERT'), Argument::type('array'))
      ->shouldBeCalled();
    $this->container->get('logger.factory')
      ->get('commerce_local_stock_test')
      ->addLogger($logger->reveal());

    $this->stockService->getStockUpdater()
      ->createTransaction($this->variation, $this->locations[1]->getId(), '', 10, 10.10, 'USD', StockTransactionsInterface::STOCK_IN, []);
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_IN);
    $result = $query->execute()->fetchAll();
    $this->assertEquals('1', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

}
