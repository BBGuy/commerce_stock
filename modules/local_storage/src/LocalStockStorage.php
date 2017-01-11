<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockUpdateInterface;

/**
 * The backend for the local stock service.
 */
class LocalStockStorage implements StockCheckInterface, StockUpdateInterface {

  /**
   * {@inheritdoc}
   */
  public function createTransaction($entity_id, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata) {
    // Get optional fields.
    $related_tid = isset($metadata['related_tid']) ? $metadata['related_tid'] : NULL;
    $related_oid = isset($metadata['related_oid']) ? $metadata['related_oid'] : NULL;
    $related_uid = isset($metadata['related_uid']) ? $metadata['related_uid'] : NULL;
    $data = isset($metadata['data']) ? $metadata['data'] : NULL;

    // Create a record.
    $field_values = [
      'entity_id' => $entity_id,
      'qty' => $quantity,
      'location_id' => $location_id,
      'location_zone' => $zone,
      'unit_cost' => $unit_cost,
      'transaction_time' => time(),
      'transaction_type_id' => $transaction_type_id,
      'related_tid' => $related_tid,
      'related_oid' => $related_oid,
      'related_uid' => $related_uid,
      'data' => serialize($data),
    ];
    $insert = \Drupal::database()->insert('commerce_stock_transaction')
      ->fields(array_keys($field_values))
      ->values(array_values($field_values))->execute();

    return $insert;
  }

  /**
   * {@inheritdoc}
   */
  public function updateLocationStockLevel($location_id, $entity_id) {
    $current_level = $this->getLocationStockLevel($location_id, $entity_id);
    $last_update = $current_level['last_transaction'];
    $latest_txn = $this->getLocationStockTransactionLatest($location_id, $entity_id);
    $latest_sum = $this->getLocationStockTransactionSum($location_id, $entity_id, $last_update, $latest_txn);
    $new_level = $current_level['qty'] + $latest_sum;

    $this->setLocationStockLevel($location_id, $entity_id, $new_level, $latest_txn);
  }

  /**
   * Gets stock level for a given location and purchasable entity.
   *
   * @param int $location_id
   *   Location id.
   * @param int $entity_id
   *   Purchasable entity ID.
   *
   * @return array
   *   An array of 'qty' and 'last_transaction_id' values.
   */
  public function getLocationStockLevel($location_id, $entity_id) {
    $db = \Drupal::database();
    $result = $db->select('commerce_stock_location_level', 'll')
      ->fields('ll')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity_id)
      ->execute()
      ->fetch();

    return [
      'qty' => $result ? $result->qty : 0,
      'last_transaction' => $result ? $result->last_transaction_id : 0,
    ];
  }

  /**
   * Sets the stock level and last transaction for a given location and purchasable entity.
   *
   * Creates first stock level transaction record if none exists.
   *
   * @param int $location_id
   *   Location id.
   * @param int $entity_id
   *   Purchasable entity ID.
   * @param int $qty
   *   Quantity.
   * @param int $last_txn
   *   Last transaction id.
   */
  public function setLocationStockLevel($location_id, $entity_id, $qty, $last_txn) {
    $db = \Drupal::database();
    $existing = $db->select('commerce_stock_location_level', 'll')
      ->fields('ll')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity_id)
      ->execute()->fetch();
    if ($existing) {
      $db->update('commerce_stock_location_level')
        ->fields([
          'qty' => $qty,
          'last_transaction_id' => $last_txn,
        ])
        ->condition('location_id', $location_id, '=')
        ->condition('entity_id', $entity_id, '=')
        ->execute();
    }
    else {
      $db->insert('commerce_stock_location_level')
        ->fields(['location_id', 'entity_id', 'qty', 'last_transaction_id'])
        ->values([$location_id, $entity_id, $qty, $last_txn])
        ->execute();
    }
  }

  /**
   * Gets the last transaction id for a given location and purchasable entity.
   *
   * @param int $location_id
   *   Location id.
   * @param int $entity_id
   *   Purchasable entity ID.
   *
   * @return int
   *   The last location stock transaction id.
   */
  public function getLocationStockTransactionLatest($location_id, $entity_id) {
    $db = \Drupal::database();
    $query = $db->select('commerce_stock_transaction')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity_id);
    $query->addExpression('MAX(id)', 'max_id');
    $query->groupBy('location_id');
    $result = $query
      ->execute()
      ->fetch();

    return $result ? $result->max_id : 0;
  }

  /**
   * Gets the sum of all stock transactions between a range of transactions.
   *
   * @param int $location_id
   *   The location id.
   * @param int $entity_id
   *   The purchasable entity ID.
   * @param int $min
   *   The minimum transaction number.
   * @param int $max
   *   The maximum transaction number.
   *
   * @return int
   *   The sum of stock transactions for a given location and purchasable entity.
   */
  public function getLocationStockTransactionSum($location_id, $entity_id, $min, $max) {
    $db = \Drupal::database();
    $query = $db->select('commerce_stock_transaction', 'txn')
      ->fields('txn', ['location_id'])
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity_id)
      ->condition('id', $min, '>');
    if ($max) {
      $query->condition('id', $max, '<=');
    }
    $query->addExpression('SUM(qty)', 'qty');
    $query->groupBy('location_id');
    $result = $query->execute()
      ->fetch();

    return $result ? $result->qty : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalStockLevel($entity_id, array $locations) {
    $location_info = $this->getLocationsStockLevels($entity_id, $locations);
    $total = 0;
    foreach ($location_info as $location) {
      $total += $location['qty'] + $location['transactions_qty'];
    }

    return $total;
  }

  /**
   * Gets the stock levels for a set of locations.
   *
   * @param int $entity_id
   *   The purchasable entity ID.
   * @param array $locations
   *   Array of locations ids.
   *
   * @return array
   *   Stock level information indexed by location id with these values:
   *     - 'qty': The quantity.
   *     - 'last_transaction': The id of the last transaction.
   */
  public function getLocationsStockLevels($entity_id, array $locations) {
    $location_levels = [];
    /** @var \Drupal\commerce_stock\StockLocationInterface $location */
    foreach ($locations as $location) {
      $location_id = $location->id();
      $location_level = $this->getLocationStockLevel($location_id, $entity_id);

      $latest_txn = $this->getLocationStockTransactionLatest($location_id, $entity_id);
      $transactions_qty = $this->getLocationStockTransactionSum($location_id, $entity_id, $location_level['last_transaction'], $latest_txn);

      $location_levels[$location_id] = [
        'qty' => $location_level['qty'],
        'transactions_qty' => $transactions_qty,
      ];
    }

    return $location_levels;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsInStock($entity_id, array $locations) {
    return ($this->getTotalStockLevel($entity_id, $locations) > 0);
  }

  /**
   * {@inheritdoc}
   */
  public function getIsAlwaysInStock($entity_id) {
    // @todo - not yet implamanted.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsStockManaged($entity_id) {
    // @todo - not yet implemented, so for now all products are managed.
    // Also we have the "always in stock" function so unless we have cascading s
    // service functionality this is not needed and can just return TRUE.
    return TRUE;
  }

}
