<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceConfigInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce\Context;

class LocalStockServiceConfig implements StockServiceConfigInterface {

  /**
   * @var \Drupal\commerce_stock_local\StockLocationStorageInterface
   *   The stock location storage.
   */
  protected $storage;

  /**
   * @var  \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected $entityTypeManager;

  /**
   * Constructs the local stock service config.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->storage = $entity_type_manager->getStorage('commerce_stock_location');
  }

  /**
   * {@inheritdoc}
   *
   */
  public function getTransactionLocation(Context $context, PurchasableEntityInterface $entity, $quantity) {
    // location.
    $locations = $this->getAvailabilityLocations($context, $entity);
    return empty($locations) ? NULL : array_shift($locations);
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailabilityLocations(Context $context, PurchasableEntityInterface $entity) {
    return $this->storage->loadEnabled($entity);
  }

}
