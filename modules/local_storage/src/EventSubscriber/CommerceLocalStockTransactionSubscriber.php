<?php

namespace Drupal\commerce_stock_local\EventSubscriber;

use Drupal\commerce_stock_local\Event\LocalStockTransactionEvent;
use Drupal\commerce_stock_local\Event\LocalStockTransactionEvents;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Test class to test the commerce_stock transaction events.
 */
class CommerceLocalStockTransactionSubscriber implements EventSubscriberInterface {

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a CommerceStockTransactionSubscriber.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(
    CacheTagsInvalidatorInterface $cache_tags_invalidator
  ) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [
      LocalStockTransactionEvents::LOCAL_STOCK_TRANSACTION_INSERT => 'onTransactionInsert',
    ];
  }

  /**
   * Invalidate the cache for the purchased entity.
   *
   * @param \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent $event
   *   The event.
   */
  public function onTransactionInsert(LocalStockTransactionEvent $event) {
    $purchasableEntity = $event->getEntity();
    $this->cacheTagsInvalidator->invalidateTags($purchasableEntity->getCacheTagsToInvalidate());
  }

}
