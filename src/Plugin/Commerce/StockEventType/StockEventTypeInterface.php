<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockEventType;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Stock Event Type plugins.
 */
interface StockEventTypeInterface extends PluginInspectionInterface {

  /**
   * Gets the stock event type label.
   *
   * @return string
   *   The stock event type  label.
   */
  public function getLabel();

  /**
   * Gets the stock event type display label.
   *
   * For enventually use in UI.
   *
   * @return string
   *   The stock event type  display label.
   */
  public function getDisplayLabel();

  /**
   * Gets the stock event type default transaction message.
   *
   * @return string
   *   The stock event type transaction message.
   */
  public function getDefaultMessage();

}
