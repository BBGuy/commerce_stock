<?php

namespace Drupal\commerce_stock_local\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

/**
 * Chain transaction location resolver.
 */
class ChainTransactionLocationResolver implements ChainTransactionLocationResolverInterface {

  /**
   * The resolvers.
   *
   * @var \Drupal\commerce_stock_local\Resolver\TransactionLocationResolverInterface[]
   */
  protected $resolvers = [];

  /**
   * Constructs a new ChainTransactionLocationResolver object.
   *
   * @param \Drupal\commerce_stock_local\Resolver\TransactionLocationResolverInterface[] $resolvers
   *   The resolvers.
   */
  public function __construct(array $resolvers = []) {
    $this->resolvers = $resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function addResolver(TransactionLocationResolverInterface $resolver) {
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
  public function resolve(PurchasableEntityInterface $entity, Context $context, $quantity) {
    foreach ($this->resolvers as $resolver) {
      $result = $resolver->resolve($entity, $context, $quantity);
      if ($result) {
        return $result;
      }
    }
  }

}
