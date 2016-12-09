<?php

namespace Drupal\commerce_stock_s;

use Drupal\commerce_stock\StockServiceInterface;
use Drupal\commerce_stock\CoreStockConfiguration;

/**
 * A stock service providing local DB storage.
 */
class LocalStockService implements StockServiceInterface {

  /**
   * The stock Checker.
   *
   * @var \Drupal\commerce_stock\StockServiceInterface
   */
  protected $stockChecker;

  /**
   * The stock Updater.
   *
   * @var \Drupal\commerce_stock\StockUpdateInterface
   */
  protected $stockUpdater;

  /**
   * The stock Configuration.
   *
   * @var \Drupal\commerce_stock\StockConfigurationInterface
   */
  protected $stockConfiguration;

  /**
   * Constructor for the service.
   */
  public function __construct() {
    // Create the objects needed.
    $this->stockChecker = new StockStorageAPI();
    $this->stockUpdater = $this->stockChecker;
    $this->stockConfiguration = new CoreStockConfiguration($this->stockChecker);
  }

  /**
   * Get the name of the service.
   */
  public function getName() {
    return 'Local Stock';
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
   *   The stock checkers.
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
   * @return \Drupal\commerce_stock\StockConfigurationInterface
   *   The stock Configuration.
   */
  public function getConfiguration() {
    return $this->stockConfiguration;
  }

}
