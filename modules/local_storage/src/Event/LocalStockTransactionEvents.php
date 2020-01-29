<?php

namespace Drupal\commerce_stock_local\Event;

/**
 * List of local stock transaction events.
 */
final class LocalStockTransactionEvents {

  /**
   * Name of the event fired after loading a stock transaction.
   *
   * @Event
   *
   * @see \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent
   */
  const LOCAL_STOCK_TRANSACTION_LOAD = 'commerce_stock_local.stock_transaction.load';

  /**
   * Name of the event fired after creating a new stock transaction.
   *
   * Fired before the stock transaction is saved.
   *
   * @Event
   *
   * @see \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent
   */
  const LOCAL_STOCK_TRANSACTION_CREATE = 'commerce_stock_local.stock_transaction.create';

  /**
   * Name of the event fired after saving a new stock location.
   *
   * @Event
   *
   * @see \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent
   */
  const LOCAL_STOCK_TRANSACTION_INSERT = 'commerce_stock_local.stock_transaction.insert';

  /**
   * Name of the event fired before deleting a stock transaction.
   *
   * @Event
   *
   * @see \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent
   */
  const LOCAL_STOCK_TRANSACTION_PREDELETE = 'commerce_stock_local.stock_transaction.predelete';

  /**
   * Name of the event fired after deleting a stock location.
   *
   * @Event
   *
   * @see \Drupal\commerce_stock_local\Event\LocalStockTransactionEvent
   */
  const LOCAL_STOCK_TRANSACTION_DELETE = 'commerce_stock_local.stock_transaction.delete';

}
