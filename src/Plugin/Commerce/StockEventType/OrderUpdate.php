<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

/**
 * Provides the order place event type.
 *
 * @StockEventType(
 *   id = "commerce_stock_order_update",
 *   label = @Translation("Commerce stock order update event type"),
 *   displayLabel = @Translation("Order Update Event"),
 *   transactionMessage = @Translation("Order updated: new order item added."),
 * )
 */
class OrderUpdate extends StockEventTypeBase {}
