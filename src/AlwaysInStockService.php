<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\AlwaysInStockService.
 */

namespace Drupal\commerce_stock;

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

  /**
   * Constructs a new AlwaysInStockService object.
   */
  function __construct() {
    // Create the objects needed.
    $this->stockChecker = new AlwaysInStock;
    $this->stockUpdater = $this->stockChecker;
    $this->stockConfiguration = new CoreStockConfiguration($this->stockChecker);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Always In Stock';
  }

  /**
   * {@inheritdoc}
   */
  public function getID() {
    return 'always_in_stock';
  }

  /**
   * {@inheritdoc}
   */
  public function getStockChecker() {
    return $this->stockChecker;
  }

  /**
   * {@inheritdoc}
   */
  public function getStockUpdater() {
    return $this->stockUpdater;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->stockConfiguration;
  }
}
