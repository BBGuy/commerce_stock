<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\AvailabilityCheckerInterface;
use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

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
  public function __construct(
    StockServiceManagerInterface $stock_service_manager
  ) {
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
   *
   * We don't do anything here. The AvailibilityCheckerInterface allows only
   * TRUE/FALSE as answer. This isn't enough for sophisticated use cases.
   *
   * See the commerce_stock.module for certain inception points.
   *
   * @see https://www.drupal.org/project/commerce/issues/2710107
   * @see https://www.drupal.org/project/commerce/issues/2937041
   */
  public function check(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  ) {
    return TRUE;
  }

}
