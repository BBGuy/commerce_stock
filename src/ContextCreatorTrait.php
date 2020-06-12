<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides trait to create a commerce context object from a purchasable entity.
 */
trait ContextCreatorTrait {

  /**
   * Returns the active commerce context.
   *
   * This is to support UI calls.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   *
   * @return \Drupal\commerce\Context
   *   The context containing the customer & store.
   *
   * @throws \Exception
   */
  public function getContext(PurchasableEntityInterface $entity) {
    return $this->getContextDetails($entity);
  }

  /**
   * Checks that the context returned is valid for $entity.
   *
   * This is to support UI calls.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   *
   * @return bool
   *   TRUE if valid, FALSE if not.
   */
  public function isValidContext(PurchasableEntityInterface $entity) {
    try {
      $this->getContextDetails($entity);
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get context details.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return \Drupal\commerce\Context
   *   The Stock service context.
   *
   * @throws \Exception
   *   When the entity can't be purchased from the current store.
   *
   * @see \Drupal\commerce_cart\Form\AddToCartForm::selectStore()
   *   Original logic comes from this function.
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
      foreach ($stores as $store) {
        $store_ids[] = $store->id();
      }
      /** @var \Drupal\commerce_store\CurrentStore $currentStore */
      $currentStore = \Drupal::service('commerce_store.current_store');
      $store = $currentStore->getStore();
      if (!in_array($store->id(), $store_ids)) {
        // Indicates that the site listings are not filtered properly.
        throw new \Exception("The given entity can't be purchased from the current store.");
      }
    }

    $currentUser = \Drupal::currentUser();

    return new Context($currentUser, $store);
  }

  /**
   * Creates a commerce context object.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The commerce order obejct.
   * @param int|null $time
   *   The unix timestamp, or NULL to use the current time.
   * @param array $data
   *   The data.
   *
   * @return \Drupal\commerce\Context
   *   The context.
   */
  public static function createContextFromOrder(OrderInterface $order, $time = NULL, array $data = []) {
    return new Context($order->getCustomer(), $order->getStore(), $time, $data);
  }

}
