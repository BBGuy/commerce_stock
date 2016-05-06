<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockTransactionsInterface.
 */

namespace Drupal\commerce_stock;

/**
 * Defines a common interface for creating stock transactions.
 */
interface StockTransactionsInterface {

  /**
   * Receive stock.
   */
  public function receiveStock($variation_id, $location_id, $zone, $quantity, $unit_cost);

  /**
   * Sell stock.
   */
  public function sellStock($variation_id, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id);

  /**
   * Move stock.
   */
  public function moveStock($variation_id, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost);

  /**
   * Stock returns.
   */
  public function returnStock($variation_id, $location_id,  $zone, $quantity, $unit_cost, $order_id, $user_id);


}
