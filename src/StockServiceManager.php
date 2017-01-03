<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;

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
   * {@inheritdoc}
   */
  public function addService(StockServiceInterface $stock_service) {
    $this->stockServices[$stock_service->getId()] = $stock_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getService(PurchasableEntityInterface $entity) {
    $config = \Drupal::config('commerce_stock.service_manager');
    $default_service_id = $config->get('default_service_id');
    $entity_type = $entity->getEntityType()->id();
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
  public function getPrimaryTransactionLocation(PurchasableEntityInterface $purchasable_entity, $quantity) {
    $stock_config = $this->getService($purchasable_entity)->getConfiguration();
    return $stock_config->getPrimaryTransactionLocation($purchasable_entity, $quantity);
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata = []) {
    $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
    $stock_updater->createTransaction($purchasable_entity->id(), $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function receiveStock(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $message = NULL) {
    $transaction_type_id = TRANSACTION_TYPE_NEW_STOCK;
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
    $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
    $stock_updater->createTransaction($purchasable_entity->id(), $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function sellStock(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL) {
    $transaction_type_id = TRANSACTION_TYPE_SALE;
    $metadata = [
      'related_oid' => $order_id,
      'related_uid' => $user_id,
    ];
    if (!is_null($message)) {
      $metadata['data']['message'] = $message;
    }
    // Make sure quantity is positive.
    $quantity = -1 * abs($quantity);
    $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
    $stock_updater->createTransaction($purchasable_entity->id(), $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function moveStock(PurchasableEntityInterface $purchasable_entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message = NULL) {
    $entity_id = $purchasable_entity->id();
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
    $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
    $tid = $stock_updater->createTransaction($entity_id, $from_location_id, $from_zone, $quantity_from, $unit_cost, TRANSACTION_TYPE_STOCK_MOVMENT_FROM, $metadata);
    // The second transaction will point to the first one.
    $metadata['related_tid'] = $tid;
    $stock_updater->createTransaction($entity_id, $to_location_id, $to_zone, $quantity_to, $unit_cost, TRANSACTION_TYPE_STOCK_MOVMENT_TO, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function returnStock(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL) {
    $transaction_type_id = TRANSACTION_TYPE_RETURN;
    $metadata = [
      'related_oid' => $order_id,
      'related_uid' => $user_id,
    ];
    if (!is_null($message)) {
      $metadata['data']['message'] = $message;
    }
    // Make sure quantity is positive.
    $quantity = abs($quantity);
    $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
    $stock_updater->createTransaction($purchasable_entity->id(), $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
  }

  /**
   * Gets the total stock level for a given purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $purchasable_entity
   *   The purchasable entity to get the stock level for.
   *
   * @return int
   *   The stock level.
   */
  public function getStockLevel(PurchasableEntityInterface $purchasable_entity) {
    $entity_id = $purchasable_entity->id();
    if (is_null($entity_id)) {
      return 0;
    }
    $stock_checker = $this->getService($purchasable_entity)->getStockChecker();
    // @todo - we need a better way to determine the locations.
    $locations = array_keys($stock_checker->getLocationList());

    return $stock_checker->getTotalStockLevel($entity_id, $locations);
  }

}
