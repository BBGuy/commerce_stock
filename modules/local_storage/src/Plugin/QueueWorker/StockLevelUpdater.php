<?php

namespace Drupal\commerce_stock_local\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Commerce Stock Local location level update worker.
 *
 * @QueueWorker(
 *   id = "commerce_stock_local_stock_level_updater",
 *   title = @Translation("Commerce Stock Local stock level updater"),
 *   cron = {"time" = 10}
 * )
 *
 * @ToDo Inject the config factory instead of calling \Drupal::
 */
class StockLevelUpdater extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $entity_id = $data;
    $checker = \Drupal::service('commerce_stock.local_stock_service')->getStockChecker();
    $locations = $checker->getLocationList(TRUE);
    foreach ($locations as $location_id => $location) {
      $checker->updateLocationStockLevel($location_id, $entity_id);
    }
  }

}
