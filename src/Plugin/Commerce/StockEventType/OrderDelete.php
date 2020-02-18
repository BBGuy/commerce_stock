<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

/**
 * Provides the order delete event type.
 *
 * @StockEventType(
 *   id = "commerce_stock_order_delete",
 *   label = @Translation("Commerce stock order delete event type"),
 *   displayLabel = @Translation("Order Delete Event"),
 *   transactionMessage = @Translation("Order deleted."),
 * )
 */
class OrderDelete extends StockEventTypeBase {}
