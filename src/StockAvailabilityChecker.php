<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\AvailabilityCheckerInterface;
use Drupal\commerce\AvailabilityResponse\AvailabilityResponse;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce\Context;

/**
 * The entry point for availability checking through Commerce Stock.
 *
 * Proxies requests to stock services configured for each entity.
 *
 * @package Drupal\commerce_stock
 */
class StockAvailabilityChecker implements AvailabilityCheckerInterface {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * Constructs a new StockAvailabilityChecker object.
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   */
  public function __construct(StockServiceManagerInterface $stock_service_manager) {
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(PurchasableEntityInterface $entity) {
    $stock_service = $this->stockServiceManager->getService($entity);
    $stock_checker = $stock_service->getStockChecker();

    return $stock_checker->getIsStockManaged($entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function check(PurchasableEntityInterface $entity, $quantity, Context $context) {
    $stock_service = $this->stockServiceManager->getService($entity);
    $stock_checker = $stock_service->getStockChecker();
    $stock_config = $stock_service->getConfiguration();
    $entity_id = $entity->id();
    $locations = $stock_config->getLocationList($entity_id);

    if ($stock_checker->getIsAlwaysInStock($entity_id)) {
      return AvailabilityResponse::available(0, $quantity);
    }

    $stock_level = $stock_checker->getTotalStockLevel($entity_id, $locations);
    // @todo Minimum qty instead of 0?

    if ($stock_level > $quantity) {
      return AvailabilityResponse::available(0, $stock_level);
    }
    else {
      return AvailabilityResponse::unavailable(0, $stock_level, 'maximum exceeded');
    }
  }

}
