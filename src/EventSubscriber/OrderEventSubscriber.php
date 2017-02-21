<?php

namespace Drupal\commerce_stock\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_stock\StockServiceManagerInterface;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Performs stock transactions on order and order item events.
 */
class OrderEventSubscriber implements EventSubscriberInterface {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * Constructs a new OrderReceiptSubscriber object.
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   */
  public function __construct(StockServiceManagerInterface $stock_service_manager) {
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * Creates a stock transaction when an order is placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The order workflow event.
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    $order = $event->getEntity();
    foreach ($order->getItems() as $item) {
      $entity = $item->getPurchasedEntity();
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if ($checker->getIsStockManaged($entity)) {
        // If always in stock then no need to create a transaction.
        if ($checker->getIsAlwaysInStock($entity)) {
          return;
        }
        $quantity = -1 * $item->getQuantity();
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $quantity);
        $transaction_type = StockTransactionsInterface::STOCK_SALE;
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order placed'],
        ];
        $service->getStockUpdater()->createTransaction($entity, $location, '', $quantity, NULL, $transaction_type, $metadata);
      }
    }
  }

  /**
   * Responds to order update events.
   *
   * While we do not care about order updates per-se, we use the ORDER_UPDATE
   * event to create transactions for NEW order items because it is only during
   * this order update event--after Order::postSave() has run--that we have
   * 'order_id' back-references set on each order item.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The order event.
   *
   * @see https://www.drupal.org/node/2853277
   */
  public function onOrderUpdate(OrderEvent $event) {
    $order = $event->getOrder();
    foreach ($order->getItems() as $item) {
      // Handle new order items added to placed orders.
      if (!in_array($order->getState()->value, ['draft', 'canceled'])
          && !$order->original->hasItem($item)) {
        $this->handleOrderItemInsert($item);
      }
    }
  }

  /**
   * Handles new order items being added to an order.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $item
   *   The new order item.
   */
  protected function handleOrderItemInsert(OrderItemInterface $item) {
    $order = $item->getOrder();
    $entity = $item->getPurchasedEntity();
    $service = $this->stockServiceManager->getService($entity);
    $checker = $service->getStockChecker();
    // If always in stock then no need to create a transaction.
    if ($checker->getIsAlwaysInStock($entity)) {
      return;
    }
    $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $item->getQuantity());
    $amount = -1 * $item->getQuantity();
    $metadata = [
      'related_oid' => $order->id(),
      'related_uid' => $order->getCustomerId(),
      'data' => ['message' => 'order item added'],
    ];
    $service->getStockUpdater()->createTransaction($entity, $location, '', $amount, NULL, StockTransactionsInterface::STOCK_SALE, $metadata);
  }

  /**
   * Performs a stock transaction for an order Cancel event.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The order workflow event.
   */
  public function onOrderCancel(WorkflowTransitionEvent $event) {
    $order = $event->getEntity();
    foreach ($order->getItems() as $item) {
      $entity = $item->getPurchasedEntity();
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if ($checker->getIsStockManaged($entity)) {
        // If always in stock then no need to create a transaction.
        if ($checker->getIsAlwaysInStock($entity)) {
          return;
        }
        $quantity = $item->getQuantity();
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $quantity);
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order canceled'],
        ];
        $service->getStockUpdater()->createTransaction($entity, $location, '', $quantity, NULL, StockTransactionsInterface::STOCK_RETURN, $metadata);
      }
    }
  }

  /**
   * Performs a stock transaction on an order delete event.
   *
   * This happens on PREDELETE since the items are not available after DELETE.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The order event.
   */
  public function onOrderDelete(OrderEvent $event) {
    $order = $event->getOrder();
    if ($order->getState()->value == 'canceled') {
      return;
    }
    $items = $order->getItems();
    foreach ($items as $item) {
      $entity = $item->getPurchasedEntity();
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if ($checker->getIsStockManaged($entity)) {
        // If always in stock then no need to create a transaction.
        if ($checker->getIsAlwaysInStock($entity)) {
          return;
        }
        $quantity = $item->getQuantity();
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $quantity);
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order deleted'],
        ];
        $service->getStockUpdater()->createTransaction($entity, $location, '', $quantity, NULL, StockTransactionsInterface::STOCK_RETURN, $metadata);
      }
    }
  }

  /**
   * Performs a stock transaction on an order item update.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The order item event.
   */
  public function onOrderItemUpdate(OrderItemEvent $event) {
    $item = $event->getOrderItem();
    $order = $item->getOrder();
    if ($order && !in_array($order->getState()->value, ['draft', 'canceled'])) {
      $diff = $item->original->getQuantity() - $item->getQuantity();
      if ($diff) {
        $entity = $item->getPurchasedEntity();
        $service = $this->stockServiceManager->getService($entity);
        $checker = $service->getStockChecker();
        if ($checker->getIsStockManaged($entity)) {
          // If always in stock then no need to create a transaction.
          if ($checker->getIsAlwaysInStock($entity)) {
            return;
          }
          $transaction_type = ($diff < 0) ? StockTransactionsInterface::STOCK_SALE : StockTransactionsInterface::STOCK_RETURN;
          $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $diff);
          $metadata = [
            'related_oid' => $order->id(),
            'related_uid' => $order->getCustomerId(),
            'data' => ['message' => 'order item quantity updated'],
          ];
          $service->getStockUpdater()
            ->createTransaction($entity, $location, '', $diff, NULL, $transaction_type, $metadata);
        }
      }
    }
  }

  /**
   * Performs a stock transaction when an order item is deleted.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The order item event.
   */
  public function onOrderItemDelete(OrderItemEvent $event) {
    $item = $event->getOrderItem();
    $order = $item->getOrder();
    if ($order && !in_array($order->getState()->value, ['draft', 'canceled'])) {
      $entity = $item->getPurchasedEntity();
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if ($checker->getIsStockManaged($entity)) {
        // If always in stock then no need to create a transaction.
        if ($checker->getIsAlwaysInStock($entity)) {
          return;
        }
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $item->getQuantity());
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order item deleted'],
        ];
        $service->getStockUpdater()
          ->createTransaction($entity, $location, '', $item->getQuantity(), NULL, StockTransactionsInterface::STOCK_RETURN, $metadata);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      // Workflow transition events.
      // @see commerce_order.workflows.yml
      'commerce_order.place.post_transition' => ['onOrderPlace', -100],
      'commerce_order.cancel.post_transition' => ['onOrderCancel', -100],
      // Entity storage events.
      // @see CommerceContentEntityStorage::invokeHook()
      OrderEvents::ORDER_UPDATE => ['onOrderUpdate', -100],
      OrderEvents::ORDER_PREDELETE => ['onOrderDelete', -100],
      OrderEvents::ORDER_ITEM_UPDATE => ['onOrderItemUpdate', -100],
      OrderEvents::ORDER_ITEM_DELETE => ['onOrderItemDelete', -100],
    ];
    return $events;
  }

}
