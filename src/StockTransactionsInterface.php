<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

/**
 * Defines a common interface for creating stock transactions.
 */
interface StockTransactionsInterface {

  const STOCK_IN = 1;
  const STOCK_OUT = 2;
  const STOCK_SALE = 4;
  const STOCK_RETURN = 5;
  const NEW_STOCK = 6;
  const MOVEMENT_FROM = 7;
  const MOVEMENT_TO = 8;

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
   * @param string $currency_code
   *   The currency code.
   * @param int $transaction_type_id
   *   Transaction type ID.
   * @param array $metadata
   *   A metadata array.
   */
  public function createTransaction(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $currency_code, $transaction_type_id, array $metadata = []);

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
   * @param string $currency_code
   *   The currency code.
   * @param string $message
   *   The message.
   */
  public function receiveStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $currency_code, $message = NULL);

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
   * @param string $currency_code
   *   The currency code.
   * @param int $order_id
   *   The order ID.
   * @param int $user_id
   *   The user ID.
   * @param string $message
   *   The message.
   */
  public function sellStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $currency_code, $order_id, $user_id, $message = NULL);

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
   * @param string $currency_code
   *   The currency code.
   * @param string $message
   *   The message.
   */
  public function moveStock(PurchasableEntityInterface $entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $currency_code, $message = NULL);

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
   * @param string $currency_code
   *   The currency code.
   * @param int $order_id
   *   The order ID.
   * @param int $user_id
   *   The user ID.
   * @param string $message
   *   The message.
   */
  public function returnStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $currency_code, $order_id, $user_id, $message = NULL);

}
