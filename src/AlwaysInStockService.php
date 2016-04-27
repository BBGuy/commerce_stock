<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\AlwaysInStockService.
 */


namespace Drupal\commerce_stock;


use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockUpdateInterface;
use Drupal\commerce_stock\StockConfigurationInterface;
use Drupal\commerce_stock\AlwaysInStock;
use Drupal\commerce_stock\CoreStockConfiguration;


class AlwaysInStockService implements StockServiceInterface {


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



   function __construct() {
     // Create the objects needed.
     $this->stockChecker = new AlwaysInStock;
     $this->stockUpdater =  $this->stockChecker;
     $this->stockConfiguration = new CoreStockConfiguration($this->stockChecker);
   }

  /**
   * Get the name of the service
   */
  public function getName() {
    return 'Always In Stock';
  }

  public function getID() {
    return 'always_in_stock';
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
