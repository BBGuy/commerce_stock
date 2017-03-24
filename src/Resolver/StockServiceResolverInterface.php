<?php

namespace Drupal\commerce_stock\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

/**
 * Defines the interface for stock service resolvers.
 */
interface StockServiceResolverInterface {

  /**
   * Resolves the stock service for a purchasable entity and context.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce\Context $context
   *   The context.
   * @param int $quantity
   *   The quantity.
   *
   * @return \Drupal\commerce_stock\Entity\StockServiceInterface[]|null
   *   The stock service that should be used, if resolved. Otherwise NULL,
   *   indicating that the next resolver in the chain should be called.
   */
  public function resolve(PurchasableEntityInterface $entity, Context $context, $quantity = NULL);

}
