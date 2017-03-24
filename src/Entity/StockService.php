<?php

namespace Drupal\commerce_stock\Entity;

use Drupal\commerce_stock\StockServicePluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the stock service configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_stock_service",
 *   label = @Translation("Stock service"),
 *   label_singular = @Translation("stock service"),
 *   label_plural = @Translation("stock service"),
 *   label_count = @PluralTranslation(
 *     singular = "@count stock service",
 *     plural = "@count stock services",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_stock\StockServiceListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_stock\Form\StockServiceForm",
 *       "edit" = "Drupal\commerce_checkout\Form\StockServiceForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_stock_service",
 *   admin_permission = "administer commerce_stock_service",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight",
 *     "status" = "status",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "status",
 *     "plugin",
 *     "configuration",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/stock-services/add",
 *     "edit-form" = "/admin/commerce/config/stock-services/manage/{commerce_stock_service}",
 *     "delete-form" = "/admin/commerce/config/stock-services/manage/{commerce_stock_service}/delete",
 *     "collection" =  "/admin/commerce/config/stock-services"
 *   }
 * )
 */
class StockService extends ConfigEntityBase implements StockServiceInterface {

  /**
   * The stock service ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The stock service label.
   *
   * @var string
   */
  protected $label;

  /**
   * The stock service weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * The plugin ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * The plugin collection that holds the stock service plugin.
   *
   * @var \Drupal\commerce_stock\StockServicePluginCollection
   */
  protected $pluginCollection;

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginId($plugin_id) {
    $this->plugin = $plugin_id;
    $this->pluginCollection = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'configuration' => $this->getPluginCollection(),
    ];
  }

  /**
   * Gets the plugin collection that holds the stock service plugin.
   *
   * Ensures the plugin collection is initialized before returning it.
   *
   * @return \Drupal\commerce_stock\StockServicePluginCollection
   *   The plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $plugin_manager = \Drupal::service('plugin.manager.commerce_stock_service');
      $this->pluginCollection = new StockServicePluginCollection($plugin_manager, $this->plugin, $this->configuration, $this->id);
    }
    return $this->pluginCollection;
  }

}
