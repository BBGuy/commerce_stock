<?php

namespace Drupal\commerce_stock\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a stock event type item annotation object.
 *
 * Plugin namespace: Plugin\Commerce\StockEventType.
 *
 * @see \Drupal\commerce_stock\Plugin\StockEventTypeManager
 * @see plugin_api
 *
 * @Annotation
 */
class StockEventType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The stock event type label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The stock event type display label.
   *
   * For enventually use in UI.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $displayLabel;

  /**
   * The stock event type default transaction message.
   *
   * To use as default transaction message.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $transactionMessage;

}
