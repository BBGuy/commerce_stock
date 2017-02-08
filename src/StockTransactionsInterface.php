<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

// Core transaction types.
define('TRANSACTION_TYPE_STOCK_IN', 1);
define('TRANSACTION_TYPE_STOCK_OUT', 2);
define('TRANSACTION_TYPE_STOCK_MOVMENT', 3);
// Use case transaction types.
define('TRANSACTION_TYPE_SALE', 4);
define('TRANSACTION_TYPE_RETURN', 5);
define('TRANSACTION_TYPE_NEW_STOCK', 6);
define('TRANSACTION_TYPE_STOCK_MOVMENT_FROM', 7);
define('TRANSACTION_TYPE_STOCK_MOVMENT_TO', 8);

/**
 * Defines a common interface for creating stock transactions.
 */
interface StockTransactionsInterface {

  /**
   * Get the primary location for automatic stock allocation.
   *
   * This is normally a designated location to act as the main warehouse.
   * However this can also be code working out in realtime the location relevant
   * at that time. To help support this we are including the quantity related to
   * the transaction.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   *
   * @return int
   *   The location ID.
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity);

  /**
   * Create a transaction.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   * @param int $location_id
   *   The location ID.
   * @param string $zone
   *   The zone.
   * @param float $quantity
   *   The quantity.
   * @param float $unit_cost
   *   The unit cost.
   * @param int $transaction_type_id
   *   Transaction type ID.
   * @param array $metadata
   *   A metadata array.
   */
  public function createTransaction(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata = []);

  /**
   * Receive stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   * @param int $location_id
   *   The location ID.
   * @param string $zone
   *   The zone.
   * @param float $quantity
   *   The quantity.
   * @param float $unit_cost
   *   The unit cost.
   * @param string $message
   *   The message.
   */
  public function receiveStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $message = NULL);

  /**
   * Sell stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   * @param int $location_id
   *   The location ID.
   * @param string $zone
   *   The zone.
   * @param float $quantity
   *   The quantity.
   * @param float $unit_cost
   *   The unit cost.
   * @param int $order_id
   *   The order ID.
   * @param int $user_id
   *   The user ID.
   * @param string $message
   *   The message.
   */
  public function sellStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL);

  /**
   * Move stock.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   * @param int $from_location_id
   *   The source location ID.
   * @param int $to_location_id
   *   The target location ID.
   * @param string $from_zone
   *   The source zone.
   * @param string $to_zone
   *   The target zone.
   * @param float $quantity
   *   The quantity.
   * @param float $unit_cost
   *   The unit cost.
   * @param string $message
   *   The message.
   */
  public function moveStock(PurchasableEntityInterface $entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message = NULL);

  /**
   * Stock returns.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity (most likely a product variation entity).
   * @param int $location_id
   *   The location ID.
   * @param string $zone
   *   The zone.
   * @param float $quantity
   *   The quantity.
   * @param float $unit_cost
   *   The unit cost.
   * @param int $order_id
   *   The order ID.
   * @param int $user_id
   *   The user ID.
   * @param string $message
   *   The message.
   */
  public function returnStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL);

}
