<?php

namespace Drupal\commerce_stock_local\Event;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the stock location event.
 */
class LocalStockTransactionEvent extends Event {

  /**
   * The stock transaction.
   *
   * @var array
   *   The local stock transaction.
   */
  protected $stockTransaction;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new stock transaction event.
   *
   * @param object $stock_transaction
   *   The local stock location.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    $stock_transaction,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->stockTransaction = $stock_transaction;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get the stock_location_id for this transaction.
   *
   * @return int
   *    The stock location.
   */
  public function getTransactionLocation() {
    $locationId = $this->stockTransaction['location_id'];
    return $this->entityTypeManager->getStorage('commerce_stock_location')
      ->load($locationId);
  }

  /**
   * Get the purchasable entity.
   *
   * @return  \Drupal\commerce\PurchasableEntityInterface
   *    The purchasable entity.
   */
  public function getEntity() {
    $entityId = $this->stockTransaction['entity_id'];
    $entityType = $this->stockTransaction['entity_type'];
    return $this->entityTypeManager->getStorage($entityType)
      ->load($entityId);
  }

  /**
   * Get the quantity.
   *
   * @return int
   *    The quantity value.
   */
  public function getQuantity() {
    return $this->stockTransaction['qty'];
  }

  /**
   * Get the stock transaction.
   *
   * @return object
   *   The local stock transaction.
   */
  public function getStockTransaction() {
    return $this->stockTransaction;
  }

}
