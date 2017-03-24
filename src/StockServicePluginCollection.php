<?php

namespace Drupal\commerce_stock;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * A collection of stock service config plugins.
 */
class StockServicePluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * The stock service entity ID this plugin collection belongs to.
   *
   * @var string
   */
  protected $entityId;

  /**
   * Constructs a new StockServicePluginCollection object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The manager to be used for instantiating plugins.
   * @param string $instance_id
   *   The ID of the plugin instance.
   * @param array $configuration
   *   An array of configuration.
   * @param string $entity_id
   *   The stock service entity ID this plugin collection belongs to.
   */
  public function __construct(PluginManagerInterface $manager, $instance_id, array $configuration, $entity_id) {
    parent::__construct($manager, $instance_id, $configuration);

    $this->entityId = $entity_id;
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\commerce_stock\Plugin\Commerce\StockService\StockServiceInterface
   *   The stock service plugin.
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    if (!$instance_id) {
      throw new PluginException("The stock service '{$this->entityId}' did not specify a plugin.");
    }

    $configuration = $this->configuration + ['_entity_id' => $this->entityId];
    $plugin = $this->manager->createInstance($instance_id, $configuration);
    $this->set($instance_id, $plugin);
  }

}
