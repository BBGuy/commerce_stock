<?php

namespace Drupal\commerce_local_stock;

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
    $this->entityTypeManager = $entity_type_manager->getStorage('commerce_stock_location');
    $this->storage = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @ToDo Do we really need a primary transaction location. If so users need a way to set this.
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity) {
    return array_shift($this->getEnabledLocations($entity));
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledLocations(PurchasableEntityInterface $entity) {
    return $this->storage->loadEnabled($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getLocations() {
    return $this->storage->loadMultiple();
  }

}
