<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

interface StockServiceManagerConfigInterface {

  /**
   * Constructor to point back at manager.
   *
   * @todo It's bad practice to define a specific constructor in an interface.
   *       If we want to enforce this relationship, we should find a better way!
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   */
  public function __construct(StockServiceManagerInterface $stock_service_manager);

  /**
   * Get a service relevant for the entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   *
   * @return \Drupal\commerce_stock\StockServiceInterface
   *   The appropriate stock service for the given purchasable entity.
   */
  public function getService(PurchasableEntityInterface $entity);

}
