<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockCheckInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocalStockChecker implements StockCheckInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs the local stock checker.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Creates an instance of the local stock checker.
   *
   * @param ContainerInterface $container
   *   The DI container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Gets stock level for a given location and purchasable entity.
   *
   * @param int $location_id
   *   Location id.
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   Purchasable entity.
   *
   * @return array
   *   An array of 'qty' and 'last_transaction_id' values.
   */
  public function getLocationStockLevel($location_id, PurchasableEntityInterface $entity) {
    $result = $this->database->select('commerce_stock_location_level', 'll')
      ->fields('ll')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity->id())
      ->execute()
      ->fetch();

    return [
      'qty' => $result ? $result->qty : 0,
      'last_transaction' => $result ? $result->last_transaction_id : 0,
    ];
  }

  /**
   * Gets the last transaction id for a given location and purchasable entity.
   *
   * @param int $location_id
   *   Location id.
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   *
   * @return int
   *   The last location stock transaction id.
   */
  public function getLocationStockTransactionLatest($location_id, PurchasableEntityInterface $entity) {
    $query = $this->database->select('commerce_stock_transaction')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity->id());
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
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $min
   *   The minimum transaction number.
   * @param int $max
   *   The maximum transaction number.
   *
   * @return int
   *   The sum of stock transactions for a given location and purchasable entity.
   */
  public function getLocationStockTransactionSum($location_id, PurchasableEntityInterface $entity, $min, $max) {
    $query = $this->database->select('commerce_stock_transaction', 'txn')
      ->fields('txn', ['location_id'])
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity->id())
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
  public function getTotalStockLevel(PurchasableEntityInterface $entity, array $locations) {
    $location_info = $this->getLocationsStockLevels($entity, $locations);
    $total = 0;
    foreach ($location_info as $location) {
      $total += $location['qty'] + $location['transactions_qty'];
    }

    return $total;
  }

  /**
   * Gets the stock levels for a set of locations.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param array $locations
   *   Array of locations ids.
   *
   * @return array
   *   Stock level information indexed by location id with these values:
   *     - 'qty': The quantity.
   *     - 'last_transaction': The id of the last transaction.
   */
  public function getLocationsStockLevels(PurchasableEntityInterface $entity, array $locations) {
    $location_levels = [];
    /** @var \Drupal\commerce_stock\StockLocationInterface $location */
    foreach ($locations as $location) {
      $location_id = $location->id();
      $location_level = $this->getLocationStockLevel($location_id, $entity);

      $latest_txn = $this->getLocationStockTransactionLatest($location_id, $entity);
      $transactions_qty = $this->getLocationStockTransactionSum($location_id, $entity, $location_level['last_transaction'], $latest_txn);

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
  public function getIsInStock(PurchasableEntityInterface $entity, array $locations) {
    return ($this->getTotalStockLevel($entity, $locations) > 0);
  }

  /**
   * {@inheritdoc}
   */
  public function getIsAlwaysInStock(PurchasableEntityInterface $entity) {
    // @todo - not yet implamanted.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsStockManaged(PurchasableEntityInterface $entity) {
    // @todo - not yet implemented, so for now all products are managed.
    // Also we have the "always in stock" function so unless we have cascading s
    // service functionality this is not needed and can just return TRUE.
    return TRUE;
  }

}
