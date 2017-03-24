<?php

namespace Drupal\commerce_stock\Transaction;

class StockReceive extends StockIn implements StockTransactionInterface {

  protected $transactionTypeId = self::NEW_STOCK;

}
