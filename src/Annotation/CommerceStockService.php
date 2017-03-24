<?php

namespace Drupal\commerce_stock\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the stock service plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\StockService.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceStockService extends Plugin {

  /**
   * The stock service plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The stock service label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
