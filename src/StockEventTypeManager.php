<?php

namespace Drupal\commerce_stock;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the Stock Event Type plugin manager.
 */
class StockEventTypeManager extends DefaultPluginManager implements StockEventTypeManagerInterface {

  /**
   * Constructs a new StockEventTypeManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct('Plugin/Commerce/StockEventType', $namespaces, $module_handler, 'Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface', 'Drupal\commerce_stock\Annotation\StockEventType');
    $this->alterInfo('commerce_stock_event_type_info');
    $this->setCacheBackend($cache_backend, 'commerce_stock_event_type_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    foreach (['id', 'label'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new PluginException(sprintf('The stock event type "%s" must define the "%s" property.', $plugin_id, $required_property));
      }
    }
  }

}
