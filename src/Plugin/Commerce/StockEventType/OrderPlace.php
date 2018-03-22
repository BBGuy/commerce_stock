<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

/**
 * Provides the order place event type.
 *
 * @StockEventType(
 *   id = "commerce_stock_order_place",
 *   label = @Translation("Commerce stock order place event type"),
 *   displayLabel = @Translation("Order Place Event"),
 *   transactionMessage = @Translation("Order placed."),
 * )
 */
class OrderPlace extends StockEventTypeBase {}
