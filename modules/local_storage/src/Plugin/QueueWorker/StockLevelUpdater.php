<?php

namespace Drupal\commerce_stock_local\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Commerce Stock Local location level update worker.
 *
 * @QueueWorker(
 *   id = "commerce_stock_local_stock_level_updater",
 *   title = @Translation("Commerce Stock Local stock level updater"),
 *   cron = {"time" = 30}
 * )
 *
 * @ToDo Inject the config factory instead of calling \Drupal::
 */
class StockLevelUpdater extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $storage = \Drupal::entityTypeManager()->getStorage($data['entity_type']);
    $entity = $storage->load($data['entity_id']);
    if (!$entity) {
      return;
    }
    // Load the Stockupdate Service.
    $service = \Drupal::service('commerce_stock.local_stock_service');
    /** @var \Drupal\commerce_stock_local\LocalStockUpdater $updater */
    $updater = $service->getStockUpdater();

    /** @var \Drupal\commerce_stock_local\StockLocationStorage $locationStorage */
    $locationStorage = \Drupal::entityTypeManager()->getStorage('commerce_stock_location');
    $locations = $locationStorage->loadEnabled($entity);

    foreach ($locations as $location) {
      $updater->updateLocationStockLevel($location->getId(), $entity);
    }
    $entity->save();
  }

}
