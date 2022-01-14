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
use Drupal\commerce_stock\Plugin\StockEventsInterface;
use Drupal\commerce_stock\StockEventsManagerInterface;
use Drupal\commerce_stock\StockEventTypeManagerInterface;
use Drupal\commerce_stock\StockLocationInterface;
use Drupal\commerce_stock\StockServiceManagerInterface;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * The config factory manager.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->stockServiceManager = $stock_service_manager;
    $this->eventTypeManager = $event_type_manager;
    $this->eventsManager = $events_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Creates a stock transaction when an order is placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The order workflow event.
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    $config = $this->configFactory->get('commerce_stock.core_stock_events');
    $complete_event_type = $config->get('core_stock_events_order_complete_event_type') ?? 'placed';
    // Only update a placed order if the matching configuration is set.
    if ($complete_event_type == 'placed') {
      // Create the complete transaction.
      $this->orderCompleteTransaction($event);
    }
  }

  /**
   * Creates a stock transaction when an order is placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The order workflow event.
   */
  public function onOrderComplete(WorkflowTransitionEvent $event) {
    $id = $event->getTransition()->getToState()->getId();
    $config = $this->configFactory->get('commerce_stock.core_stock_events');
    $complete_event_type = $config->get('core_stock_events_order_complete_event_type') ?? 'placed';
    // Only update if a completed event and the matching configuration is set.
    if (($id == 'completed') && ($complete_event_type == 'completed')) {
      // Create the complete transaction.
      $this->orderCompleteTransaction($event);
    }
  }

  /**
   * Creates a stock transaction when an order complete or placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The order workflow event.
   */
  public function orderCompleteTransaction(WorkflowTransitionEvent $event) {
    $eventType = $this->getEventType('commerce_stock_order_place');
    $order = $event->getEntity();
    // We are only handling the commerce order workflows.
    if ($order->getState()->getWorkflow()->getGroup() !== 'commerce_order') {
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
    // Check if we should create a transaction.
    if (!$this->shouldWeUpdateOrderStockOrder($event, $eventType)) {
      return;
    }

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
    //  Don't update canceled draft orders.
    if ($original_order->getState()->value == 'draft') {
      return FALSE;
    }

    // Check if we should create a transaction.
    if (!$this->shouldWeUpdateOrderStockOrder($event, $eventType)) {
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
    // Check if we should create a transaction.
    if (!$this->shouldWeUpdateOrderStockOrder($event, $eventType)) {
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
    // Check if we should create a transaction.
    if (!$this->shouldWeUpdateOrderStockItem($event, $eventType)) {
      return;
    }
    $original = $this->getOriginalEntity($item);
    $diff = $original->getQuantity() - $item->getQuantity();
    // Only create a transaction if quantity changed.
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
    // Check if we should create a transaction.
    if (!$this->shouldWeUpdateOrderStockItem($event, $eventType)) {
      return;
    }
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
    $context = self::createContextFromOrder($order);
    $location = $this->stockServiceManager->getTransactionLocation($context, $entity, $item->getQuantity());
    $transaction_type = StockTransactionsInterface::STOCK_RETURN;

    $this->runTransactionEvent($eventType, $context,
      $entity, $item->getQuantity(), $location, $transaction_type, $order);
  }


  private function shouldWeUpdateOrderStockOrder($event, StockEventTypeInterface $event_type): bool {
    // Make sure we have an order.
    if ($event instanceof OrderEvent) {
      $order = $event->getOrder();
    }
    elseif ($event instanceof WorkflowTransitionEvent) {
      $order = $event->getEntity();
    }

    if (!isset($order)) {
      return FALSE;
    }
    return $this->shouldWeUpdateOrderStock($order, $event_type);
  }

  /**
   *
   */
  private function shouldWeUpdateOrderStockItem(OrderItemEvent $event, StockEventTypeInterface $event_type): bool {
    $item = $event->getOrderItem();
    $order = $item->getOrder();
    if (!isset($order)) {
      return FALSE;
    }
    return $this->shouldWeUpdateOrderStock($order, $event_type);
  }

  /**
   * Should we create stock update transactions?
   *
   * We only want to create a stock update transaction on an order that has been
   * placed/completed.
   */
  private function shouldWeUpdateOrderStock(Order $order, StockEventTypeInterface $event_type): bool {
    // We are only handling the commerce order workflows.
    if ($order->getState()->getWorkflow()->getGroup() !== 'commerce_order') {
      return FALSE;
    }

    // Check for a valid completed state.
    $order_state = $order->getState()->value;
    $config = $this->configFactory->get('commerce_stock.core_stock_events');
    $complete_event_type = $config->get('core_stock_events_order_complete_event_type') ?? 'placed';

    // Don't double return stock.
    if ($event_type->getPluginId() == 'commerce_stock_order_delete' &&  $order_state == 'canceled') {
      return FALSE;
    }

    switch ($complete_event_type) {
      case 'placed':
        if (in_array($order_state, ['draft'])) {
          return FALSE;
        }
        break;
      case 'completed':
        if (in_array($order_state, [
          'draft',
          'validation',
          'canceled',
          'fulfillment',
        ])) {
          return FALSE;
        }
        break;

      default:
        // Completed transaction disabled.
        return FALSE;
    }

    // Make sure the event is enabled.
    $order_update_events = [
      StockEventsInterface::ORDER_UPDATE_EVENT,
      StockEventsInterface::ORDER_ITEM_UPDATE_EVENT,
    ];
    $order_delete_events = [
      StockEventsInterface::ORDER_CANCEL_EVENT,
      StockEventsInterface::ORDER_DELET_EVENT,
      StockEventsInterface::ORDER_ITEM_DELETE_EVENT,
    ];
    $config = \Drupal::configFactory()->get('commerce_stock.core_stock_events');
    $event_type_id = CoreStockEvents::mapStockEventIds($event_type->getPluginId());
    $core_stock_events_order_updates = $config->get('core_stock_events_order_updates') ?? FALSE;
    $core_stock_events_order_cancel = $config->get('core_stock_events_order_cancel') ?? FALSE;
    if ((in_array($event_type_id, $order_update_events)) && !$core_stock_events_order_updates) {
      return FALSE;
    }
    elseif ((in_array($event_type_id, $order_delete_events)) && !$core_stock_events_order_cancel) {
      return FALSE;
    }

    // If not failed any of the condition, return TRUE.
    return TRUE;
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      // State change events fired on workflow transitions from state_machine.
      'commerce_order.post_transition' => ['onOrderComplete', -100],
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

    $event_handler = $this->configFactory->get('commerce_stock.service_manager')->get('stock_events_plugin_id');

    return $this->eventsManager->createInstance($event_handler)
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
