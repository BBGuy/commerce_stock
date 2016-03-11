<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\Entity\EntityStockUpdateInterface.
 */

namespace Drupal\commerce_stock\Entity;

/**
 * Defines a common interface for writing stock.
 */
interface EntityStockUpdateInterface {


  /**
   * check if product is in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function createTransaction($product_id, $location_id, $zone, $quantity, $unit_cost);

}
