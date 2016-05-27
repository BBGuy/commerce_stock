<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\CoreStockConfiguration.
 */

namespace Drupal\commerce_stock;

// Core functionality can be overridden or extended by contrib modules.
class CoreStockConfiguration implements StockConfigurationInterface {

  /**
   * @var \Drupal\commerce_stock\StockCheckInterface $stock_checker
   *   The stock checker
   */
  protected $stockChecker;

  /**
   * @yar array
   *   Array of location IDs.
   */
  protected $stockLocations;

  /**
   * Constructs a new CoreStockConfiguration object.
   *
   * @param \Drupal\commerce_stock\StockCheckInterface $stock_checker
   *   The stock checker
   */
  public function __construct(StockCheckInterface $stock_checker) {
    // @todo - we need another object that holds information about the locations
    // that we need to check.
    $this->stockChecker = $stock_checker;
    // Load the configuration
    $this->loadConfiguration();
  }

  public function getLocationList($variation_id) {
    return $this->stockLocations;
  }

  public function loadConfiguration() {
    // For now we will use all active locations for all products.
    $locations = $this->stockChecker->getLocationList(TRUE);
    $this->stockLocations = [];
    foreach ($locations as $key => $value) {
      $this->stockLocations[$key] = $key;
    }
  }

}
