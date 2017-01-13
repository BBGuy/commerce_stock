<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce_stock\StockUpdateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LocalStockUpdater
 */
class LocalStockUpdater implements StockUpdateInterface {

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
   */
  public function __construct(Connection $database){
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
    $insert = $this->database->insert('commerce_stock_transaction')
      ->fields(array_keys($field_values))
      ->values(array_values($field_values))->execute();

    return $insert;
  }

}
