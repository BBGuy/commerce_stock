<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\AvailabilityCheckerInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce\AvailabilityResponse;
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

    return $stock_checker->getIsStockManaged($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function check(PurchasableEntityInterface $entity, $quantity, Context $context) {
    if (empty($quantity)) {
      $quantity = 1;
    }
    $stock_service = $this->stockServiceManager->getService($entity);
    $stock_checker = $stock_service->getStockChecker();

    if ($stock_checker->getIsAlwaysInStock($entity)) {
      return AvailabilityResponse::available(0, PHP_INT_MAX);
    }

    $stock_config = $stock_service->getConfiguration();
    $stock_level = $stock_checker->getTotalStockLevel(
      $entity,
      $stock_config->getAvailabilityLocations($context, $entity)
    );

    if ($stock_level >= $quantity) {
      return AvailabilityResponse::available(0, $stock_level);
    }
    else {
      return AvailabilityResponse::unavailable(0, $stock_level, 'has Insufficient stock');
    }

  }

}
