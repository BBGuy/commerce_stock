<?php

namespace Drupal\field_stock;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for processing stock.
 */
class StockFieldProcessing extends TypedData {

  /**
   * Cached processed text.
   *
   * @var string|null
   */
  protected $processed = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    // If we have already processed.
    if ($this->processed !== NULL) {
      // Return the processed value.
      return $this->processed;
    }
    // Get the product entity.
    $item = $this->getParent();
    $entity = $item->getEntity();
    // Get stock level.
    $stockManager = \Drupal::service('commerce.stock_manager');
    $level = $stockManager->getStockLevel($entity);
    // Save in processed.
    $this->processed = $level;
    // Return available stock.
    return $level;
  }

}
