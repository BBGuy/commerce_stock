<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

/**
 * Provides the order item update event type.
 *
 * @StockEventType(
 *   id = "commerce_stock_order_item_update",
 *   label = @Translation("Commerce stock order item update event type"),
 *   displayLabel = @Translation("Order Item Update Event"),
 *   transactionMessage = @Translation("Order item updated."),
 * )
 */
class OrderItemUpdate extends StockEventTypeBase {}
