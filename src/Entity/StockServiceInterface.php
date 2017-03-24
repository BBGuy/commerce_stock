<?php

namespace Drupal\commerce_stock\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the interface for stock services.
 *
 * This configuration entity stores configuration for stock service plugins.
 */
interface StockServiceInterface extends ConfigEntityInterface {

  /**
   * Gets the stock service weight.
   *
   * @return string
   *   The stock service weight.
   */
  public function getWeight();

  /**
   * Sets the stock service weight.
   *
   * @param int $weight
   *   The stock service weight.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Gets the stock service plugin.
   *
   * @return \Drupal\commerce_stock\Plugin\Commerce\StockService\StockServiceInterface
   *   The stock service.
   */
  public function getPlugin();

  /**
   * Gets the stock service plugin ID.
   *
   * @return string
   *   The stock service plugin ID.
   */
  public function getPluginId();

  /**
   * Sets the stock service plugin ID.
   *
   * @param string $plugin_id
   *   The stock service plugin ID.
   *
   * @return $this
   */
  public function setPluginId($plugin_id);

}
