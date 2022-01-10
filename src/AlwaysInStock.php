<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * The Checker and updater implementation for the always in stock service.
 */
class AlwaysInStock implements StockCheckInterface, StockUpdateInterface {

  /**
   * {@inheritdoc}
   */
  public function createTransaction(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $currency_code, $transaction_type_id, array $metadata) {
    // Do nothing and return a NULL value as its N/A.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalStockLevel(
    PurchasableEntityInterface $entity,
    array $locations
  ) {
    return PHP_INT_MAX;
  }


  /**
   * {@inheritdoc}
   */
  public function getTotalAvailableStockLevel(
    PurchasableEntityInterface $entity,
    array $locations
  ) {
    return PHP_INT_MAX;
  }


  /**
   * {@inheritdoc}
   */
  public function getIsInStock(PurchasableEntityInterface $entity, array $locations) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsAlwaysInStock(PurchasableEntityInterface $entity) {
    return TRUE;
  }

}
