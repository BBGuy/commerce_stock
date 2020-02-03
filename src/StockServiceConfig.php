<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce\Context;

/**
 * The default stock service configuration class. This class does nothing
 * meaningful and act as a stub to fullfil the StockServiceInterface for the
 * AlwaysInStockService.
 *
 * For a more meaningful example see the LocalStockServiceConfig
 * class in the local_storage submodule.
 */
class StockServiceConfig implements StockServiceConfigInterface {

  /**
   * A list of stock locations.
   *
   * @var array
   */
  protected $stockLocations = [];

  /**
   * {@inheritdoc}
   */
  public function getAvailabilityLocations(Context $context, PurchasableEntityInterface $entity) {
    return $this->stockLocations;
  }

  /**
   * {@inheritdoc}
   */
  public function getTransactionLocation(Context $context, PurchasableEntityInterface $entity, $quantity) {
    $locations = $this->getAvailabilityLocations($context, $entity);
    return array_shift($locations);
  }

}
