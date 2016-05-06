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
   * {@inheritdoc}
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata) {
    // Do nothing.
  }


  /**
   * {@inheritdoc}
   */
  public function getStockLevel($variation_id, $locations) {
    // @todo this can be configurable?
    return 999;
  }


  /**
   * {@inheritdoc}
   */
  public function getIsInStock($variation_id, $locations) {
    return TRUE;
  }


  /**
   * {@inheritdoc}
   */
  public function getIsAlwaysInStock($variation_id) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsStockManaged($variation_id) {
    // @todo - Not sure about this one. The result will be the same for:
    // TRUE - managed by this and will always be availalbe.
    // FALSE - not managed so will be available.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationList($return_active_only = TRUE) {
    // We dont have locations so return an empty array.
    return array();
  }

}
