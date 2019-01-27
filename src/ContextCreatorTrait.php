<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;

/**
 * Provides trait to create a commerce context object from a purchasable entity.
 */
trait ContextCreatorTrait {

  /**
   * Returns the active context.
   *
   * This is to support UI calls.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   *
   * @throws \Exception
   *
   * @return \Drupal\commerce\Context
   *   The context containing the customer & store.
   */
  public function getContext(PurchasableEntityInterface $entity) {
    return $this->getContextDetails($entity);
  }

  /**
   * Get context details.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @throws \Exception
   *   When the entity can't be purchased from the current store.
   *
   * @see \Drupal\commerce_cart\Form\AddToCartForm::selectStore()
   *   Original logic comes from this function.
   *
   * @return \Drupal\commerce\Context
   *   The Stock service context.
   */
  private function getContextDetails(PurchasableEntityInterface $entity) {
    // Make sure the current store is in the entity stores.
    $stores = $entity->getStores();
    if (count($stores) === 1) {
      $store = reset($stores);
    }
    elseif (count($stores) === 0) {
      // Malformed entity.
      throw new \Exception('The given entity is not assigned to any store.');
    }
    else {
      /** @var \Drupal\commerce_store\CurrentStore $currentStore */
      $currentStore = \Drupal::service('commerce_store.current_store');
      $store = $currentStore->getStore();
      if (!in_array($store, $stores)) {
        // Indicates that the site listings are not filtered properly.
        throw new \Exception("The given entity can't be purchased from the current store.");
      }
    }

    $currentUser = \Drupal::currentUser();

    return new Context($currentUser, $store);
  }

}
