<?php

namespace Drupal\field_stock\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for dblog routes.
 */
class FieldStockController extends ControllerBase {

  /**
   * A simple page to explain to the developer what to do.
   */
  public function description() {
    return array(
      '#markup' => t(
        "The Stock Field"),
    );
  }

}
