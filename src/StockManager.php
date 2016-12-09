<?php

namespace Drupal\commerce_stock;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;

/**
 * The Stock Manager service collector used by the Stock availability checker.
 */
class StockManager implements StockManagerInterface, StockTransactionsInterface {

  /**
   * The stock services.
   *
   * @var \Drupal\commerce_stock\StockServiceInterface[]
   */
  protected $stockServices = [];

  /**
   * The stock manager config.
   *
   * @var \Drupal\commerce_stock\StockManagerConfig
   */
  protected $stockManagerConfig;

  /**
   * Constructs a new StockManager object.
   */
  public function __construct() {
    $this->stockManagerConfig = new StockManagerConfig($this);
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
    return $this->stockManagerConfig->getService($entity);
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
    $stock_config = $this->getService($purchasable_entity)
      ->getConfiguration();
    return $stock_config->getPrimaryTransactionLocation($purchasable_entity, $quantity);
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(PurchasableEntityInterface $purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata = []) {
    if ($purchasable_entity instanceof ProductVariationInterface) {
      // Set values.
      $variation_id = $purchasable_entity->id();
      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)
        ->getStockUpdater();
      // Create the transaction.
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
    // Make sure entity is a product variation.
    if ($purchasable_entity instanceof ProductVariationInterface) {
      // Set values.
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
      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)
        ->getStockUpdater();

      // Create the transaction.
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
    // Make sure entity is a product variation.
    if ($purchasable_entity instanceof ProductVariationInterface) {
      // Set values.
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
      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)
        ->getStockUpdater();
      // Create the transaction.
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
    // Make sure entity is a product variation.
    if ($purchasable_entity instanceof ProductVariationInterface) {
      // Set values.
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

      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)
        ->getStockUpdater();
      // Create the transactions.
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
    // Make sure entity is a product variation.
    if ($purchasable_entity instanceof ProductVariationInterface) {
      // Set values.
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

      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)
        ->getStockUpdater();
      // Create the transactions.
      $stock_updater->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }

  }

  /**
   *
   */
  public function getStockLevel(PurchasableEntityInterface $purchasable_entity) {
    // Make sure entity is a product variation.
    if ($purchasable_entity instanceof ProductVariationInterface) {
      // Get the  ID.
      $variation_id = $purchasable_entity->id();
      // If no ID no stock.
      if (is_null($variation_id)) {
        return 0;
      }
      // Get the stock checker.
      $stock_checker = $this->getService($purchasable_entity)
        ->getStockChecker();
      // @todo - we need a better way to determin the locations.
      $locations = array_keys($stock_checker->getLocationList());
      return $stock_checker->getTotalStockLevel($variation_id, $locations);

    }
    else {
      // @todo - raise exception.
    }

  }

}
