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
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost);

}
