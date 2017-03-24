<?php

namespace Drupal\commerce_stock_local\Resolver;

/**
 * Runs the added resolvers one by one until one of them returns a list of locations.
 *
 * Each resolver in the chain can be another chain, which is why this interface
 * extends the availability location resolver one.
 */
interface ChainAvailabilityLocationResolverInterface extends AvailabilityLocationResolverInterface {

  /**
   * Adds a resolver.
   *
   * @param \Drupal\commerce_stock_local\Resolver\AvailabilityLocationResolverInterface $resolver
   *   The resolver.
   */
  public function addResolver(AvailabilityLocationResolverInterface $resolver);

  /**
   * Gets all added resolvers.
   *
   * @return \Drupal\commerce_stock_local\Resolver\AvailabilityLocationResolverInterface[]
   *   The resolvers.
   */
  public function getResolvers();

}
