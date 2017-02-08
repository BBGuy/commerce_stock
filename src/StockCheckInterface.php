<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

interface StockCheckInterface {

  /**
   * Gets the stock level.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param array $locations
   *   Array of locations.
   *
   * @return int
   *   The stock level.
   */
  public function getTotalStockLevel(PurchasableEntityInterface $entity, array $locations);

  /**
   * Check if purchasable entity is in stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param array $locations
   *   Array of locations.
   *
   * @return bool
   *   TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsInStock(PurchasableEntityInterface $entity, array $locations);

  /**
   * Check if purchasable entity is always in stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return bool
   *    TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock(PurchasableEntityInterface $entity);

  /**
   * Check if purchasable entity is managed by stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return bool
   *   TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsStockManaged(PurchasableEntityInterface $entity);

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
