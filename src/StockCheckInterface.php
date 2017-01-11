<?php

namespace Drupal\commerce_stock;

interface StockCheckInterface {

  /**
   * Gets the stock level.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   * @param \Drupal\commerce_stock\StockLocationInterface[] $locations
   *  The locations.
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
   * @param \Drupal\commerce_stock\StockLocationInterface[] $locations
   *   The locations to check against.
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

}
