<?php

namespace Drupal\commerce_stock;

/**
 * Defines a common interface for configuration.
 */
interface StockConfigurationInterface {


  /**
   * Get the primary location for automatic stock allocation.
   *
   * This is normally a designated location to act as the main warehouse.
   * However this can also be code working out in realtime the location relevant
   * at that time. To help support this we are including the quantity related to
   * the transaction.
   *
   * @param int $variation_id
   *   The product variation ID.
   *
   * @return int.
   *   The location ID.
   */
  public function getPrimaryTransactionLocation($variation_id, $quantity);

  /**
   * Get a list of location relevant for the provided product.
   *
   * The product can be ignored. Any other contextual information like active
   * store/department/.. needs to be managed by the implementing class.
   *
   * @param int $variation_id
   *   The product variation ID.
   *
   * @return array Array of relevant location IDs.
   *   Array of relevant location IDs.
   */
  public function getLocationList($variation_id);

}
