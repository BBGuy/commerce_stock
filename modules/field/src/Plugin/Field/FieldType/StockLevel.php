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
    parent::setValue($values, $notify);

    // @todo Figure out why sometimes this is called twice.
    static $called = FALSE;
    if ($called) {
      return;
    }
    $called = TRUE;

    $entity_id = empty($values['stock']['stocked_entity_id']) ? null : $values['stock']['stocked_entity_id'];
    if (!empty($entity_id)) {
      /** @var \Drupal\commerce\PurchasableEntityInterface $purchasable_entity */
      $purchasable_entity = $this->getEntity()->load($entity_id);
      $transaction_qty = 0;
      switch ($values['stock']['entry_system']) {
        case 'simple':
          $new_level = $values['stock']['value'];
          $level = $this->stockServiceManager->getStockLevel($purchasable_entity);
          $transaction_qty = $new_level - $level;
          break;

        case 'basic':
          $transaction_qty = (int) $values['stock']['adjustment'];
          break;
      }
      if ($transaction_qty) {
        $transaction_type = ($transaction_qty > 0) ? TRANSACTION_TYPE_STOCK_IN : TRANSACTION_TYPE_STOCK_OUT;
        // @todo Add zone and location to form.
        /** @var \Drupal\commerce_stock\StockLocationInterface $location */
        $location = $this->stockServiceManager->getPrimaryTransactionLocation($purchasable_entity, $transaction_qty);
        $zone = '';
        // @todo Implement unit_cost?
        $unit_cost = NULL;
        $transaction_note = empty($values['stock']['stock_transaction_note']) ? '' : $values['stock']['stock_transaction_note'];
        $metadata = ['data' => ['message' => $transaction_note]];
        $this->stockServiceManager->createTransaction($purchasable_entity, $location->getId() , $zone, $transaction_qty, $unit_cost, $transaction_type, $metadata);
      }
    }
  }

}
