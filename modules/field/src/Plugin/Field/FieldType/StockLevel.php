<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Plugin implementation of the 'commerce_stock_field' field type.
 *
 * @FieldType(
 *   id = "commerce_stock_level",
 *   label = @Translation("Stock level"),
 *   module = "commerce_stock_field",
 *   description = @Translation("Stock level"),
 *   default_widget = "commerce_stock_level_simple",
 *   default_formatter = "commerce_stock_level_simple"
 * )
 */
class StockLevel extends FieldItemBase {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManager
   */
  protected $stockServiceManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    $this->stockServiceManager = \Drupal::service('commerce_stock.service_manager');
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    // We don't need storage but as computed fields are not properly implemented
    // We will use a dummy column that should be ignored.
    // @see https://www.drupal.org/node/2392845.
    return [
      'columns' => [
        'value' => [
          'type' => 'numeric',
          'size' => 'normal',
          'precision' => 10,
          'scale' => 2,
          'not null' => FALSE,
        ],
      ],
    ];
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
    // @todo What's the difference/utility between both fields?
    $properties['value'] = DataDefinition::create('float')
      ->setLabel(t('Available stock'));
    $properties['available_stock'] = DataDefinition::create('float')
      ->setLabel(t('Available stock'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\commerce_stock_field\StockLevelProcessor')
      ->setSetting('stock level', 'summary');

    return $properties;
  }

  /**************************  TESTING *********************************/

  /**
   * This updates the stock based on parameters set by the stock widget.
   */
  public function setValue($values, $notify = TRUE) {
    // @todo Figure out why sometimes this is called twice.
    static $called = FALSE;
    if ($called) {
      return;
    }
    $called = TRUE;

    if (!empty($this->getEntity())) {
      $entity = $this->getEntity();
      $transaction_qty = 0;

      // Supports absolute values being passed in directly, i.e. programmatically.
      if (!is_array($values)) {
        $values = ['stock' => ['value' => $values]];
      }
      if (empty($values['stock']['entry_system'])) {
        $transaction_qty = (int) $values['stock']['value'];
      }

      // Or supports a field widget entry system.
      else {
        switch ($values['stock']['entry_system']) {
          case 'simple':
            $new_level = $values['stock']['value'];
            $level = $this->stockServiceManager->getStockLevel($entity);
            $transaction_qty = $new_level - $level;
            break;

          case 'basic':
            $transaction_qty = (int) $values['stock']['adjustment'];
            break;
        }
      }

      if ($transaction_qty) {
        $transaction_type = ($transaction_qty > 0) ? TRANSACTION_TYPE_STOCK_IN : TRANSACTION_TYPE_STOCK_OUT;
        // @todo Add zone and location to form.
        $location_id = $this->stockServiceManager->getPrimaryTransactionLocation($entity, $transaction_qty);
        $zone = '';
        // @todo Implement unit_cost?
        $unit_cost = NULL;
        $transaction_note = isset($values['stock']['stock_transaction_note']) ? $values['stock']['stock_transaction_note'] : 'stock level set or updated by field';
        $metadata = ['data' => ['message' => $transaction_note]];
        $this->stockServiceManager->createTransaction($entity, $location_id, $zone, $transaction_qty, $unit_cost, $transaction_type, $metadata);
      }
    }
  }

}
