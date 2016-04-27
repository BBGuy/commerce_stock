<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockServiceInterface.
 */

namespace Drupal\commerce_stock;

use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockUpdateInterface;



/**
 * Defines a common interface for stock checking.
 */
interface StockServiceInterface {

  /**
   * Get the name of the service
   */
  public function getName();

  /**
   * Get the id of the service
   */
  public function getID();
  /**
   * Gets the stock checker.
   *
   * @return \Drupal\commerce_stock\StockCheckInterface
   *   The stock checkers.
   */
  public function getStockChecker();

  /**
   * Gets the stock updater.
   *
   * @return \Drupal\commerce_stock\StockUpdateInterface
   *   The stock updater.
   */
  public function getStockUpdater();


  /**
   * Gets the stock Configuration.
   *
   * @return \Drupal\commerce_stock\StockConfigurationInterface
   *   The stock Configuration.
   */
  public function getConfiguration();

}
