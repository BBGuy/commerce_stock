<?php

namespace Drupal\commerce_stock;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the default stock_events manager.
 */
class StockEventsManager extends DefaultPluginManager implements StockEventsManagerInterface {

  /**
   * Provides default values for all stock_events plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'label' => '',
  ];

  /**
   * Constructs a new StockEventsManager object.
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
    // Add more services as required.
    parent::__construct('Plugin/StockEvents', $namespaces, $module_handler, 'Drupal\commerce_stock\Plugin\StockEventsInterface', 'Drupal\commerce_stock\Annotation\StockEvents');
    $this->alterInfo('commerce_stock_stock_events_info');
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'commerce_stock_stock_events');
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    // You can add validation of the plugin definition here.
    if (empty($definition['id'])) {
      throw new PluginException(sprintf('plugin property (%s) definition "is" is required.', $plugin_id));
    }
  }

}
