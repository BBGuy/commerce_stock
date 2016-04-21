<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockManager.
 */


namespace Drupal\commerce_stock;


use Drupal\commerce_stock\StockManagerInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceInterface;


class StockManager implements StockManagerInterface {


  /**
   * The stock services.
   *
   * @var \Drupal\commerce_stock\StockServiceInterface[]
   */
  protected $stockServices = [];

  /**
   * {@inheritdoc}
   */
  public function addService(StockServiceInterface $stock_service) {
    $this->stockServices[] = $stock_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getService(PurchasableEntityInterface $entity) {
    // @todo - testing only return the first one
    return $this->stockServices[0];
  }

  /**
   * Returns an array of all services.
   *
   */
  public function listServices() {
    return $this->stockServices;
  }



}
