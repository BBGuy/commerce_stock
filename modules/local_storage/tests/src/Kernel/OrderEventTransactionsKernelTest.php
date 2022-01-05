<?php

namespace Drupal\Tests\commerce_stock_local\Kernel;

use Drupal\commerce\Context;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\commerce_store\StoreCreationTrait;
use Drupal\profile\Entity\Profile;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;

/**
 * Ensure the stock transactions are performed on order events.
 *
 * @coversDefaultClass \Drupal\commerce_stock\EventSubscriber\OrderEventSubscriber
 *
 * @group commerce_stock
 */
class OrderEventTransactionsKernelTest extends CommerceStockKernelTestBase {

  use StoreCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'path',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_stock',
    'commerce_stock_field',
    'commerce_stock_local',
    'commerce_stock_local_test',
    'commerce_number_pattern',
  ];

  /**
   * A test product.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $product;

  /**
   * A test variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation;

  /**
   * A second test variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation2;

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
   * The second stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $checker2;

  /**
   * The stock service configuration.
   *
   * @var \Drupal\commerce_stock\stockServiceConfiguration
   */
  protected $stockServiceConfiguration;

  /**
   * The second stock service configuration.
   *
   * @var \Drupal\commerce_stock\stockServiceConfiguration
   */
  protected $stockServiceConfiguration2;

  /**
   * An array of location ids for variation1.
   *
   * @var int[]
   */
  protected $locations;

  /**
   * An array of location ids for variation2.
   *
   * @var int[]
   */
  protected $locations2;

  /**
   * A sample user profile.
   *
   * @var \Drupal\profile\Entity\Profile
   */
  protected $profile;

  /**
   * A sample user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_stock_location');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installConfig([
      'commerce_product',
      'commerce_order',
      'commerce_stock',
      'commerce_stock_local',
      'commerce_stock_local_test',
      'profile',
      'commerce_number_pattern',
    ]);
    $this->installSchema(
      'commerce_stock_local',
      [
        'commerce_stock_transaction',
        'commerce_stock_transaction_type',
        'commerce_stock_location_level',
      ]
    );
    $this->installSchema('commerce_number_pattern', ['commerce_number_pattern_sequence']);

    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->getEditable('commerce_stock.service_manager');
    $config->set('default_service_id', 'local_stock');
    $config->set('stock_events_plugin_id', 'core_stock_events');
    $config->save();
    $this->stockServiceManager = \Drupal::service('commerce_stock.service_manager');

    $location = StockLocation::create([
      'type' => 'default',
      'name' => $this->randomString(),
      'status' => 1,
    ]);
    $location->save();

    $this->variation = ProductVariation::create([
      'type' => 'default',
      'sku' => 'TEST_' . strtolower($this->randomMachineName()),
      'status' => 1,
      'price' => [
        'number' => '12.00',
        'currency_code' => 'USD',
      ],
    ]);
    $this->variation->save();

    $this->variation2 = ProductVariation::create([
      'type' => 'default',
      'sku' => 'TEST_' . strtolower($this->randomMachineName()),
      'status' => 1,
      'price' => [
        'number' => '11.00',
        'currency_code' => 'USD',
      ],
    ]);
    $this->variation2->save();

    $this->product = Product::create([
      'type' => 'default',
      'title' => $this->randomMachineName(),
      'stores' => [$this->store],
      'variations' => [$this->variation, $this->variation2],
    ]);
    $this->product->save();

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);
    $this->user = $user;

    // Set some initial stock.
    $this->stockServiceManager->createTransaction($this->variation, 1, '', 100, 0, 'USD', StockTransactionsInterface::STOCK_IN);
    $this->stockServiceManager->createTransaction($this->variation2, 1, '', 200, 0, 'USD', StockTransactionsInterface::STOCK_IN);

    $this->checker = $this->stockServiceManager->getService($this->variation)
      ->getStockChecker();
    $this->checker2 = $this->stockServiceManager->getService($this->variation2)
      ->getStockChecker();
    $this->stockServiceConfiguration = $this->stockServiceManager->getService($this->variation)
      ->getConfiguration();
    $this->stockServiceConfiguration2 = $this->stockServiceManager->getService($this->variation2)
      ->getConfiguration();

    $context = new Context($user, $this->store);
    $this->locations = $this->stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
    $this->locations2 = $this->stockServiceConfiguration2->getAvailabilityLocations($context, $this->variation2);

    $profile = Profile::create([
      'type' => 'customer',
      'uid' => $user->id(),
    ]);
    $profile->save();
    $this->profile = $profile;
  }

  /**
   * Whether transitions and order deletion resulting in proper
   * stock transactions.
   *
   * @covers ::onOrderPlace
   * @covers ::onOrderCancel
   */
  public function testOrderEvents() {

    // Change the workflow of the default order type.
    $order_type = OrderType::load('default');
    $order_type->setWorkflowId('order_fulfillment_validation');
    $order_type->save();

    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $this->user->getEmail(),
      'uid' => $this->user->id(),
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'billing_profile' => $this->profile,
      'store_id' => $this->store->id(),
    ]);

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');
    // Add order item.
    $order_item1 = $order_item_storage->createFromPurchasableEntity($this->variation);
    $order_item1->setQuantity('44');
    $order_item1->save();
    $order->addItem($order_item1);
    $order->save();

    // We react on workflow transition event. Just adding something to an order
    // in 'draft' state shouldn't result in stock transactions.
    $this->assertEquals(100, $this->checker->getTotalStockLevel($this->variation, $this->locations));

    // Whether setting the order state to 'place' triggers stock transaction.
    $transition = $order->getState()->getTransitions();
    $order->getState()->applyTransition($transition['place']);
    $order->save();
    $this->assertEquals(56, $this->checker->getTotalStockLevel($this->variation, $this->locations));

    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn');
    $result = $query->execute()->fetchAll();
    $this->assertCount(3, $result);

    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('3', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals($order->id(), $result[0]->related_oid);
    $this->assertEquals($order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('-44.00', $result[0]->qty);
    $this->assertNotEmpty(unserialize($result[0]->data)['message']);

    // Whether setting the order state to 'cancel' returns the stock.
    $order->getState()->applyTransition($transition['cancel']);
    $order->save();
    $this->assertEquals(100, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('4', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals($order->id(), $result[0]->related_oid);
    $this->assertEquals($order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('44.00', $result[0]->qty);
    $this->assertNotEmpty(unserialize($result[0]->data)['message']);

  }

  /**
   * Whether order item modifications resulting in proper stock
   * transactions.
   *
   * @covers ::onOrderUpdate
   * @covers ::onOrderItemUpdate
   * @covers ::onOrderItemDelete
   * @covers ::onOrderDelete
   */
  public function testOrderItemEvents() {

    // Change the workflow of the default order type.
    $order_type = OrderType::load('default');
    $order_type->setWorkflowId('order_fulfillment_validation');
    $order_type->save();

    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $this->user->getEmail(),
      'uid' => $this->user->id(),
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'billing_profile' => $this->profile,
      'store_id' => $this->store->id(),
    ]);

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');
    // Add order item.
    $order_item1 = $order_item_storage->createFromPurchasableEntity($this->variation);
    $order_item1->setQuantity('44');
    $order_item1->save();
    $order->addItem($order_item1);
    $order->save();
    $order_item1 = $this->reloadEntity($order_item1);

    $transition = $order->getState()->getTransitions();
    $order->getState()->applyTransition($transition['place']);
    $order->save();

    $order_item2 = $order_item_storage->createFromPurchasableEntity($this->variation2);
    $order_item2->setQuantity('22');
    $order_item2->save();
    $order->addItem($order_item2);
    $order->save();
    $order_item2 = $this->reloadEntity($order_item2);

    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn');
    $result = $query->execute()->fetchAll();
    $this->assertCount(4, $result);

    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(2, $result);
    $this->assertEquals('4', $result[1]->id);
    $this->assertEquals($this->variation2->id(), $result[1]->entity_id);
    $this->assertEquals('-22.00', $result[1]->qty);
    $this->assertNotEmpty(unserialize($result[1]->data)['message']);
    $this->assertEquals(178, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));

    // Whether changing the qty triggers the stock transaction.
    $order_item2->setQuantity('33');
    $order_item2->save();
    $order->save();

    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(3, $result);
    $this->assertEquals('5', $result[2]->id);
    $this->assertEquals($this->variation2->id(), $result[2]->entity_id);
    $this->assertEquals('-11.00', $result[2]->qty);
    $this->assertNotEmpty(unserialize($result[2]->data)['message']);
    $this->assertEquals(167, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));

    // Whether changing the qty triggers the stock transaction.
    $order_item2->setQuantity('22');
    $order_item2->save();
    $order->save();

    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('6', $result[0]->id);
    $this->assertEquals($this->variation2->id(), $result[0]->entity_id);
    $this->assertEquals('11.00', $result[0]->qty);
    $this->assertNotEmpty(unserialize($result[0]->data)['message']);
    $this->assertEquals(178, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));

    // Whether removing one item from order results in the proper transaction.
    $order->removeItem($order_item1);
    $order_item1->delete();
    $order->save();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(2, $result);
    $this->assertEquals('7', $result[1]->id);
    $this->assertEquals($this->variation->id(), $result[1]->entity_id);
    $this->assertEquals('44.00', $result[1]->qty);
    $this->assertNotEmpty(unserialize($result[1]->data)['message']);
    $this->assertEquals(100, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->assertEquals(178, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));

    // Whether deleting the order triggers stock transactions.
    $order->delete();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(3, $result);
    $this->assertEquals(200, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));
    $this->assertNotEmpty(unserialize($result[2]->data)['message']);
  }

  /**
   * Whether the order cancel transition dont return stock if the order is
   * in 'draft' state. That would result in wrong stock levels.
   *
   * @covers ::onOrderCancel
   */
  public function testCancelTransitionDontFireInDraftState() {

    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $this->user->getEmail(),
      'uid' => $this->user->id(),
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'billing_profile' => $this->profile,
      'store_id' => $this->store->id(),
    ]);

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');
    // Add order item.
    $order_item1 = $order_item_storage->createFromPurchasableEntity($this->variation);
    $order_item1->setQuantity('44');
    $order_item1->save();
    $order->addItem($order_item1);
    $order->save();

    $transition = $order->getState()->getTransitions();
    $order->getState()->applyTransition($transition['cancel']);
    $order->save();
    $this->assertEquals(100, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);
  }

}
