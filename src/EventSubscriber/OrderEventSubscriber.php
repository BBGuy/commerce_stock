<?php

namespace Drupal\commerce_stock\EventSubscriber;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_stock\ContextCreatorTrait;
use Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface;
use Drupal\commerce_stock\Plugin\StockEvents\CoreStockEvents;
use Drupal\commerce_stock\StockEventsManagerInterface;
use Drupal\commerce_stock\StockEventTypeManagerInterface;
use Drupal\commerce_stock\StockLocationInterface;
use Drupal\commerce_stock\StockServiceManagerInterface;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Performs stock transactions on order and order item events.
 */
class OrderEventSubscriber implements EventSubscriberInterface {

  use ContextCreatorTrait;
  use StringTranslationTrait;

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * The stock event types.
   *
   * @var \Drupal\commerce_stock\StockEventTypeManagerInterface
   */
  protected $eventTypeManager;

  /**
   * The stock events manager.
   *
   * @var \Drupal\commerce_stock\StockEventsManagerInterface
   */
  protected $eventsManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new OrderReceiptSubscriber object.
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   * @param \Drupal\commerce_stock\StockEventTypeManagerInterface $event_type_manager
   *   The stock event type manager.
   * @param \Drupal\commerce_stock\StockEventsManagerInterface $events_manager
   *   The stock events manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    StockServiceManagerInterface $stock_service_manager,
    StockEventTypeManagerInterface $event_type_manager,
    StockEventsManagerInterface $events_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->stockServiceManager = $stock_service_manager;
    $this->eventTypeManager = $event_type_manager;
    $this->eventsManager = $events_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Creates a stock transaction when an order is placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The order workflow event.
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    $eventType = $this->getEventType('commerce_stock_order_place');
    $order = $event->getEntity();
    foreach ($order->getItems() as $item) {
      $entity = $item->getPurchasedEntity();
      if (!$entity) {
        continue;
      }
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      // If always in stock then no need to create a transaction.
      if ($checker->getIsAlwaysInStock($entity)) {
        continue;
      }
      $quantity = -1 * $item->getQuantity();
      $context = self::createContextFromOrder($order);
      $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $quantity);
      $transaction_type = StockTransactionsInterface::STOCK_SALE;

      $this->runTransactionEvent($eventType, $context,
        $entity, $quantity, $location, $transaction_type, $order);
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
    $eventType = $this->getEventType('commerce_stock_order_update');
    $order = $event->getOrder();
    $original_order = $this->getOriginalEntity($order);

    foreach ($order->getItems() as $item) {
      if (!$original_order->hasItem($item)) {
        if ($order && !in_array($order->getState()->value, [
          'draft',
          'canceled',
        ])) {
          $entity = $item->getPurchasedEntity();
          if (!$entity) {
            continue;
          }
          $service = $this->stockServiceManager->getService($entity);
          $checker = $service->getStockChecker();
          // If always in stock then no need to create a transaction.
          if ($checker->getIsAlwaysInStock($entity)) {
            continue;
          }
          $context = self::createContextFromOrder($order);
          $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $item->getQuantity());
          $transaction_type = StockTransactionsInterface::STOCK_SALE;
          $quantity = -1 * $item->getQuantity();

          $this->runTransactionEvent($eventType, $context,
            $entity, $quantity, $location, $transaction_type, $order);
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
    $eventType = $this->getEventType('commerce_stock_order_cancel');
    $order = $event->getEntity();
    $original_order = $this->getOriginalEntity($order);

    if ($original_order && $original_order->getState()->value === 'draft') {
      return;
    }
    foreach ($order->getItems() as $item) {
      $entity = $item->getPurchasedEntity();
      if (!$entity) {
        continue;
      }
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      // If always in stock then no need to create a transaction.
      if ($checker->getIsAlwaysInStock($entity)) {
        continue;
      }
      $quantity = $item->getQuantity();
      $context = self::createContextFromOrder($order);
      $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $quantity);
      $transaction_type = StockTransactionsInterface::STOCK_RETURN;

      $this->runTransactionEvent($eventType, $context,
        $entity, $quantity, $location, $transaction_type, $order);
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
    $eventType = $this->getEventType('commerce_stock_order_delete');
    $order = $event->getOrder();
    if (in_array($order->getState()->value, ['draft', 'canceled'])) {
      return;
    }
    $items = $order->getItems();
    foreach ($items as $item) {
      $entity = $item->getPurchasedEntity();
      if (!$entity) {
        continue;
      }
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      // If always in stock then no need to create a transaction.
      if ($checker->getIsAlwaysInStock($entity)) {
        continue;
      }
      $quantity = $item->getQuantity();
      $context = self::createContextFromOrder($order);
      $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $quantity);
      $transaction_type = StockTransactionsInterface::STOCK_RETURN;

      $this->runTransactionEvent($eventType, $context,
        $entity, $quantity, $location, $transaction_type, $order);
    }
  }

  /**
   * Performs a stock transaction on an order item update.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The order item event.
   */
  public function onOrderItemUpdate(OrderItemEvent $event) {
    $eventType = $this->getEventType('commerce_stock_order_item_update');
    $item = $event->getOrderItem();
    $order = $item->getOrder();

    if ($order && !in_array($order->getState()->value, ['draft', 'canceled'])) {
      $original = $this->getOriginalEntity($item);
      $diff = $original->getQuantity() - $item->getQuantity();
      if ($diff) {
        $entity = $item->getPurchasedEntity();
        if (!$entity) {
          return;
        }
        $service = $this->stockServiceManager->getService($entity);
        $checker = $service->getStockChecker();
        // If always in stock then no need to create a transaction.
        if ($checker->getIsAlwaysInStock($entity)) {
          return;
        }
        $transaction_type = ($diff < 0) ? StockTransactionsInterface::STOCK_SALE : StockTransactionsInterface::STOCK_RETURN;
        $context = self::createContextFromOrder($order);
        $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $diff);

        $this->runTransactionEvent($eventType, $context,
          $entity, $diff, $location, $transaction_type, $order);
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
    $eventType = $this->getEventType('commerce_stock_order_item_delete');
    $item = $event->getOrderItem();
    $order = $item->getOrder();
    if ($order && !in_array($order->getState()->value, ['draft', 'canceled'])) {
      $entity = $item->getPurchasedEntity();
      if (!$entity) {
        return;
      }
      /** @var \Drupal\commerce_stock\StockServiceInterface $service */
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      // If always in stock then no need to create a transaction.
      if ($checker->getIsAlwaysInStock($entity)) {
        return;
      }
      $context = self::createContextFromOrder($order);
      $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $item->getQuantity());
      $transaction_type = StockTransactionsInterface::STOCK_RETURN;

      $this->runTransactionEvent($eventType, $context,
        $entity, $item->getQuantity(), $location, $transaction_type, $order);
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
      // Order storage events dispatched during entity operations in
      // CommerceContentEntityStorage.
      // ORDER_UPDATE handles new order items since ORDER_ITEM_INSERT doesn't.
      OrderEvents::ORDER_UPDATE => ['onOrderUpdate', -100],
      OrderEvents::ORDER_PREDELETE => ['onOrderDelete', -100],
      OrderEvents::ORDER_ITEM_UPDATE => ['onOrderItemUpdate', -100],
      OrderEvents::ORDER_ITEM_DELETE => ['onOrderItemDelete', -100],
    ];
    return $events;
  }

  /**
   * Run the transaction event.
   *
   * @param \Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface $event_type
   *   The stock event type.
   * @param \Drupal\commerce\Context $context
   *   The context containing the customer & store.
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $quantity
   *   The quantity.
   * @param \Drupal\commerce_stock\StockLocationInterface $location
   *   The stock location.
   * @param int $transaction_type_id
   *   The transaction type ID.
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The original order the transaction belongs to.
   *
   * @return int
   *   Return the ID of the transaction or FALSE if no transaction created.
   */
  private function runTransactionEvent(
    StockEventTypeInterface $event_type,
    Context $context,
    PurchasableEntityInterface $entity,
    $quantity,
    StockLocationInterface $location,
    $transaction_type_id,
    Order $order
  ) {

    $data['message'] = $event_type->getDefaultMessage();
    $metadata = [
      'related_oid' => $order->id(),
      'related_uid' => $order->getCustomerId(),
      'data' => $data,
    ];

    $event_type_id = CoreStockEvents::mapStockEventIds($event_type->getPluginId());

    return $this->eventsManager->createInstance('core_stock_events')
      ->stockEvent($context, $entity, $event_type_id, $quantity, $location,
        $transaction_type_id, $metadata);
  }

  /**
   * Creates a stock event type object.
   *
   * @param string $plugin_id
   *   The id of the stock event type.
   *
   * @return \Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface
   *   The stock event type.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getEventType($plugin_id) {
    return $this->eventTypeManager->createInstance($plugin_id);
  }

  /**
   * Returns the entity from an updated entity object. In certain
   * cases the $entity->original property is empty for updated entities. In such
   * a situation we try to load the unchanged entity from storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The changed/updated entity object.
   *
   * @return null|\Drupal\Core\Entity\EntityInterface
   *   The original unchanged entity object or NULL.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getOriginalEntity(EntityInterface $entity) {
    // $entity->original only exists during save. See
    // \Drupal\Core\Entity\EntityStorageBase::save().
    // If we don't have $entity->original we try to load it.
    $original_entity = NULL;
    $original_entity = $entity->original;

    // @ToDo Consider how this may change due to: ToDo https://www.drupal.org/project/drupal/issues/2839195
    if (!$original_entity) {
      $id = $entity->getOriginalId() !== NULL ? $entity->getOriginalId() : $entity->id();
      $original_entity = $this->entityTypeManager
        ->getStorage($entity->getEntityTypeId())
        ->loadUnchanged($id);
    }
    return $original_entity;
  }

}
