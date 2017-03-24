<?php

namespace Drupal\commerce_stock\Resolver;

/**
 * Runs the added resolvers one by one until one of them returns a service.
 *
 * Each resolver in the chain can be another chain, which is why this interface
 * extends the service resolver one.
 */
interface ChainStockServiceResolverInterface extends StockServiceResolverInterface {

  /**
   * Adds a resolver.
   *
   * @param \Drupal\commerce_stock\Resolver\StockServiceResolverInterface $resolver
   *   The resolver.
   */
  public function addResolver(StockServiceResolverInterface $resolver);

  /**
   * Gets all added resolvers.
   *
   * @return \Drupal\commerce_stock\Resolver\StockServiceResolverInterface[]
   *   The resolvers.
   */
  public function getResolvers();

}
