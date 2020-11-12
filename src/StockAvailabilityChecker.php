<?php

namespace Drupal\commerce_stock;

use Drupal\commerce_order\AvailabilityCheckerInterface;
use Drupal\commerce\Context;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_order\AvailabilityResult;

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
  public function applies(OrderItemInterface $order_item) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function check(
      OrderItemInterface $order_item,
      Context $context
  ) {
    return (new AvailabilityResult(TRUE))->isNeutral();
  }

}
