<?php

/**
 * @file
 * Contains \Drupal\commerce_stock_s\Entity\StockStorageAPI.
 */


namespace Drupal\commerce_stock_s\Entity;


use Drupal\commerce_stock\Entity\EntityStockCheckInterface;
use Drupal\commerce_stock\Entity\EntityStockUpdateInterface;



class StockStorageAPI implements EntityStockCheckInterface, EntityStockUpdateInterface {

  /**
   * check if product is in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function createTransaction($product_id, $location_id, $zone, $qry, $cost) {

  }

  /**
   * Gets the Stock level.
   *
   * @return int
   *   Stock Level.
   */
  public function getStockLevel($product_id, $locations) {
    // @todo - just testing
    return 10;
  }


  /**
   * check if product is in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsInStock($product_id, $locations) {
    // @todo - just testing
    return TRUE;
  }


  /**
   * Check if product is always in stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock($product_id) {
    // @todo - just testing
    return TRUE;
  }

  /**
   * Check if product is managed by stock.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsStockManaged($product_id) {
    // @todo - just testing
    return TRUE;
  }


  /**
   * Get list of locations.
   *
   * @return array
   *   List of locations keyd by ID.
   */
  public function getLocationList() {

  }
}
