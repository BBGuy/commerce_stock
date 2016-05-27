<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockUpdateInterface.
 */

namespace Drupal\commerce_stock;

/**
 * Defines a common interface for writing stock.
 */
interface StockUpdateInterface {

  /**
   * Check if product is in stock. @todo Really? Is this the correct description?
   *
   * @param int $variation_id
   *   The variation ID
   * @param int $location_id
   *   The location ID
   * @param string $zone
   *   The zone
   * @param float $quantity
   *   Tbe quantity
   * @param float $unit_cost
   *   The unit cost
   * @param int $transaction_type_id
   *   The transaction type ID
   * @param array $metadata
   *   Holds all the optional values those are:
   *     - related_tid: related transaction.
   *     - related_oid: related order.
   *     - related_uid: related user.
   *     - data: Serialized data array.
   * @return mixed
   *   @todo document the return type
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata);

}
