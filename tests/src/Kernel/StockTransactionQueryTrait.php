<?php

namespace Drupal\Tests\commerce_stock\Kernel;

/**
 * Provides methods to query the transaction database.
 */
trait StockTransactionQueryTrait {

  /**
   * Return the last transaction for a entity.
   *
   * @param string $entity_id
   *   The id of the entity to.
   *
   * @return obj
   *   The transaction.
   */
  protected function getLastEntityTransaction($entity_id) {
    $connection = \Drupal::database();
    $query = $connection->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->condition('entity_id', $entity_id)
      ->orderBy('id', 'DESC')
      ->range(0, 1);
    return $query
      ->execute()
      ->fetchObject();
  }

  /**
   * Return the last transaction in the table.
   *
   * @return obj
   *   The transaction.
   */
  protected function getLastTransaction() {
    $connection = \Drupal::database();
    $query = $connection->select('commerce_stock_transaction', 'txn')
      ->fields('txn')
      ->orderBy('id', 'DESC')
      ->range(0, 1);
    return $query
      ->execute()
      ->fetchObject();
  }

}
