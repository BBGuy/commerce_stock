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
 */
class StockLevelUpdater extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $variation_id = $data;
    $updater = \Drupal::service('commerce_stock.local_stock_service')->getStockUpdater();
    $locations = $updater->getLocationList(TRUE);
    foreach ($locations as $location_id => $location) {
      $updater->updateLocationStockLevel($location_id, $variation_id);
    }
  }

}
