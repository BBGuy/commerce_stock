<?php

namespace Drupal\commerce_stock_local\Resolver;

/**
 * Runs the added resolvers one by one until one of them returns a location.
 *
 * Each resolver in the chain can be another chain, which is why this interface
 * extends the transaction location resolver one.
 */
interface ChainTransactionLocationResolverInterface extends TransactionLocationResolverInterface {

  /**
   * Adds a resolver.
   *
   * @param \Drupal\commerce_stock_local\Resolver\TransactionLocationResolverInterface $resolver
   *   The resolver.
   */
  public function addResolver(TransactionLocationResolverInterface $resolver);

  /**
   * Gets all added resolvers.
   *
   * @return \Drupal\commerce_stock_local\Resolver\TransactionLocationResolverInterface[]
   *   The resolvers.
   */
  public function getResolvers();

}
