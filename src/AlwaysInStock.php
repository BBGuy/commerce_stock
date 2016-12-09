<?php

namespace Drupal\commerce_stock;

/**
 * The API class used by the always in stock service.
 */
class AlwaysInStock implements StockCheckInterface, StockUpdateInterface {

  /**
   * {@inheritdoc}
   */
  public function createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata) {
    // Do nothing and return a NULL value as its N/A.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalStockLevel($variation_id, array $locations) {
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
    // TRUE - managed by this and will always be available.
    // FALSE - not managed so will be available.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationList($return_active_only = TRUE) {
    // We don't have locations, so return an empty array.
    return [];
  }

}
