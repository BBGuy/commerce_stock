<?php

namespace Drupal\commerce_stock_local\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

/**
 * Defines the interface for transaction location resolvers.
 */
interface TransactionLocationResolverInterface {

  /**
   * Resolves the transaction location for a purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce\Context $context
   *   The context.
   * @param int $quantity
   *   The quantity.
   *
   * @return \Drupal\commerce_stock\StockLocationInterface|null
   *   The location that should be used for a stock transaction, if resolved.
   *   Otherwise NULL, indicating that the next resolver in the chain should be called.
   */
  public function resolve(PurchasableEntityInterface $entity, Context $context, $quantity);

}
