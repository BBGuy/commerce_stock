<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockManagerConfig.
 */


namespace Drupal\commerce_stock;


use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceInterface;
use Drupal\commerce_stock\StockManagerConfigInterface;


class StockManagerConfig implements StockManagerConfigInterface {


  protected $stockManager;

  function __construct(StockManagerInterface $stock_manager) {
    $this->stockManager = $stock_manager;
  }

  /**
   * Get a serice relevent for the entity.
   *
   */
  public function getService(PurchasableEntityInterface $entity) {
    // Get the list of services.
    $services = $this->stockManager->listServices();

    // Get the default service.
    $config = \Drupal::config('commerce_stock.manager');
    $default_service_id = $config->get('default_service_id');


    // Cycle all services to see if we got the default service.
    foreach ($services as $service) {
      if ($service->getID() == $default_service_id) {
        return $service;
      }
    }

    // If not found return the first service in the list.
    return $services[0];
  }



}
