<?php

namespace Drupal\commerce_stock_local_test\EventSubscriber;

use Drupal\commerce_stock_local\Event\LocalStockTransactionEvent;
use Drupal\commerce_stock_local\Event\LocalStockTransactionEvents;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Test class to test the commerce_stock transaction events.
 */
class CommerceStockTransactionSubscriber implements EventSubscriberInterface, ContainerInjectionInterface {

  use LoggerChannelTrait;

  /**
   * Constructs a CommerceStockTransactionSubscriber object.
   */
  public function __construct() {}

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    $commerceStockTransactionSubscriber = new static();
    $commerceStockTransactionSubscriber->setLoggerFactory($container->get('logger.factory'));

    return $commerceStockTransactionSubscriber;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [
      LocalStockTransactionEvents::LOCAL_STOCK_TRANSACTION_CREATE => 'onTransactionCreate',
      LocalStockTransactionEvents::LOCAL_STOCK_TRANSACTION_INSERT => 'onTransactionInsert',
    ];
  }

  /**
   * Logging the create event.
   *
   * @param \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent $event
   *   The event.
   */
  public function onTransactionCreate(LocalStockTransactionEvent $event) {
    $this->getLogger('commerce_local_stock_test')->debug('LOCAL_STOCK_TRANSACTION_CREATE issued', $event->getStockTransaction());
  }

  /**
   * Logging the insert event.
   *
   * @param \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent $event
   *   The event.
   */
  public function onTransactionInsert(LocalStockTransactionEvent $event) {
    $this->getLogger('commerce_local_stock_test')->debug('LOCAL_STOCK_TRANSACTION_INSERT issued', $event->getStockTransaction());
  }

}
