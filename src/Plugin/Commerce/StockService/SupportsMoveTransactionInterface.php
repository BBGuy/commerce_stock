<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockService;

use Drupal\commerce\PurchasableEntityInterface;

interface SupportsMoveTransactionInterface {

  public function moveStock(PurchasableEntityInterface $entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message = NULL);

}
