<?php

namespace Drupal\commerce_stock_local\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Stock Location entities.
 */
class CommerceStockLocationViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
