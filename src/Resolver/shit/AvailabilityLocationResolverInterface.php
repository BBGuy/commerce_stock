<?php

namespace Drupal\commerce_stock_local\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

/**
 * Defines the interface for availability location resolvers.
 */
interface AvailabilityLocationResolverInterface {

  /**
   * Resolves the availability locations for a purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce\Context $context
   *   The context.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface[]|null
   *   The locations that can be checked for availability, if resolved.
   *   Otherwise NULL, indicating that the next resolver in the chain should be called.
   */
  public function resolve(PurchasableEntityInterface $entity, Context $context);

}
