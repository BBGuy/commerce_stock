<?php

namespace Drupal\commerce_stock\Plugin\StockEvents;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface;
use Drupal\commerce_stock\StockLocationInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Core Stock Events.
 *
 * @StockEvents(
 *   id = "core_stock_events",
 *   description = @Translation("Core stock Events."),
 * )
 */
class CoreStockEvents extends CoreStockEventsBase {

  /**
   * {@inheritdoc}
   */
  public function stockEvent(
    Context $context,
    PurchasableEntityInterface $entity,
    StockEventTypeInterface $stock_event_type,
    $quantity,
    StockLocationInterface $location,
    $transaction_type,
    Order $order
  ) {

    $config = \Drupal::configFactory()->get('commerce_stock.core_stock_events');

    // Check if event should be handled.
    $order_placed_events = ['commerce_stock_order_place'];
    $order_update_events = [
      'commerce_stock_order_update',
      'commerce_stock_order_item_update',
    ];
    $order_delete_events = [
      'commerce_stock_order_cancel',
      'commerce_stock_order_delete',
      'commerce_stock_order_item_delete',
    ];
    // Cancel if event type is not enabled.
    if ((in_array($stock_event_type->getPluginId(), $order_placed_events)) && !$config->get('core_stock_events_order_place')) {
      return FALSE;
    }
    elseif ((in_array($stock_event_type->getPluginId(), $order_update_events)) && !$config->get('core_stock_events_order_updates')) {
      return FALSE;
    }
    elseif ((in_array($stock_event_type->getPluginId(), $order_delete_events)) && !$config->get('core_stock_events_order_cancel')) {
      return FALSE;
    }

    $metadata = $this->getMetadata($order, $stock_event_type);

    // Get the stock service.
    $stockService = \Drupal::service('commerce_stock.service_manager')
      ->getService($entity);
    // Use the stock updater to create the transaction.
    $transaction_id = $stockService->getStockUpdater()
      ->createTransaction($entity, $location->getId(), '', $quantity, NULL, $currency_code = NULL, $transaction_type, $metadata);
    // Return the transaction ID.
    return $transaction_id;
  }

  /**
   * {@inheritdoc}
   */
  public function configFormOptions() {

    $config = \Drupal::configFactory()->get('commerce_stock.core_stock_events');

    $form_options['core_stock_events_order_place'] = [
      '#type' => 'checkbox',
      '#title' => t('Reserve stock on order complete'),
      '#default_value' => $config->get('core_stock_events_order_place'),
    ];
    $form_options['core_stock_events_order_cancel'] = [
      '#type' => 'checkbox',
      '#title' => t('Automatically return stock on cancel'),
      '#default_value' => $config->get('core_stock_events_order_cancel'),
    ];
    $form_options['core_stock_events_order_updates'] = [
      '#type' => 'checkbox',
      '#title' => t('Adjust stock on order updates (after the order was completed)'),
      '#default_value' => $config->get('core_stock_events_order_updates'),
    ];
    return $form_options;
  }

  /**
   * {@inheritdoc}
   */
  public function saveConfigFormOptions(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = \Drupal::configFactory()
      ->getEditable('commerce_stock.core_stock_events');
    $config->set('core_stock_events_order_place', $values['core_stock_events_order_place']);
    $config->set('core_stock_events_order_cancel', $values['core_stock_events_order_cancel']);
    $config->set('core_stock_events_order_updates', $values['core_stock_events_order_updates']);

    $config->save();
  }

}
