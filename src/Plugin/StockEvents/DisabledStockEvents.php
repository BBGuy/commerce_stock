<?php

namespace Drupal\commerce_stock\Plugin\StockEvents;

use Drupal\Component\Plugin\PluginBase;
use Drupal\commerce_stock\Plugin\StockEventsInterface;
use Drupal\commerce\PurchasableEntityInterface;



/**
 * Core Stock Events.
 *
 *
 * @StockEvents(
 *   id = "disabled_stock_events",
 *   description = @Translation("Disabled all stock events."),
 * )
 */
class DisabledStockEvents extends PluginBase implements StockEventsInterface {

  /**
   * {@inheritdoc}
   */
  public function stockEvent($context, PurchasableEntityInterface $entity,
                             $stockEvent, $quantity, $location,
                             $transaction_type, $metadata) {

    // This does nothing.
    return NULL;
  }


  /**
   * {@inheritdoc}
   */
  public function configFormOptions() {
    // No configuration.
    $form['na'] = [
      '#type' => 'markup',
      '#markup' => t('No configuration options.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function SaveconfigFormOptions($from, $form_state) {
    // Nothing to do.
    return FALSE;
  }


}
