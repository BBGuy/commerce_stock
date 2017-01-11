<?php

namespace Drupal\commerce_stock_local\Event;

use Drupal\commerce_stock_local\Entity\LocalStockLocationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the stock location event.
 *
 * @see \Drupal\commerce_stock_local\Event\LocalStockEvents
 */
class StockLocationEvent extends Event {

  /**
   * The stock location.
   *
   * @var \Drupal\commerce_stock_local\Entity\LocalStockLocationInterface
   */
  protected $stockLocation;

  /**
   * Constructs a new stock location event.
   *
   * @param \Drupal\commerce_stock_local\Entity\LocalStockLocationInterface $stock_location
   */
  public function __construct(LocalStockLocationInterface $stock_location){
    $this->stockLocation = $stock_location;
  }

  /**
   * Gets the stock location.
   *
   * @return \Drupal\commerce_stock_local\Entity\LocalStockLocationInterface
   */
  public function getStockLocation(){
    return $this->stockLocation;
  }

}
