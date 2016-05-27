<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockManagerConfigInterface.
 */

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * Defines a common interface for stock checking.
 */
interface StockManagerConfigInterface {

  /**
   * Constructor to point back at manager.
   * @todo It's bad practice to define a specific constructor in an interface.
   *       If we want to enforce this relationship, we should find a better way!
   *
   * @param \Drupal\commerce_stock\StockManagerInterface $stock_manager
   *   The stock manager.
   */
  function __construct(StockManagerInterface $stock_manager);

  /**
   * Get a service relevant for the entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   * @return \Drupal\commerce_stock\StockServiceInterface
   *   The appropriate stock service for the given purchasable entity.
   */
  public function getService(PurchasableEntityInterface $entity);

}
