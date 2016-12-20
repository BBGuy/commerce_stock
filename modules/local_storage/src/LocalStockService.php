<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce_stock\StockServiceInterface;
use Drupal\commerce_stock\StockServiceConfig;

class LocalStockService implements StockServiceInterface {

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $stockChecker;

  /**
   * The stock updater.
   *
   * @var \Drupal\commerce_stock\StockUpdateInterface
   */
  protected $stockUpdater;

  /**
   * The stock configuration.
   *
   * @var \Drupal\commerce_stock\StockServiceConfigInterface
   */
  protected $stockServiceConfig;

  /**
   * Constructor for the service.
   */
  public function __construct() {
    // Create the objects needed.
    $this->stockChecker = new LocalStockStorage();
    $this->stockUpdater = $this->stockChecker;
    $this->stockServiceConfig = new StockServiceConfig($this->stockChecker);
  }

  /**
   * Get the name of the service.
   */
  public function getName() {
    return 'Local stock';
  }

  /**
   * Get the ID of the service.
   */
  public function getId() {
    return 'local_stock';
  }

  /**
   * Gets the stock checker.
   *
   * @return \Drupal\commerce_stock\StockCheckInterface
   *   The stock checker.
   */
  public function getStockChecker() {
    return $this->stockChecker;
  }

  /**
   * Gets the stock updater.
   *
   * @return \Drupal\commerce_stock\StockUpdateInterface
   *   The stock updater.
   */
  public function getStockUpdater() {
    return $this->stockUpdater;
  }

  /**
   * Gets the stock Configuration.
   *
   * @return \Drupal\commerce_stock\StockServiceConfigInterface
   *   The stock Configuration.
   */
  public function getConfiguration() {
    return $this->stockServiceConfig;
  }

}
