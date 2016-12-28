<?php

namespace Drupal\commerce_stock;

interface StockCheckInterface {

  /**
   * Gets the stock level.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   * @param array $locations
   *   Array of locations.
   *
   * @return int
   *   The stock level.
   */
  public function getTotalStockLevel($entity_id, array $locations);

  /**
   * Check if purchasable entity is in stock.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   * @param array $locations
   *   Array of locations.
   *
   * @return bool
   *   TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsInStock($entity_id, array $locations);

  /**
   * Check if purchasable entity is always in stock.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   *
   * @return bool
   *    TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock($entity_id);

  /**
   * Check if purchasable entity is managed by stock.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   *
   * @return bool
   *   TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsStockManaged($entity_id);

  /**
   * Get list of locations.
   *
   * @param bool $return_active_only
   *   Whether or not only return active locations.
   *
   * @return array
   *   List of locations keyed by ID.
   */
  public function getLocationList($return_active_only = TRUE);

}
