<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockManagerInterface.
 */

namespace Drupal\commerce_stock;


use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceInterface;


/**
 * Defines a common interface for stock checking.
 */
interface StockManagerInterface {

  /**
   * Adds a Stock serice..
   *
   */
  public function addService(StockServiceInterface $stock_service);


  /**
   * Get a serice relevent for the entity.
   *
   */
  public function getService(PurchasableEntityInterface $entity);


  /**
   * Returns an array of all services.
   *
   */
  public function listServices();


}
