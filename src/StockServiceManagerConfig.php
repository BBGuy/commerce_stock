<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

class StockServiceManagerConfig implements StockServiceManagerConfigInterface {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(StockServiceManagerInterface $stock_service_manager) {
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getService(PurchasableEntityInterface $entity) {
    $services = $this->stockServiceManager->listServices();

    // Get the default service.
    $config = \Drupal::config('commerce_stock.service_manager');
    $default_service_id = $config->get('default_service_id');

    // Cycle all services to see if we got the default service.
    // @todo Service should be determined by configuration of each product type.
    // @todo Get product type -> stock service, not default or first.
    foreach ($services as $service) {
      if ($service->getId() == $default_service_id) {
        return $service;
      }
    }

    // If not found return the first service in the list.
    return $services[0];
  }

}
