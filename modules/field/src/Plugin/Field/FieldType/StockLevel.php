<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldType;

use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'commerce_stock_field' field type.
 *
 * @FieldType(
 *   id = "commerce_stock_level",
 *   label = @Translation("Stock level"),
 *   module = "commerce_stock_field",
 *   description = @Translation("Stock level"),
 *   default_widget = "commerce_stock_level_simple_transaction",
 *   default_formatter = "commerce_stock_level_simple",
 *   cardinality = 1,
 * )
 */
class StockLevel extends FieldItemBase {

  /**
   * {@inheritdoc}
   *
   * Originally we had to define a real db field, because cores implementation
   * of computed fields was brittle. During development of the module, we
   * found, that we can "misuse" this to provide the possibility to enter
   * initial stock values for newly created product variations.
   *
   * Currently we use the column 'value' for exactly this one purpose. Don't get
   * fooled by this. The calculation of the stock level is transaction based.
   * The transactions have their own table.
   */
  public static function schema(
    FieldStorageDefinitionInterface $field_definition
  ) {
    return [
      'columns' => [
        'value' => [
          'type' => 'numeric',
          'size' => 'normal',
          'precision' => 19,
          'scale' => 4,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(
    FieldStorageDefinitionInterface $field_definition
  ) {
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

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL;
  }

  /**
   * @inheritdoc
   *
   * This updates the stock based on parameters set by the stock widget.
   *
   * For computed fields we didn't find a chance to trigger the transaction,
   * other than in ::setValue(). ::postSave() is not called for computed fields.
   *
   * If you pass in a single value programmatically, note that we do not support
   * the setting of a absolute stock levels here. We assume a stock adjustment
   * if we get a singe value here. As usual a negative value decreases the
   * stock level and a positive value increases the stock level.
   *
   * @throws \InvalidArgumentException
   *   In case of a invalid stock level value.
   */
  public function setValue($values, $notify = TRUE) {
    // To prevent multiple stock transactions, we need to track the processing.
    static $processed = [];

    // Supports absolute values being passed in directly, i.e.
    // programmatically.
    if (!is_array($values)) {
      $value = filter_var($values, FILTER_VALIDATE_FLOAT);
      if ($value) {
        $values = ['adjustment' => $value];
      }
      else {
        throw new \InvalidArgumentException('Values passed to the commerce stock level field must be floats');
      }
    }

    if (!empty($this->getEntity())) {
      $entity = $this->getEntity();
      if (empty($entity->id())) {
        return;
      }
      // This is essential to prevent triggering of multiple transactions.
      if (isset($processed[$entity->getEntityTypeId() . $entity->id()])) {
        return;
      }
      $processed[$entity->getEntityTypeId() . $entity->id()] = TRUE;

      $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
      $transaction_qty = empty($values['adjustment']) ? 0 : $values['adjustment'];

      // Some basic validation and type coercion.
      $transaction_qty = filter_var((float) ($transaction_qty), FILTER_VALIDATE_FLOAT);

      if ($transaction_qty) {
        $transaction_type = ($transaction_qty > 0) ? StockTransactionsInterface::STOCK_IN : StockTransactionsInterface::STOCK_OUT;
        // @todo Add zone and location to form.
        /** @var \Drupal\commerce_stock\StockLocationInterface $location */
        $location = $stockServiceManager->getTransactionLocation($stockServiceManager->getContext($entity), $entity, $transaction_qty);
        if (empty($location)) {
          // If we have no location, something isn't properly configured.
          throw new \RuntimeException('The StockServiceManager didn\'t return a location. Did you setup stock correctly?');
        }
        $zone = empty($values['zone']) ? '' : $values['zone'];
        $unit_cost = NULL;
        if (isset($values['unit_cost']['amount'])) {
          $unit_cost = filter_var((float) ($values['unit_cost']['amount']), FILTER_VALIDATE_FLOAT);
          $unit_cost ?: NULL;
        };
        $currency_code = empty($values['unit_cost']['currency_code']) ? NULL : $values['unit_cost']['currency_code'];
        $transaction_note = empty($values['stock_transaction_note']) ? '' : $values['stock_transaction_note'];
        $metadata = ['data' => ['message' => $transaction_note]];
        if (!empty($values['user_id'])) {
          $metadata['related_uid'] = $values['user_id'];
        }
        else {
          $metadata['related_uid'] = \Drupal::currentUser()->id();
        }
        $stockServiceManager->createTransaction($entity, $location->getId(), $zone, $transaction_qty, (float) $unit_cost, $currency_code, $transaction_type, $metadata);
      }
    }
  }

}
