<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceConfigInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * @ToDo Do we really need a primary transaction location. If so users need a way to set this.
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity) {
    $locations = $this->getLocationList($entity);
    return empty($locations) ? NULL : array_shift($locations);
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationList(PurchasableEntityInterface $entity) {
    return $this->storage->loadEnabled($entity);
  }

}
