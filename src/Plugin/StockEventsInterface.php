<?php

namespace Drupal\commerce_stock\Plugin;

use Drupal\commerce\Context;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface;
use Drupal\commerce_stock\StockLocationInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Stock events plugins.
 */
interface StockEventsInterface extends PluginInspectionInterface {

  /**
   * A stock event with transaction details.
   *
   * The stock event gets both the details about the type of the event and the
   * transaction it should create.
   * It can simply create the transaction from the details provided, add logic
   * to check if the transaction is to be created or override the details
   * provided before creating the transaction.
   *
   * @param \Drupal\commerce\Context $context
   *   The context containing the customer & store.
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param \Drupal\commerce_stock\Plugin\Commerce\StockEventType\StockEventTypeInterface $stock_event_type
   *   The stock event type.
   * @param int $quantity
   *   The quantity.
   * @param \Drupal\commerce_stock\StockLocationInterface $location
   *   The stock location.
   * @param int $transaction_type
   *   The transaction type ID.
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The order that belongs to the transaction.
   *
   * @return int
   *   Return the ID of the transaction or FALSE if no transaction created.
   */
  public function stockEvent(Context $context, PurchasableEntityInterface $entity, StockEventTypeInterface $stock_event_type, $quantity, StockLocationInterface $location, $transaction_type, Order $order);

  /**
   * Return form elements holding the configuration options.
   */
  public function configFormOptions();

  /**
   * Save the configuration options.
   *
   * @param array $form
   *   The stock manager configuration form holding the option elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The stock manager configuration form state.
   */
  public function saveConfigFormOptions(array $form, FormStateInterface $form_state);

}
