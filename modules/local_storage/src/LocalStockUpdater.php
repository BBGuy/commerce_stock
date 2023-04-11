<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockUpdateInterface;
use Drupal\commerce_stock_local\Event\LocalStockTransactionEvent;
use Drupal\commerce_stock_local\Event\LocalStockTransactionEvents;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class LocalStockUpdater.
 */
class LocalStockUpdater implements StockUpdateInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The local stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $checker;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the local stock updater.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\commerce_stock\StockCheckInterface $checker
   *   The local stock checker.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    Connection $database,
    StockCheckInterface $checker,
    EventDispatcherInterface $event_dispatcher,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->database = $database;
    $this->checker = $checker;
    $this->eventDispatcher = $event_dispatcher;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Creates an instance of the local stock checker.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The DI container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('commerce_stock.local_stock_checker'),
      $container->get('event_dispatcher'),
      $container->get('entity_type_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(
    PurchasableEntityInterface $entity,
    $location_id,
    $zone,
    $quantity,
    $unit_cost,
    $currency_code,
    $transaction_type_id,
    array $metadata
  ) {
    // Get optional fields.
    $related_tid = isset($metadata['related_tid']) ? $metadata['related_tid'] : NULL;
    $related_oid = isset($metadata['related_oid']) ? $metadata['related_oid'] : NULL;
    $related_uid = isset($metadata['related_uid']) ? $metadata['related_uid'] : NULL;
    $data = isset($metadata['data']) ? $metadata['data'] : NULL;

    // Create a record.
    $field_values = [
      'entity_id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
      'qty' => $quantity,
      'location_id' => $location_id,
      'location_zone' => $zone,
      'unit_cost' => $unit_cost,
      'currency_code' => $currency_code,
      'transaction_time' => time(),
      'transaction_type_id' => $transaction_type_id,
      'related_tid' => $related_tid,
      'related_oid' => $related_oid,
      'related_uid' => $related_uid,
      'data' => serialize($data),
    ];

    $event = new LocalStockTransactionEvent($this->entityTypeManager, $field_values);

    $this->eventDispatcher->dispatch($event, LocalStockTransactionEvents::LOCAL_STOCK_TRANSACTION_CREATE);
    $insert = $this->database->insert('commerce_stock_transaction')
      ->fields(array_keys($field_values))
      ->values(array_values($field_values))->execute();

    $this->eventDispatcher->dispatch($event, LocalStockTransactionEvents::LOCAL_STOCK_TRANSACTION_INSERT);

    // Find out if we have real-time aggregation turned on.
    $transactions_aggregation_mode = \Drupal::config('commerce_stock_local.transactions')->get('transactions_aggregation_mode');
    if ($transactions_aggregation_mode == 'real-time') {
      // Aggregate if we do.
      /** @var \Drupal\commerce_stock_local\StockLocationStorage $locationStorage */
      $locationStorage = \Drupal::entityTypeManager()
        ->getStorage('commerce_stock_location');
      $locations = $locationStorage->loadEnabled($entity);

      foreach ($locations as $location) {
        $this->updateLocationStockLevel($location->getId(), $entity);
      }
    }

    return $insert;
  }

  /**
   * Updates the stock level of an entity at a specific location.
   *
   * @param int $location_id
   *   The location id.
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   */
  public function updateLocationStockLevel(
    $location_id,
    PurchasableEntityInterface $entity
  ) {
    $current_level = $this->checker->getLocationStockLevel($location_id, $entity);
    $last_update = $current_level['last_transaction'];
    $latest_txn = $this->checker->getLocationStockTransactionLatest($location_id, $entity);
    $latest_sum = $this->checker->getLocationStockTransactionSum($location_id, $entity, $last_update, $latest_txn);
    $new_level = $current_level['qty'] + $latest_sum;

    $this->setLocationStockLevel($location_id, $entity, $new_level, $latest_txn);


    // Do we need to clear the transactions after they have been aggregated?
    $transactions_retention = \Drupal::config('commerce_stock_local.transactions')->get('transactions_retention');
    if ($transactions_retention == 'delete') {
       $this->clearLocationStockTransactions($location_id, $entity, $latest_txn);
    }

  }

  /**
   * Set the location stock level.
   *
   * Sets the stock level and last transaction for a given location and
   * purchasable entity.
   * Creates first stock level transaction record if none exists.
   *
   * @param int $location_id
   *   The location id.
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $qty
   *   The quantity.
   * @param int $last_txn
   *   The last transaction id.
   */
  public function setLocationStockLevel(
    $location_id,
    PurchasableEntityInterface $entity,
    $qty,
    $last_txn
  ) {
    $existing = $this->database->select('commerce_stock_location_level', 'll')
      ->fields('ll')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity->id())
      ->condition('entity_type', $entity->getEntityTypeId())
      ->execute()->fetch();
    if ($existing) {
      $this->database->update('commerce_stock_location_level')
        ->fields([
          'qty' => $qty,
          'last_transaction_id' => $last_txn,
        ])
        ->condition('location_id', $location_id, '=')
        ->condition('entity_id', $entity->id(), '=')
        ->condition('entity_type', $entity->getEntityTypeId())
        ->execute();
    }
    else {
      $this->database->insert('commerce_stock_location_level')
        ->fields([
          'location_id',
          'entity_id',
          'entity_type',
          'qty',
          'last_transaction_id',
        ])
        ->values([
          $location_id,
          $entity->id(),
          $entity->getEntityTypeId(),
          $qty,
          $last_txn,
        ])
        ->execute();
    }
  }


  public function clearLocationStockTransactions(
    $location_id,
    PurchasableEntityInterface $entity,
    $last_txn
  ) {
    $query = $this->database->delete('commerce_stock_transaction')
      ->condition('location_id', $location_id)
      ->condition('entity_id', $entity->id())
      ->condition('entity_type', $entity->getEntityTypeId())
      ->condition('id', $last_txn, '<=');
    $result = $query->execute();

    return $result;

  }

}
