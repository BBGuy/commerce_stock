<?php

namespace Drupal\commerce_stock_field;

use Drupal\commerce_stock\ContextCreatorTrait;
use Drupal\Core\TypedData\TypedData;

/**
 * Processor used by the StockLevel field.
 */
class StockLevelProcessor extends TypedData {

  use ContextCreatorTrait;

  /**
   * Whether the stock level have already been computed or not.
   *
   * @var bool
   */
  protected $valueComputed = FALSE;

  /**
   * Cached processed level.
   *
   * @var float|null
   */
  protected $processedLevel = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureComputedValue();
    return $this->processedLevel;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    if (is_null($value)) {
      return;
    }
    $this->processedLevel = $value;
    // Make sure that subsequent getter calls do not try to compute the
    // stock level again.
    $this->valueComputed = TRUE;
  }

  /**
   * Get the current stock level.
   */
  protected function computeValue() {
    $entity = $this->getParent()->getEntity();
    /** @var \Drupal\commerce_stock\StockServiceManager $stockServiceManager */
    $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
    $context = $this->getContext($entity);
    $locations = $stockServiceManager->getService($entity)->getConfiguration()->getAvailabilityLocations($context, $entity);
    $level = $stockServiceManager->getService($entity)->getStockChecker()->getTotalStockLevel($entity, $locations);
    $this->processedLevel = $level;
  }

  /**
   * Ensures that the stock level is only computed once.
   */
  protected function ensureComputedValue() {
    if ($this->valueComputed === FALSE) {
      $this->computeValue();
      $this->valueComputed = TRUE;
    }
  }

}
