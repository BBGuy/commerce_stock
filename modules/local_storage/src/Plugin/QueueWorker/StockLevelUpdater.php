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
    /** @var \Drupal\commerce_stock\StockUpdateInterface $updater */
    $updater = \Drupal::service('commerce_stock.local_stock_service')->getStockUpdater();
    /** @var \Drupal\commerce_stock\StockServiceConfigInterface $config */
    $config = \Drupal::service('commerce_stock.local_stock_service')->getConfiguration();

    // @ToDo Figure out how to get the entity instead of the id?.
    $locations = $config->getEnabledLocations($entity_id);
    /** @var \Drupal\commerce_stock\StockLocationInterface $location */
    foreach ($locations as $location) {
      // @ToDo This method is not defined in any interface.  So it is not guaranteed, that a StockUpdater has that method.
      // @see https://www.drupal.org/node/2842583
      $updater->updateLocationStockLevel($location->getId(), $entity_id);
    }
  }

}
