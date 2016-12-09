<?php

namespace Drupal\field_stock\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_stock' field type.
 *
 * @FieldType(
 *   id = "field_stock_level",
 *   label = @Translation("Stock Level"),
 *   module = "field_stock",
 *   description = @Translation("Stock level."),
 *   default_widget = "field_stock_widget",
 *   default_formatter = "field_stock_level_simple"
 * )
 */
class StockItem extends FieldItemBase {



  /********************************************************************************/

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    // We don't need storage but as computed fields are not properly implamnted
    // We will use a dummy column that should be ignored.
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'numeric',
          'size' => 'normal',
          'precision' => 10,
          'scale' => 2,
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['value'] = DataDefinition::create('float')
      ->setLabel(t('Available stock'));

    $properties['available_stock'] = DataDefinition::create('float')
      ->setLabel(t('Availab Stock'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\field_stock\StockFieldProcessing')
      ->setSetting('stock level', 'summary');

    return $properties;
  }

  /**************************  TESTING *********************************/

  /**
   * This updates the stock based on paremeters set by the stock widget.
   */
  public function setValue($values, $notify = TRUE) {
    parent::setValue($values, $notify);
    // Get the values set by the widget.
    $commerce_stock_widget_values = &drupal_static('commerce_stock_widget_values', []);
    if (isset($commerce_stock_widget_values['update']) && $commerce_stock_widget_values['update']) {
      // Simple widget.
      if ($commerce_stock_widget_values['update_type'] == 'simple') {
        // Get the new requested details.
        $variation_id = $commerce_stock_widget_values['variation_id'];
        // Make sure we have a product.
        if (!empty($variation_id)) {
          $new_level = $commerce_stock_widget_values['stock_level'];
          $transaction_note = $commerce_stock_widget_values['transaction_note'];
          // Get the available Stock for the product variation.
          /** @var ProductVariationStorage $variation_storage */
          $variation_storage = \Drupal::service('entity_type.manager')
            ->getStorage('commerce_product_variation');
          $product_variation = $variation_storage->load($variation_id);
          /** @var StockManager $stockManager */
          $stockManager = \Drupal::service('commerce.stock_manager');
          $level = $stockManager->getStockLevel($product_variation);
          // Work out the adjustment.
          $transaction_qty = $new_level - $level;
          if ($transaction_qty > 0) {
            $transaction_type = TRANSACTION_TYPE_STOCK_IN;
          } elseif ($transaction_qty < 0) {
            $transaction_type = TRANSACTION_TYPE_STOCK_OUT;
          }

          if ($transaction_qty != 0) {
            // @todo - add note.
            $metadata = ['data' => ['message' => $transaction_note]];
            $unit_cost = NULL;
            $zone = '';
            // Get the $location_id.
            $location_id = $stockManager->getPrimaryTransactionLocation($product_variation, $transaction_qty);
            $stockManager->createTransaction($product_variation, $location_id, $zone, $transaction_qty, $unit_cost, $transaction_type, $metadata);
          }
        }
      }
      $commerce_stock_widget_values['update'] = FALSE;
    }

  }

  // /**
  //   * {@inheritdoc}
  //   */
  //  protected function writePropertyValue($property_name, $value) {
  //    parent::writePropertyValue($property_name, $value);
  //  }.
}
