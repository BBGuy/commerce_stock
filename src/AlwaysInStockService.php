<?php

namespace Drupal\commerce_stock;

/**
 * A stock service for always in stock products.
 */
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
  public function __construct() {
    // Create the objects needed.
    $this->stockChecker = new AlwaysInStock();
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
  public function getId() {
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
