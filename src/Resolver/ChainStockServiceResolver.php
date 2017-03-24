<?php

namespace Drupal\commerce_stock\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

/**
 * Chain stock service resolver.
 */
class ChainStockServiceResolver implements ChainStockServiceResolverInterface {

  /**
   * The resolvers.
   *
   * @var \Drupal\commerce_stock\Resolver\StockServiceResolverInterface[]
   */
  protected $resolvers = [];

  /**
   * Constructs a new ChainStockServiceResolver object.
   *
   * @param \Drupal\commerce_stock\Resolver\StockServiceResolverInterface[] $resolvers
   *   The resolvers.
   */
  public function __construct(array $resolvers = []) {
    $this->resolvers = $resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function addResolver(StockServiceResolverInterface $resolver) {
    $this->resolvers[] = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function getResolvers() {
    return $this->resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, Context $context, $quantity = NULL) {
    foreach ($this->resolvers as $resolver) {
      $result = $resolver->resolve($entity, $context, $quantity);
      if ($result) {
        return $result;
      }
    }
  }

}
