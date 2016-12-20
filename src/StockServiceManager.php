<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;

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
   * The stock service manager config.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerConfig
   */
  protected $stockServiceManagerConfig;

  /**
   * Constructs a new StockServiceManagerConfig object.
   */
  public function __construct() {
    $this->stockServiceManagerConfig = new StockServiceManagerConfig($this);
  }

  /**
   * {@inheritdoc}
   */
  public function addService(StockServiceInterface $stock_service) {
    $this->stockServices[] = $stock_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getService(PurchasableEntityInterface $entity) {
    return $this->stockServiceManagerConfig->getService($entity);
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
    if ($purchasable_entity instanceof ProductVariationInterface) {
      $variation_id = $purchasable_entity->id();
      $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
      $stock_updater->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function receiveStock(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $message = NULL) {
    if ($purchasable_entity instanceof ProductVariationInterface) {
      $variation_id = $purchasable_entity->id();
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
      $stock_updater->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sellStock(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL) {
    if ($purchasable_entity instanceof ProductVariationInterface) {
      $variation_id = $purchasable_entity->id();
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
      $stock_updater->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function moveStock(PurchasableEntityInterface $purchasable_entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message = NULL) {
    if ($purchasable_entity instanceof ProductVariationInterface) {
      $variation_id = $purchasable_entity->id();
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
      $tid = $stock_updater->createTransaction($variation_id, $from_location_id, $from_zone, $quantity_from, $unit_cost, TRANSACTION_TYPE_STOCK_MOVMENT_FROM, $metadata);
      // The second transaction will point to the first one.
      $metadata['related_tid'] = $tid;
      $stock_updater->createTransaction($variation_id, $to_location_id, $to_zone, $quantity_to, $unit_cost, TRANSACTION_TYPE_STOCK_MOVMENT_TO, $metadata);
    }
    else {
      // @todo - raise exception.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function returnStock(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL) {
    if ($purchasable_entity instanceof ProductVariationInterface) {
      $variation_id = $purchasable_entity->id();
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
      $stock_updater->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }
  }

  /**
   * Gets the total stock level for a given purchasable entity.
   */
  public function getStockLevel(PurchasableEntityInterface $purchasable_entity) {
    if ($purchasable_entity instanceof ProductVariationInterface) {
      $variation_id = $purchasable_entity->id();
      if (is_null($variation_id)) {
        return 0;
      }
      $stock_checker = $this->getService($purchasable_entity)->getStockChecker();
      // @todo - we need a better way to determine the locations.
      $locations = array_keys($stock_checker->getLocationList());

      return $stock_checker->getTotalStockLevel($variation_id, $locations);
    }
    else {
      // @todo - raise exception.
    }

  }

}
