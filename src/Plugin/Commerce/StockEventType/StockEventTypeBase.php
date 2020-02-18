<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Stock Event Type plugins.
 */
abstract class StockEventTypeBase extends PluginBase implements StockEventTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayLabel() {
    return $this->pluginDefinition['displayLabel'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultMessage() {
    return $this->pluginDefinition['transactionMessage'];
  }

}
