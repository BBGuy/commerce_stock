<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * The stock checker interface.
 */
interface StockCheckInterface {

  /**
   * Gets the stock level.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce_stock\StockLocationInterface[] $locations
   *   The locations.
   *
   * @return int
   *   The stock level.
   */
  public function getTotalStockLevel(PurchasableEntityInterface $entity, array $locations);


  /**
   * Gets the available to buy stock level.
   *
   * This allows for the separation of the actual stock level and the quantity
   * available for a user to porches.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce_stock\StockLocationInterface[] $locations
   *   The locations.
   *
   * @return int
   *   The stock level available for purchase.
   */
  public function getTotalAvailableStockLevel(PurchasableEntityInterface $entity, array $locations);

  /**
   * Check if purchasable entity is in stock.
   *
   * @deprecated in Drupal  8.x-1.0-rc1, will be removed at a later stage.
   *   Use getTotalAvailableStockLevel() > 0.
   *
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce_stock\StockLocationInterface[] $locations
   *   The locations to check against.
   *
   * @return bool
   *   TRUE if the entity is in stock, FALSE otherwise.
   */
  public function getIsInStock(PurchasableEntityInterface $entity, array $locations);

  /**
   * Check if entity is always in stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return bool
   *   TRUE if the entity is always in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock(PurchasableEntityInterface $entity);

}
