<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockConfigurationInterface.
 */

namespace Drupal\commerce_stock;

/**
 * Defines a common interface for configuration.
 */
interface StockConfigurationInterface {


  /**
   * Get a list of location relevent for the provided product.
   *
   * The product can be ignored. Any other contextual information like active
   * store/department/.. needs to be managed by the implementing class.
   *
   * @return array
   *   Of location IDs.
   */
  public function getLocationList($variation_id);

}
