<?php

namespace Drupal\commerce_stock\Plugin\StockEvents;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface;
use Drupal\commerce_stock\Plugin\StockEventsInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Core stock events base class.
 */
abstract class CoreStockEventsBase extends PluginBase implements StockEventsInterface {

  /**
   * Helper method for building the transaction metadata.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The commerce order.
   * @param \Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface $stock_event_type
   *   The stock event type.
   * @param string|null $message
   *   An optional transaction message.
   * @param array $data
   *   Arbitrary data to save in transactions metadata.
   *
   * @return array
   *   The metadata.
   */
  protected function getMetadata(Order $order, StockEventTypeInterface $stock_event_type, $message = NULL, array $data = []) {
    $metadata = array_merge(
      ['message' => $message ?: $stock_event_type->getDefaultMessage()],
      $data
    );
    return [
      'related_oid' => $order->id(),
      'related_uid' => $order->getCustomerId(),
      'data' => $metadata,
    ];
  }

}
