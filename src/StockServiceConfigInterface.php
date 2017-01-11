<?php

namespace Drupal\commerce_stock;

interface StockServiceConfigInterface {

  /**
   * Get the primary location for automatic stock allocation.
   *
   * This is normally a designated location to act as the main warehouse.
   * However this can also be code working out in realtime the location relevant
   * at that time. To help support this we are including the quantity related to
   * the transaction.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   * @param int $quantity
   *    The quantity.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface
   *   The stock location.
   */
  public function getPrimaryTransactionLocation($entity_id, $quantity);

  /**
   * Get list of enabled locations for a purchasable entity.
   *
   * Enabled locations are active locations that
   * may have been further filtered by other criteria.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface[]
   *   List of enabled locations.
   */
  public function getEnabledLocations($entity_id);


  /**
   * Get list of all locations.
   *
   * Note: The returned locations are not checked against
   * the status or filtered in any way. The caller needs to check
   * the status itself.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface[]
   *   List of locations.
   */
  public function getLocations($entity_id);

}
