<?php


namespace Drupal\commerce_stock;

use Drupal\commerce\AvailabilityCheckerInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;

/**
 * The Stock Availability checker declared by commerce core.
 */
class StockAvailabilityChecker implements AvailabilityCheckerInterface {

  /**
   * The stock manager service collector.
   *
   * @var \Drupal\commerce_stock\StockManagerInterface
   */
  protected $stockManager;

  /**
   * Constructs a new StockAvailabilityChecker object.
   *
   * @param \Drupal\commerce_stock\StockManagerInterface $stock_manager
   *   The stock manager.
   */
  public function __construct(StockManagerInterface $stock_manager) {
    $this->stockManager = $stock_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(PurchasableEntityInterface $entity) {
    $stock_service = $this->stockManager->getService($entity);
    $stock_checker = $stock_service->getStockChecker();

    // Check if a purchasable entity is a product variation.
    // @todo - should we be using instanceof? dosent work?
    if ($entity instanceof ProductVariationInterface) {

      // Get product variation id.
      $variation_id = $entity->id();

      // Check if stock is managed for the product.
      return $stock_checker->getIsStockManaged($variation_id);
    }
    else {
      // If not a product variation then not applicable.
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function check(PurchasableEntityInterface $entity, $quantity = 1) {
    $stock_service = $this->stockManager->getService($entity);
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
      $stock_level = $stock_checker->getStockLevel($variation_id, $locations);
      return ($stock_level >= $quantity);
    }
    return TRUE;
  }

}
