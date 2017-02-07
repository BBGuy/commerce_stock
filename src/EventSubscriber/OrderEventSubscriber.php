<?php

namespace Drupal\commerce_stock\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_stock\StockServiceManagerInterface;
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
        $quantity = -1 * $item->getQuantity();
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $quantity);
        $transaction_type = TRANSACTION_TYPE_SALE;
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order placed'],
        ];
        $service->getStockUpdater()->createTransaction($entity->id(), $location->getId(), '', $quantity, NULL, $transaction_type, $metadata);
      }
    }
  }

  /**
   * Acts on the order update event to create transactions for new items.
   *
   * The reason this isn't handled by OrderEvents::ORDER_ITEM_INSERT is because
   * that event never has the correct values.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The order event.
   */
  public function onOrderUpdate(OrderEvent $event) {
    $order = $event->getOrder();
    $original_order = $order->original;
    foreach ($order->getItems() as $item) {
      if (!$original_order->hasItem($item)) {
        if ($order && !in_array($order->getState()->value, ['draft', 'canceled'])) {
          $entity = $item->getPurchasedEntity();
          $service = $this->stockServiceManager->getService($entity);
          $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $item->getQuantity());
          $amount = -1 * $item->getQuantity();
          $metadata = [
            'related_oid' => $order->id(),
            'related_uid' => $order->getCustomerId(),
            'data' => ['message' => 'order item added'],
          ];
          $service->getStockUpdater()->createTransaction($entity->id(), $location->getId(), '', $amount, NULL, TRANSACTION_TYPE_SALE, $metadata);
        }
      }
    }
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
        $quantity = $item->getQuantity();
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $quantity);
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order canceled'],
        ];
        $service->getStockUpdater()->createTransaction($entity->id(), $location->getId(), '', $quantity, NULL, TRANSACTION_TYPE_RETURN, $metadata);
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
        $quantity = $item->getQuantity();
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $quantity);
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order deleted'],
        ];
        $service->getStockUpdater()->createTransaction($entity->id(), $location->getId(), '', $quantity, NULL, TRANSACTION_TYPE_RETURN, $metadata);
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
        $transaction_type = ($diff < 0) ? TRANSACTION_TYPE_SALE : TRANSACTION_TYPE_RETURN;
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $diff);
        $metadata = [
          'related_oid' => $order->id(),
          'related_uid' => $order->getCustomerId(),
          'data' => ['message' => 'order item quantity updated'],
        ];
        $service->getStockUpdater()->createTransaction($entity->id(), $location->getId(), '', $diff, NULL, $transaction_type, $metadata);
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
      $location = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $item->getQuantity());
      $metadata = [
        'related_oid' => $order->id(),
        'related_uid' => $order->getCustomerId(),
        'data' => ['message' => 'order item deleted'],
      ];
      $service->getStockUpdater()->createTransaction($entity->id(), $location->getId(), '', $item->getQuantity(), NULL, TRANSACTION_TYPE_RETURN, $metadata);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      // State change events fired on workflow transitions from state_machine.
      'commerce_order.place.post_transition' => ['onOrderPlace', -100],
      'commerce_order.cancel.post_transition' => ['onOrderCancel', -100],
      // Order storage events dispatched during entity operations in CommerceContentEntityStorage.
      // ORDER_UPDATE handles new order items since ORDER_ITEM_INSERT doesn't.
      OrderEvents::ORDER_UPDATE => ['onOrderUpdate', -100],
      OrderEvents::ORDER_PREDELETE => ['onOrderDelete', -100],
      OrderEvents::ORDER_ITEM_UPDATE => ['onOrderItemUpdate', -100],
      OrderEvents::ORDER_ITEM_DELETE => ['onOrderItemDelete', -100],
    ];
    return $events;
  }

}