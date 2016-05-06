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
   * check if product is in stock.
   *
   * $metadata holds all the optional values those are:
   * related_tid - related transaction.
   * related_oid - related order.
   * related_uid - related user.
   * data - Serialized data array.
   *
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);

}
