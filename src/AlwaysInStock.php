<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

class AlwaysInStock implements StockCheckInterface, StockUpdateInterface, StockServiceConfigInterface {

  /**
   * {@inheritdoc}
   */
  public function createTransaction($entity_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata) {
    // Do nothing and return a NULL value as its N/A.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalStockLevel($entity_id, array $locations) {
    // @todo this can be configurable?
    return 999;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsInStock($entity_id, array $locations) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsAlwaysInStock($entity_id) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsStockManaged($entity_id) {
    // @todo - Not sure about this one. The result will be the same for:
    // TRUE - managed by this and will always be available.
    // FALSE - not managed so will be available.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledLocations(PurchasableEntityInterface $entity) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLocations() {
    return [];
  }

}
