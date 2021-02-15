<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce\CommerceEntityViewsData;

/**
 * Provides Views data for Stock Location entities.
 */
class StockLocationViewsData extends CommerceEntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // @todo join related tables.

    return $data;
  }

}
