<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

/**
 * Provides the order item delete event type.
 *
 * @StockEventType(
 *   id = "commerce_stock_order_item_delete",
 *   label = @Translation("Commerce stock order item delete event type"),
 *   displayLabel = @Translation("Order Item Delete Event"),
 *   transactionMessage = @Translation("Order item deleted."),
 * )
 */
class OrderItemDelete extends StockEventTypeBase {}
