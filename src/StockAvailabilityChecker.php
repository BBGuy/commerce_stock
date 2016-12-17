<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\AvailabilityCheckerInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce\Context;
use Drupal\commerce_product\Entity\ProductVariationInterface;

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

    // Check if a purchasable entity is a product variation.
    // @todo - should we be using instanceof? dosent work?
    if ($entity instanceof ProductVariationInterface) {
      $variation_id = $entity->id();
      return $stock_checker->getIsStockManaged($variation_id);
    }
    else {
      return FALSE;
    }
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
    $stock_config = $stock_service->getConfiguration();

    // Get product variation id.
    // @todo - validation of $entity type.
    $variation_id = $entity->id();

    // Get locations.
    $locations = $stock_config->getLocationList($variation_id);

    // Check if always in stock.
    if (!$stock_checker->getIsAlwaysInStock($variation_id)) {
      // Check if quantity is available.
      $stock_level = $stock_checker->getTotalStockLevel($variation_id, $locations);
      return ($stock_level >= $quantity);
    }
    return TRUE;
  }

}
