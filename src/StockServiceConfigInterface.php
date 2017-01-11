<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

interface StockServiceConfigInterface {

  /**
   * Get the primary location for automatic stock allocation.
   *
   * This is normally a designated location to act as the main warehouse.
   * However this can also be code working out in realtime the location relevant
   * at that time. To help support this we are including the quantity related to
   * the transaction.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $quantity
   *    The quantity.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface
   *   The stock location.
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity);

  /**
   * Get list of enabled locations for a purchasable entity.
   *
   * Enabled locations are active locations that
   * may have been further filtered by other criteria.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface[]
   *   List of enabled locations.
   */
  public function getEnabledLocations(PurchasableEntityInterface $entity);

  /**
   * Get list of all locations.
   *
   * Note: The returned locations are not checked against
   * the status or filtered in any way. The caller needs to apply
   * its own logic.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface[]
   *   List of locations.
   */
  public function getLocations();

}
