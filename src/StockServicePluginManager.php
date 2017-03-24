<?php

namespace Drupal\commerce_stock;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages stock service plugins.
 */
class StockServicePluginManager extends DefaultPluginManager {

  /**
   * Constructs a new StockServicePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Commerce/StockService', $namespaces, $module_handler, 'Drupal\commerce_stock\Plugin\Commerce\StockService\StockServiceInterface', 'Drupal\commerce_stock\Annotation\CommerceStockService');

    $this->alterInfo('commerce_stock_service_info');
    $this->setCacheBackend($cache_backend, 'commerce_stock_service_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    foreach (['id', 'label'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new PluginException(sprintf('The stock service plugin %s must define the %s property.', $plugin_id, $required_property));
      }
    }
  }

}
