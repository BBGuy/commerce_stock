<?php

namespace Drupal\commerce_stock;

/**
 * Defines a common interface for writing stock.
 */
interface StockUpdateInterface {

  /**
   * Create a stock transaction.
   *
   * @param int $variation_id
   *   The variation ID.
   * @param int $location_id
   *   The location ID.
   * @param string $zone
   *   The zone.
   * @param float $quantity
   *   Tbe quantity.
   * @param float $unit_cost
   *   The unit cost.
   * @param int $transaction_type_id
   *   The transaction type ID.
   * @param array $metadata
   *   Holds all the optional values those are:
   *     - related_tid: related transaction.
   *     - related_oid: related order.
   *     - related_uid: related user.
   *     - data: Serialized data array.
   *
   * @return int
   *   Return the ID of the transaction.
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata);

}
