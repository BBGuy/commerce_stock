<?php

namespace Drupal\commerce_stock\Plugin\StockEvents;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\Plugin\StockEventsInterface;
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
  public function stockEvent(Context $context, PurchasableEntityInterface $entity, $stockEvent, $quantity, StockLocationInterface $location, $transaction_type, array $metadata) {
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

    $form_options['core_stock_events_order_complete_event_type'] = [
      '#type' => 'select',
      '#title' => t('Order complete transaction'),
      '#description' => t('What event should trigger the complete transaction.'),
      '#options' => [
        'disabled' => "Don't create a transaction for completed orders",
        'placed' => 'Create a transaction when order is Placed',
        'completed' => 'Create a transaction when order is Completed/Fulfilled',
      ],
      '#default_value' => $config->get('core_stock_events_order_complete_event_type') ?? 'placed',
    ];
    $form_options['core_stock_events_order_cancel'] = [
      '#type' => 'checkbox',
      '#title' => t('Automatically return stock on cancel'),
      '#default_value' => $config->get('core_stock_events_order_cancel') ?? FALSE,
    ];
    $form_options['core_stock_events_order_updates'] = [
      '#type' => 'checkbox',
      '#title' => t('Adjust stock on order updates (after the order was completed)'),
      '#default_value' => $config->get('core_stock_events_order_updates') ?? FALSE,
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
    $config->set('core_stock_events_order_complete_event_type', $values['core_stock_events_order_complete_event_type']);
    $config->set('core_stock_events_order_cancel', $values['core_stock_events_order_cancel']);
    $config->set('core_stock_events_order_updates', $values['core_stock_events_order_updates']);

    $config->save();
  }

  /**
   * To ensure backwards compatibility we introduced StockEventTypes plugins
   * without changing the StockEventsInterface. This functions maps the
   * interface constants to StockEventTypes.
   *
   * @param int $event_type_id
   *   The StockEventsInterface interface constant.
   *
   * @return string
   *   The StockEventType id or FALSE if not exists.
   */
  public static function mapStockEventTypes($event_type_id) {
    $map = self::getEventTypeMap();
    $result = array_key_exists($event_type_id, $map) ? $map[$event_type_id] : FALSE;
    return $result;
  }

  /**
   * To ensure backwards compatibility we introduced StockEventTypes plugins
   * without changing the StockEventsInterface. This functions maps the
   * interface constants to StockEventTypes.
   *
   * @param string $stock_event_type_id
   *   The StockEventType id.
   *
   * @return int
   *   The StockEventsInterface interface constant or FALSE if it not exists.
   */
  public static function mapStockEventIds($stock_event_type_id) {
    $map = array_flip(self::getEventTypeMap());
    $result = array_key_exists($stock_event_type_id, $map) ? $map[$stock_event_type_id] : FALSE;
    return $result;
  }

  /**
   * Get the map of StockEvenTypes.
   */
  private static function getEventTypeMap() {
    return $map = [
      StockEventsInterface::ORDER_PLACE_EVENT => 'commerce_stock_order_place',
      StockEventsInterface::ORDER_UPDATE_EVENT => 'commerce_stock_order_update',
      StockEventsInterface::ORDER_CANCEL_EVENT => 'commerce_stock_order_cancel',
      StockEventsInterface::ORDER_DELET_EVENT => 'commerce_stock_order_delete',
      StockEventsInterface::ORDER_ITEM_DELETE_EVENT => 'commerce_stock_order_item_delete',
      StockEventsInterface::ORDER_ITEM_UPDATE_EVENT => 'commerce_stock_order_item_update',
    ];
  }

}
