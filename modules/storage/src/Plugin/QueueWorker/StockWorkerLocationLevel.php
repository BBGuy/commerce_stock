<?php

namespace Drupal\commerce_stock_s\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\commerce_stock_s\StockStorageAPI;

/**
 * A Commerce Stock worker.
 *
 * @QueueWorker(
 *   id = "cron_stock_update_location_level",
 *   title = @Translation("Commerce Stock update location level"),
 *   cron = {"time" = 10}
 * )
 */
class StockWorkerLocationLevel extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Get the Stock Storage API.
    $stock_api = new StockStorageAPI();
    // Get the active locations.
    $locations = $stock_api->getLocationList(TRUE);
    // Update.
    foreach ($locations as $location_id => $location) {
      // Update the stock level.
      $stock_api->updateProductInventoryLocationLevel($location_id, $data);
    }
  }

}
