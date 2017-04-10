<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * The stock service configuration interface.
 */
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
   *   The quantity.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface
   *   The stock location.
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity);

  /**
   * Get locations relevant for the provided purchasable entity.
   *
   * The entity can be ignored. Any other contextual information like active
   * store/department/.. needs to be managed by the implementing class.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface[]
   *   List of relevant locations.
   */
  public function getLocationList(PurchasableEntityInterface $entity);

}
