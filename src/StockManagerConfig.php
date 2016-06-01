<?php


namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * Configuration class for the stock manager.
 */
class StockManagerConfig implements StockManagerConfigInterface {

  /**
   * The stock manager.
   *
   * @var \Drupal\commerce_stock\StockManagerInterface
   */
  protected $stockManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(StockManagerInterface $stock_manager) {
    $this->stockManager = $stock_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getService(PurchasableEntityInterface $entity) {
    // Get the list of services.
    $services = $this->stockManager->listServices();

    // Get the default service.
    $config = \Drupal::config('commerce_stock.manager');
    $default_service_id = $config->get('default_service_id');

    // Cycle all services to see if we got the default service.
    foreach ($services as $service) {
      if ($service->getId() == $default_service_id) {
        return $service;
      }
    }

    // If not found return the first service in the list.
    return $services[0];
  }

}
