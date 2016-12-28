<?php

namespace Drupal\commerce_stock;

class StockServiceConfig implements StockServiceConfigInterface {

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $stockChecker;

  /**
   * A list of stock locations.
   *
   * @var array
   */
  protected $stockLocations;

  /**
   * Constructs a new StockServiceConfig object.
   *
   * @param \Drupal\commerce_stock\StockCheckInterface $stock_checker
   *   The stock checker.
   */
  public function __construct(StockCheckInterface $stock_checker) {
    // @todo - we need another object that holds information about the locations
    // that we need to check.
    $this->stockChecker = $stock_checker;
    // Load the configuration.
    $this->loadConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationList($entity_id) {
    return $this->stockLocations;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryTransactionLocation($entity_id, $quantity) {
    $locations = $this->getLocationList($entity_id);
    // @todo - we need a better way of managing this.
    return array_shift($locations);
  }

  /**
   * Load the configuration.
   */
  public function loadConfiguration() {
    // For now we will use all active locations for all products.
    $locations = $this->stockChecker->getLocationList(TRUE);
    $this->stockLocations = [];
    foreach ($locations as $key => $value) {
      $this->stockLocations[$key] = $key;
    }
  }

}
