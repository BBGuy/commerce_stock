<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * The stock service manager, responsible for handling services and transactions.
 *
 * @see StockAvailabilityChecker.
 *
 * @package Drupal\commerce_stock
 */
class StockServiceManager implements StockServiceManagerInterface, StockTransactionsInterface {

  /**
   * The stock services.
   *
   * @var \Drupal\commerce_stock\StockServiceInterface[]
   */
  protected $stockServices = [];

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a StockServiceManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function addService(StockServiceInterface $stock_service) {
    $this->stockServices[$stock_service->getId()] = $stock_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getService(PurchasableEntityInterface $entity) {
    $config = $this->configFactory->get('commerce_stock.service_manager');

    $default_service_id = $config->get('default_service_id');

    $entity_type = $entity->getEntityTypeId();
    $entity_bundle = $entity->bundle();
    $entity_config_key = $entity_type . '_' . $entity_bundle . '_service_id';
    $entity_service_id = $config->get($entity_config_key);

    $service_id = $entity_service_id ?: $default_service_id;

    return $this->stockServices[$service_id];
  }

  /**
   * {@inheritdoc}
   */
  public function listServices() {
    return $this->stockServices;
  }

  /**
   * {@inheritdoc}
   */
  public function listServiceIds() {
    $ids = [];
    foreach ($this->stockServices as $service) {
      $ids[$service->getId()] = $service->getName();
    }

    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $entity, $quantity) {
    $stock_config = $this->getService($entity)->getConfiguration();
    return $stock_config->getPrimaryTransactionLocation($entity, $quantity);
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, Price $unit_cost, $transaction_type_id, array $metadata = []) {
    $stock_updater = $this->getService($entity)->getStockUpdater();
    $stock_updater->createTransaction($entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function receiveStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, Price $unit_cost, $message = NULL) {
    $transaction_type_id = StockTransactionsInterface::NEW_STOCK;
    if (is_null($message)) {
      $metadata = [];
    }
    else {
      $metadata = [
        'data' => [
          'message' => $message,
        ],
      ];
    }
    // Make sure quantity is positive.
    $quantity = abs($quantity);
    $stock_updater = $this->getService($entity)->getStockUpdater();
    $stock_updater->createTransaction($entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function sellStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, Price $unit_cost, $order_id, $user_id, $message = NULL) {
    $transaction_type_id = StockTransactionsInterface::STOCK_SALE;
    $metadata = [
      'related_oid' => $order_id,
      'related_uid' => $user_id,
    ];
    if (!is_null($message)) {
      $metadata['data']['message'] = $message;
    }
    // Make sure quantity is positive.
    $quantity = -1 * abs($quantity);
    $stock_updater = $this->getService($entity)->getStockUpdater();
    $stock_updater->createTransaction($entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function moveStock(PurchasableEntityInterface $entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, Price $unit_cost, $message = NULL) {
    if (is_null($message)) {
      $metadata = [];
    }
    else {
      $metadata = [
        'data' => [
          'message' => $message,
        ],
      ];
    }
    // Make sure quantity is positive.
    $quantity_from = -1 * abs($quantity);
    $quantity_to = abs($quantity);
    $stock_updater = $this->getService($entity)->getStockUpdater();
    $tid = $stock_updater->createTransaction($entity, $from_location_id, $from_zone, $quantity_from, $unit_cost, StockTransactionsInterface::MOVEMENT_FROM, $metadata);
    // The second transaction will point to the first one.
    $metadata['related_tid'] = $tid;
    $stock_updater->createTransaction($entity, $to_location_id, $to_zone, $quantity_to, $unit_cost, StockTransactionsInterface::MOVEMENT_TO, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function returnStock(PurchasableEntityInterface $entity, $location_id, $zone, $quantity, Price $unit_cost, $order_id, $user_id, $message = NULL) {
    $transaction_type_id = StockTransactionsInterface::STOCK_RETURN;
    $metadata = [
      'related_oid' => $order_id,
      'related_uid' => $user_id,
    ];
    if (!is_null($message)) {
      $metadata['data']['message'] = $message;
    }
    // Make sure quantity is positive.
    $quantity = abs($quantity);
    $stock_updater = $this->getService($entity)->getStockUpdater();
    $stock_updater->createTransaction($entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * Gets the total stock level for a given purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity to get the stock level for.
   *
   * @return int
   *   The stock level.
   */
  public function getStockLevel(PurchasableEntityInterface $entity) {
    if ($entity->isNew()) {
      return 0;
    }
    $stock_config = $this->getService($entity)->getConfiguration();
    $stock_checker = $this->getService($entity)->getStockChecker();
    $locations = $stock_config->getLocationList($entity);

    return $stock_checker->getTotalStockLevel($entity, $locations);
  }

}
