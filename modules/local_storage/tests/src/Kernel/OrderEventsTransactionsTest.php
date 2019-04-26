<?php

namespace Drupal\Tests\commerce_stock_local\Kernel;

use Drupal\commerce\Context;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\commerce_stock\EventSubscriber\OrderEventSubscriber;
use Drupal\commerce_stock\StockServiceManager;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\profile\Entity\Profile;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;

/**
 * Ensure the stock transactions are performed on order events.
 *
 * @group commerce_stock
 */
class OrderEventsTransactionsTest extends CommerceStockKernelTestBase {

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
   * A sample order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

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
  public static $modules = [
    'entity_reference_revisions',
    'path',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_store',
    'commerce_stock_local',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product_variation_type');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_type');
    $this->installConfig(['commerce_product']);
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installConfig('commerce_order');
    $this->installEntitySchema('commerce_stock_location_type');
    $this->installEntitySchema('commerce_stock_location');
    $this->installConfig(['commerce_stock']);
    $this->installConfig(['commerce_stock_local']);
    $this->installSchema('commerce_stock_local', [
      'commerce_stock_transaction_type',
      'commerce_stock_transaction',
      'commerce_stock_location_level',
    ]);

    // Change the workflow of the default order type.
    $order_type = OrderType::load('default');
    $order_type->setWorkflowId('order_fulfillment_validation');
    $order_type->save();

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

    $this->variation2 = ProductVariation::create([
      'type' => 'default',
      'sku' => 'TEST_' . strtolower($this->randomMachineName()),
      'title' => $this->randomString(),
      'status' => 1,
      'price' => [
        'number' => '12.12',
        'currency_code' => 'USD',
      ],
    ]);
    $this->variation2->save();
    $this->variation2 = $this->reloadEntity($this->variation2);

    $this->product = Product::create([
      'type' => 'default',
      'title' => $this->randomMachineName(),
      'stores' => [$this->store],
      'variations' => [$this->variation, $this->variation2],
    ]);
    $this->product->save();

    $this->checker = $stockServiceManager->getService($this->variation)
      ->getStockChecker();
    $this->checker2 = $stockServiceManager->getService($this->variation2)
      ->getStockChecker();
    $stockServiceConfiguration = $stockServiceManager->getService($this->variation)
      ->getConfiguration();
    $stockServiceConfiguration2 = $stockServiceManager->getService($this->variation2)
      ->getConfiguration();

    $context = new Context($user, $this->store);
    $this->locations = $stockServiceConfiguration->getAvailabilityLocations($context, $this->variation);
    $this->locations2 = $stockServiceConfiguration2->getAvailabilityLocations($context, $this->variation2);

    // Set initial Stock level.
    $stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', 10, 10.10, 'USD', StockTransactionsInterface::STOCK_IN, []);
    $stockServiceManager->createTransaction($this->variation2, $this->locations2[1]->getId(), '', 11, 11.11, 'USD', StockTransactionsInterface::STOCK_IN, []);

    $profile = Profile::create([
      'type' => 'customer',
      'uid' => $user->id(),
    ]);
    $profile->save();

    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'billing_profile' => $profile,
      'store_id' => $this->store->id(),
    ]);
    $order->save();

    $this->order = $this->reloadEntity($order);

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');

    $order_item2 = OrderItem::create([
      'type'       => 'default',
      'quantity'   => 2,
      'unit_price' => new Price('12.00', 'USD'),
    ]);
    $order_item2->save();

    // Add order item.
    $order_item1 = $order_item_storage->createFromPurchasableEntity($this->variation);
    $order_item1->save();
    $order_item1 = $this->reloadEntity($order_item1);
    $order->addItem($order_item1);
    $order->addItem($order_item2);
    $order->save();
    $this->order = $this->reloadEntity($order);
  }

  /**
   * Whether transactions are created on 'place' transition.
   */
  public function testOrderPlaceTransition() {
    // Tests initial stock level transactions did work.
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_IN);
    $result = $query->execute()->fetchAll();
    $this->assertEquals('1', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals('2', $result[1]->id);
    $this->assertEquals($this->variation2->id(), $result[1]->entity_id);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->assertEquals(11, $this->checker2->getTotalStockLevel($this->variation2, $this->locations2));

    // Tests the commerce_order.place.post_transition workflow event.
    $transition = $this->order->getState()->getTransitions();
    $this->order->setOrderNumber('2017/01');
    $this->order->getState()->applyTransition($transition['place']);
    $this->order->save();
    $this->assertEquals($this->order->getState()->getLabel(), 'Validation');
    $this->assertEquals(9, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('3', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals($this->order->id(), $result[0]->related_oid);
    $this->assertEquals($this->order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('-1.00', $result[0]->qty);
    $this->assertEquals('order placed', unserialize($result[0]->data)['message']);
  }

  /**
   * Whether transactions are not triggered for the orders in draft state.
   */
  public function testWorkflowCancelEventNotModifyStockOnDraftOrders() {
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $transition = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transition['cancel']);
    $this->order->save();
    $this->assertEquals($this->order->getState()->getLabel(), 'Canceled');
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

  /**
   * Tests that transactions are not created on cancel events with default
   * configuration.
   */
  public function testWorkflowCancelEventNotModifyStockWithDefaultSettings() {
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $transition = $this->order->getState()->getTransitions();
    $this->order->setOrderNumber('2017/01');
    $this->order->getState()->applyTransition($transition['place']);
    $this->order->save();
    $this->assertEquals(9, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->order->getState()->applyTransition($transition['cancel']);
    $this->order->save();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);
    $this->assertEquals(9, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

  /**
   * Whether proper transactions are created on cancel transition with config
   * set to act on order cancel.
   */
  public function testWorkflowCancelEvent() {
    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.core_stock_events');
    $config->set('core_stock_events_order_cancel', TRUE);
    $config->save();

    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $transition = $this->order->getState()->getTransitions();
    $this->order->setOrderNumber('2017/01');
    $this->order->getState()->applyTransition($transition['place']);
    $this->order->save();
    $this->assertEquals(9, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->order->getState()->applyTransition($transition['cancel']);
    $this->order->save();
    $this->assertEquals($this->order->getState()->getLabel(), 'Canceled');
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('4', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals($this->order->id(), $result[0]->related_oid);
    $this->assertEquals($this->order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('1.00', $result[0]->qty);
    $this->assertEquals('order canceled', unserialize($result[0]->data)['message']);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
  }

  /**
   * Test configuration.
   *
   * Tests that no transactions are triggered for all other order and order item
   * events in case we disabled all configuration options.
   */
  public function testDisableConfigurationPreventsTransaktions() {
    // Tests the order item creation event.
    $this->assertEquals(11, $this->checker2->getTotalStockLevel($this->variation2, $this->locations2));
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));

    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.core_stock_events');
    $config->set('core_stock_events_order_updates', FALSE);
    $config->set('core_stock_events_order_cancel', FALSE);
    $config->set('core_stock_events_order_complete', FALSE);
    $config->save();

    $transition = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transition['place']);
    $this->order->save();

    // Ensure all setup is done and we have the stock level we expect here.
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->assertEquals(11, $this->checker2->getTotalStockLevel($this->variation2, $this->locations2));

    /** @var \Drupal\commerce_order\OrderItemStorage $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');

    // Adding new order item to order do not trigger a transaction.
    $order_item = $order_item_storage->createFromPurchasableEntity($this->variation2, ['quantity' => 3]);
    $order_item->save();
    $order_item = $this->reloadEntity($order_item);
    $this->order->addItem($order_item);
    $this->order->save();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation2->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);
    $this->assertEquals(11, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));

    // Tests the order item update event.
    $items = $this->order->getItems();
    $items[0]->setQuantity('3')->save();
    $this->order->save();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));

    // Tests the order item delete event.
    $items = $this->order->getItems();
    $this->order->removeItem($items[0])->save();
    $items[0]->delete();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);

    // Tests the order delete event.
    $this->order->delete();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation2->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(0, $result);
  }

  /**
   * Test order edit events.
   *
   * Tests that transactions are created for all other order and order item
   * events.
   */
  public function testOrderEditEvents() {
    // Tests the order item creation event.
    $this->assertEquals(11, $this->checker2->getTotalStockLevel($this->variation2, $this->locations2));

    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.core_stock_events');
    $config->set('core_stock_events_order_updates', TRUE);
    $config->set('core_stock_events_order_cancel', TRUE);
    $config->set('core_stock_events_order_complete', TRUE);
    $config->save();

    $transition = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transition['place']);
    $this->order->save();

    // Ensure all setup is done and we have the stock level we expect here.
    $this->assertEquals(9, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->assertEquals(11, $this->checker2->getTotalStockLevel($this->variation2, $this->locations2));

    /** @var \Drupal\commerce_order\OrderItemStorage $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');

    // Add new order item to order.
    $order_item = $order_item_storage->createFromPurchasableEntity($this->variation2, ['quantity' => 3]);
    $order_item->save();
    $order_item = $this->reloadEntity($order_item);
    $this->order->addItem($order_item);
    $this->order->save();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation2->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertEquals(8, $this->checker->getTotalStockLevel($this->variation2, $this->locations2));

    $this->assertCount(1, $result);
    $this->assertEquals('4', $result[0]->id);
    $this->assertEquals($this->variation2->id(), $result[0]->entity_id);
    $this->assertEquals($this->variation2->getEntityTypeId(), $result[0]->entity_type);
    $this->assertEquals($this->order->id(), $result[0]->related_oid);
    $this->assertEquals($this->order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('-3.00', $result[0]->qty);
    $this->assertEquals('order item added', unserialize($result[0]->data)['message']);

    // Tests the order item update event.
    $items = $this->order->getItems();
    $items[0]->setQuantity('3')->save();
    $this->order->save();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_SALE);
    $result = $query->execute()->fetchAll();
    $this->assertCount(2, $result);
    $this->assertEquals('5', $result[1]->id);
    $this->assertEquals($this->variation->id(), $result[1]->entity_id);
    $this->assertEquals($this->order->id(), $result[1]->related_oid);
    $this->assertEquals($this->order->getCustomerId(), $result[1]->related_uid);
    $this->assertEquals('-2.00', $result[1]->qty);
    $this->assertEquals('order item quantity updated', unserialize($result[1]->data)['message']);
    $this->assertEquals(7, $this->checker->getTotalStockLevel($this->variation, $this->locations));

    // Tests the order item delete event.
    $items = $this->order->getItems();
    $this->order->removeItem($items[0])->save();
    $items[0]->delete();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('6', $result[0]->id);
    $this->assertEquals($this->variation->id(), $result[0]->entity_id);
    $this->assertEquals($this->order->id(), $result[0]->related_oid);
    $this->assertEquals($this->order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('3.00', $result[0]->qty);
    $this->assertEquals('order item deleted', unserialize($result[0]->data)['message']);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));

    // Tests the order delete event.
    $this->order->delete();
    $query = \Drupal::database()->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $this->variation2->id())
      ->condition('transaction_type_id', StockTransactionsInterface::STOCK_RETURN);
    $result = $query->execute()->fetchAll();
    $this->assertCount(1, $result);
    $this->assertEquals('7', $result[0]->id);
    $this->assertEquals($this->variation2->id(), $result[0]->entity_id);
    $this->assertEquals($this->order->id(), $result[0]->related_oid);
    $this->assertEquals($this->order->getCustomerId(), $result[0]->related_uid);
    $this->assertEquals('3.00', $result[0]->qty);
    $this->assertEquals('order deleted', unserialize($result[0]->data)['message']);
    $this->assertEquals(10, $this->checker->getTotalStockLevel($this->variation, $this->locations));
    $this->assertEquals(11, $this->checker2->getTotalStockLevel($this->variation2, $this->locations2));
  }

  /**
   * Its absolutly possible to get orders from an order event that doesn't hold
   * a $order->original order object. Here we test, whether our event subscriber fail
   * graceful in such cases.
   */
  public function testFailGracefulIfNoPurchasableEntity() {
    $prophecy = $this->prophesize(OrderEvent::class);

    $order = $this->order;
    $order->original = NULL;

    $prophecy->getOrder()->willReturn($order);
    $event = $prophecy->reveal();

    $stockServiceManagerStub = $this->prophesize(StockServiceManager::class);

    $sut = new OrderEventSubscriber($stockServiceManagerStub->reveal());
    $sut->onOrderUpdate($event);
  }

}
