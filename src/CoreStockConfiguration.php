<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\CoreStockConfiguration.
 */


namespace Drupal\commerce_stock;


use Drupal\commerce_stock\StockConfigurationInterface;
use Drupal\commerce_stock\StockCheckInterface;



// Core functionality can be overridden or extended by contrib modules.
class CoreStockConfiguration implements StockConfigurationInterface {


  public function getLocationList($variation_id) {
    return $this->StockLocations;

  }

  /**
   * The Stock checker object.
   */
  protected $StockChecker;

  /**
   * Array of location IDs.
   */
  protected $StockLocations;


  /**
   * Constructor.
   *
   */
  public function __construct(StockCheckInterface $StockChecker) {
    // @todo - we need another object that holds information about the locations
    // that we need to check.
    $this->StockChecker = $StockChecker;
    // Load the configuration
    $this->loadConfiguration();
  }

  public function loadConfiguration() {
    // For now we will use all active locations for all products.
    $locations = $this->StockChecker->getLocationList(TRUE);
    $this->StockLocations = array();
    foreach ($locations as $key => $value) {
      $this->StockLocations[$key] = $key;
    }
  }

}
