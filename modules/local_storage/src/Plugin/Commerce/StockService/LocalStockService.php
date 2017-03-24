<?php

namespace Drupal\commerce_stock_local\Plugin\Commerce\StockService;

use Drupal\commerce_stock\Plugin\Commerce\StockService\StockServiceBase;
use Drupal\commerce_stock\Plugin\Commerce\StockService\SupportsSellTransactionInterface as SellSupport;
use Drupal\commerce_stock\Plugin\Commerce\StockService\SupportsReceiveTransactionInterface as ReceiveSupport;
use Drupal\commerce_stock\Plugin\Commerce\StockService\SupportsMoveTransactionInterface as MoveSupport;
use Drupal\commerce_stock\Plugin\Commerce\StockService\SupportsReturnTransactionInterface as ReturnSupport;

class LocalStockService extends StockServiceBase implements ReceiveSupport, SellSupport, MoveSupport, ReturnSupport {



}