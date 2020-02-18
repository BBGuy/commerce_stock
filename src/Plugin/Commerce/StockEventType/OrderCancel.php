<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

/**
 * Provides the order place event type.
 *
 * @StockEventType(
 *   id = "commerce_stock_order_cancel",
 *   label = @Translation("Commerce stock order cancel event type"),
 *   displayLabel = @Translation("Order Cancel Event"),
 *   transactionMessage = @Translation("Order canceled."),
 * )
 */
class OrderCancel extends StockEventTypeBase {}
