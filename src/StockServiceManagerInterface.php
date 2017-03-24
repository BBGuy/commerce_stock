<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

interface StockServiceManagerInterface {

  /**
   * Checks if a purchasable entity is stock managed.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return bool TRUE if stock is managed, FALSE if not.
   */
  public function isStockManaged(PurchasableEntityInterface $entity);

  /**
   * Gets the resolved service for a given entity, context and quantity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce\Context $context
   *   The context.
   * @param int $quantity
   *   The quantity.
   *
   * @return \Drupal\commerce_stock\Entity\StockServiceInterface|null
   *   The stock service if resolved, or NULL if none available.
   */
  public function getService(PurchasableEntityInterface $entity, Context $context, $quantity);

}
