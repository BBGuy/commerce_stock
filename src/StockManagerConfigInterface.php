<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockManagerConfigInterface.
 */

namespace Drupal\commerce_stock;


use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceInterface;
use Drupal\commerce_stock\StockManagerInterface;

/**
 * Defines a common interface for stock checking.
 */
interface StockManagerConfigInterface {


  /**
   * Constructor to point back at manager.
   *
   */
  function __construct(StockManagerInterface $stock_manager);

  /**
   * Get a serice relevent for the entity.
   *
   */
  public function getService(PurchasableEntityInterface $entity);


}
