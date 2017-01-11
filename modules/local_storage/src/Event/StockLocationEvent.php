<?php

namespace Drupal\commerce_stock_local\Event;

use Symfony\Component\EventDispatcher\Event;
use Drupal\commerce_stock_local\Entity\StockLocationInterface;

/**
 * Defines the stock location event.
 *
 * @see \Drupal\commerce_stock_local\Event\LocalStockEvents
 */
class StockLocationEvent extends Event {

  /**
   * The stock location.
   *
   * @var \Drupal\commerce_stock_local\Entity\StockLocationInterface
   */
  protected $stockLocation;

  /**
   * Constructs a new stock location event.
   *
   * @param \Drupal\commerce_stock_local\Entity\StockLocationInterface $stock_location
   */
  public function __construct(StockLocationInterface $stock_location){
    $this->stockLocation = $stock_location;
  }

  /**
   * Gets the stock location.
   *
   * @return \Drupal\commerce_stock_local\Entity\StockLocationInterface
   */
  public function getStockLocation(){
    return $this->stockLocation;
  }

}
