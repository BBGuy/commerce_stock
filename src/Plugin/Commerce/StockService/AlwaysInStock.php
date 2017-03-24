<?php

namespace Drupal\commerce_stock\Plugin\Commerce\StockService;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides an 'Always in stock' service.
 *
 * @CommerceStockService(
 *   id = 'always_in_stock',
 *   label = 'Always in stock',
 * )
 */
class AlwaysInStock extends StockServiceBase {

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\Plugin\Commerce\StockService\AlwaysInStockCheckerUpdater
   */
  protected $stockChecker;

  /**
   * The stock updater.
   *
   * @var \Drupal\commerce_stock\Plugin\Commerce\StockService\AlwaysInStockCheckerUpdater
   */
  protected $stockUpdater;

  /**
   * Constructs a new AlwaysInStock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);

    $this->stockChecker = $this->stockUpdater = new AlwaysInStockCheckerUpdater();
  }

  /**
   * {@inheritdoc}
   */
  public function getStockChecker() {
    return $this->stockChecker;
  }

  /**
   * {@inheritdoc}
   */
  public function getStockUpdater() {
    return $this->stockUpdater;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailabilityLocations(Context $context, PurchasableEntityInterface $entity) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getTransactionLocation(Context $context, PurchasableEntityInterface $entity, $quantity) {
    return NULL;
  }

}
