<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock_local\Event\FilterLocationsEvent;
use Drupal\commerce_stock_local\Event\LocalStockEvents;

/**
 * Defines the product variation storage.
 */
class StockLocationStorage extends CommerceContentEntityStorage implements StockLocationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadEnabled(PurchasableEntityInterface $entity) {

    // Speed up loading by filtering out the IDs of disabled locations.
    $query = $this->getQuery()
      ->condition('status', TRUE);
    $result = $query->execute();
    if (empty($result)) {
      return [];
    }
    $enabled_locations = $this->loadMultiple($result);

    // Allow modules to apply own filtering.
    $event = new FilterLocationsEvent($entity, $enabled_locations);
    $this->eventDispatcher->dispatch(LocalStockEvents::FILTER_STOCK_LOCATIONS, $event);
    $enabled_locations = $event->getLocations();

    return $enabled_locations;
  }


}
