<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockTransactionsInterface.
 */
namespace Drupal\commerce_stock;

define('TRANSACTION_TYPE_STOCK_IN', 1);
define('TRANSACTION_TYPE_STOCK_OUT', 2);
define('TRANSACTION_TYPE_STOCK_MOVMENT', 3);
define('TRANSACTION_TYPE_SALE', 4);
define('TRANSACTION_TYPE_RETURN', 5);
define('TRANSACTION_TYPE_NEW_STOCK', 6);



/**
 * Defines a common interface for creating stock transactions.
 */
interface StockTransactionsInterface {

  /**
   * Receive stock.
   */
  public function receiveStock($purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $message = NULL);

  /**
   * Sell stock.
   */
  public function sellStock($purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL);

  /**
   * Move stock.
   */
  public function moveStock($purchasable_entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message = NULL);

  /**
   * Stock returns.
   */
  public function returnStock($purchasable_entity, $location_id,  $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL);


}
