<?php

namespace Drupal\commerce_stock_local\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Returns the first active local stock locations, if known.
 */
class DefaultTransactionLocationResolver implements TransactionLocationResolverInterface {

  /**
   * The local stock location storage.
   *
   * @var \Drupal\commerce_stock_local\StockLocationStorageInterface
   */
  protected $storage;

  /**
   * The local stock location availability location resolver.
   *
   * @var \Drupal\commerce_stock_local\Resolver\AvailabilityLocationResolverInterface
   */
  protected $availabilityLocationResolver;

  /**
   * Constructs a new DefaultTransactionLocationResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @param \Drupal\commerce_stock_local\Resolver\AvailabilityLocationResolverInterface $availability_location_resolver
   *   The availability location resolver.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AvailabilityLocationResolverInterface $availability_location_resolver) {
    $this->storage = $entity_type_manager->getStorage('commerce_stock_location');
    $this->availabilityLocationResolver = $availability_location_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, Context $context, $quantity) {
    // Return the location specified as the transaction location for the store.
    $store = $context->getStore();
    if ($store_location = $store->get('commerce_stock_location_transaction_location')) {
      $location = $this->storage->load($store_location->first()->getValue());
      return $location;
    }
    // Or return the first active availability location.
    $locations = $this->availabilityLocationResolver->resolve($entity, $context);

    return array_shift($locations);
  }

}
