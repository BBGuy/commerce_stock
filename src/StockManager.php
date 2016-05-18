<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\StockManager.
 */


namespace Drupal\commerce_stock;


use Drupal\commerce_stock\StockManagerInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockServiceInterface;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\commerce_stock_s\LocalStockService;
use Drupal\commerce_stock\StockUpdateInterface;

class StockManager implements StockManagerInterface, StockTransactionsInterface {

  /**
   * The stock services.
   *
   * @var \Drupal\commerce_stock\StockServiceInterface[]
   */
  protected $stockServices = [];
  protected $stockManagerConfig;


  function __construct() {
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
    $service = $this->stockManagerConfig->getService($entity);
    return $service;
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
    $ids = array();
    foreach ($this->stockServices as $service) {
      $ids[$service->getID()] = $service->getName();
    }
    return $ids;
  }


  /**
   * {@inheritdoc}
   */
  public function receiveStock($purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $message = NULL) {
    // Make sure entity is a product variation.
    if ($purchasable_entity->getEntityTypeId() == 'commerce_product_variation') {
      // Set values.
      $variation_id = $purchasable_entity->id();
      $transaction_type_id = TRANSACTION_TYPE_NEW_STOCK;
      if (is_null($message)) {
        $metadata = array();
      }
      else {
        $metadata = array(
          'data' => array(
            'message' => $message,
          )
        );
      }
      // Make sure quentity is posative.
      $quantity = abs($quantity);
      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
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
  public function sellStock($purchasable_entity, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL) {
    // Make sure entity is a product variation.
    if ($purchasable_entity->getEntityTypeId() == 'commerce_product_variation') {
      // Set values.
      $variation_id = $purchasable_entity->id();
      $transaction_type_id = TRANSACTION_TYPE_SALE;
      $metadata = array(
        'related_oid' => $order_id,
        'related_uid' => $user_id,
      );

      if (!is_null($message)) {
        $metadata['data']['message'] = $message;
      }
      // Make sure quentity is posative.
      $quantity = -1 * abs($quantity);
      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
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
  public function moveStock($purchasable_entity, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message = NULL) {
    // Make sure entity is a product variation.
    if ($purchasable_entity->getEntityTypeId() == 'commerce_product_variation') {
      // Set values.
      $variation_id = $purchasable_entity->id();
      $transaction_type_id = TRANSACTION_TYPE_STOCK_MOVMENT;
      if (is_null($message)) {
        $metadata = array();
      }
      else {
        $metadata = array(
          'data' => array(
            'message' => $message,
          )
        );
      }

      // Make sure quentity is posative.
      $quantity_from = -1 * abs($quantity);
      $quantity_to = abs($quantity);

      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
      // Create the transactions.
      $tid = $stock_updater->createTransaction($variation_id, $from_location_id, $from_zone, $quantity_from, $unit_cost, $transaction_type_id, $metadata);
      // The second transaction will point to the first one.
      $metadata['related_tid'] = $tid;
      $tid = $stock_updater->createTransaction($variation_id, $to_location_id, $to_zone, $quantity_to, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function returnStock($purchasable_entity, $location_id,  $zone, $quantity, $unit_cost, $order_id, $user_id, $message = NULL) {
    // Make sure entity is a product variation.
    if ($purchasable_entity->getEntityTypeId() == 'commerce_product_variation') {
      // Set values.
      $variation_id = $purchasable_entity->id();
      $transaction_type_id = TRANSACTION_TYPE_RETURN;
      $metadata = array(
        'related_oid' => $order_id,
        'related_uid' => $user_id,
      );

      if (!is_null($message)) {
        $metadata['data']['message'] = $message;
      }


      // Make sure quentity is posative.
      $quantity = abs($quantity);

      // Get the stock updater.
      $stock_updater = $this->getService($purchasable_entity)->getStockUpdater();
      // Create the transactions.
      $tid = $stock_updater->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, $metadata);
    }
    else {
      // @todo - raise exception.
    }

  }

}
