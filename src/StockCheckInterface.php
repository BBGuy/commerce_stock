<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockCheckInterface.
 */

namespace Drupal\commerce_stock;


/**
 * Defines a common interface for stock checking.
 */
interface StockCheckInterface {

  /**
   * Gets the Stock level.
   *
   * @return int
   *   Stock Level.
   */
  public function getStockLevel($variation_id, $locations);


  /**
   * check if product is in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsInStock($variation_id, $locations);


  /**
   * Check if product is always in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock($variation_id);

  /**
   * Check if product is managed by stock.
   *
   * @todo - not sure if this is needed - not used at this point!!!
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsStockManaged($variation_id);


  /**
   * Get list of locations.
   *
   * @return array
   *   List of locations keyd by ID.
   */
  public function getLocationList($return_active_only = TRUE);

}
