<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\AlwaysInStock.
 */


namespace Drupal\commerce_stock;


use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockUpdateInterface;



class AlwaysInStock implements StockCheckInterface, StockUpdateInterface {

  /**
   * Create a stock transaction.
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost) {
    // Do nothing.
  }


  /**
   * Gets the Stock level.
   *
   * @return int
   *   Stock Level.
   */
  public function getStockLevel($variation_id, $locations) {
    // @todo this can be configurable?
    return 999;
  }


  /**
   * Check if product is in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsInStock($variation_id, $locations) {
    return TRUE;
  }


  /**
   * Check if product is always in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock($variation_id) {
    return TRUE;
  }

  /**
   * Check if product is managed by stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsStockManaged($variation_id) {
    // @todo - Not sure about this one. The result will be the same for:
    // TRUE - managed by this and will always be availalbe.
    // FALSE - not managed so will be available.
    return TRUE;
  }

  /**
   * Get list of locations.
   *
   * @return array
   *   List of locations keyd by ID.
   */
  public function getLocationList($return_active_only = TRUE) {
    // We dont have locations so return an empty array.
    return array();
  }

}
