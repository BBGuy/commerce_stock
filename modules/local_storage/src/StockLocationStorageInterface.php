<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * Defines the interface for local stock location storage.
 */
interface StockLocationStorageInterface {

  /**
   * Loads the enabled variations for the given product.
   *
   * Enabled variations are active stock locations that have
   * been filtered through the FILTER_STOCK_LOCATIONS event.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return \Drupal\commerce_stock_local\Entity\StockLocation[]
   *   The enabled stock locations.
   */
  public function loadEnabled(PurchasableEntityInterface $entity);

}
