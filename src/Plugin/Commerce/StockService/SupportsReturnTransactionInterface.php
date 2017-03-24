<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockService;

use Drupal\commerce\PurchasableEntityInterface;

interface SupportsReturnTransactionInterface {

  public function returnStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL);

}
