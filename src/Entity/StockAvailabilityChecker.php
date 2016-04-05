<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\Entity\StockAvailabilityChecker.
 */


namespace Drupal\commerce_stock\Entity;


use Drupal\commerce\AvailabilityCheckerInterface;
use Drupal\commerce_stock\Entity\EntityStockCheckInterface;
use Drupal\commerce\PurchasableEntityInterface;




class StockAvailabilityChecker implements AvailabilityCheckerInterface {

  /**
   * The Stock checker object.
   *
   */
  protected $StockChecker;

  protected $StockConfiguration;




  /**
   * Constructor.
   *
   */
  public function __construct(EntityStockCheckInterface $StockChecker, EntityStockConfigurationInterface $configuration) {
    // @todo - we need another object that holds information about the locations
    // that we need to check.
    $this->StockChecker = $StockChecker;
    $this->StockConfiguration = $configuration;
  }

  /**
   * Determines whether the checker applies to the given purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return bool
   *   TRUE if the checker applies to the given purchasable entity, FALSE
   *   otherwise.
   */
  public function applies(PurchasableEntityInterface $entity) {
    // @todo - validation of $entity type.
    // Get product id.
    $variation_id  = $entity->id();
    // Check if stock enabled for the product
    return $StockChecker->getIsStockManaged($variation_id);
  }

  /**
   * Checks the availability of the given purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $quantity
   *   The quantity.
   *
   * @return bool|null
   *   TRUE if the entity is available, FALSE if it's unavailable,
   *   or NULL if it has no opinion.
   */
  public function check(PurchasableEntityInterface $entity, $quantity = 1) {
    // @todo - validation of $entity type.
    // Get product id.
    $variation_id  = $entity->id();
    // Get locations.
    //$locations = array_keys($this->StockConfiguration->getLocationList($variation_id));
    $locations = $this->StockConfiguration->getLocationList($variation_id);
    // Check if always in stock.
    if (!$this->StockChecker->getIsAlwaysInStock($variation_id)) {
      // Check quantity is available
      $stock_level = $this->StockChecker->getStockLevel($variation_id, $locations);
      return ($stock_level >= $quantity);
    }

    return TRUE;

  }
}
