<?php

namespace Drupal\field_stock\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;


/**
 * Plugin implementation of the 'field_stock_widget' widget.
 *
 * @FieldWidget(
 *   id = "field_stock_widget",
 *   module = "field_stock",
 *   label = @Translation("Stock Widget"),
 *   field_types = {
 *     "field_stock_level"
 *   }
 * )
 */
class StockWidget extends WidgetBase {


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'transaction_note' => FALSE,
      'entry_system' => 'simple',
    ] + parent::defaultSettings();
  }

   /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Entry system: @entry_system', ['@entry_system' => $this->getSetting('entry_system')]);
    $summary[] = t('Eransaction note: @transaction_note', ['@transaction_note' => $this->getSetting('transaction_note')? 'Yes' : 'No']);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['entry_system'] = [
      '#type' => 'select',
      '#title' => $this->t('Entry system'),
      '#options' => [
        'simple' => $this->t('Simple (absolute stock level)'),
        'basic' => $this->t('Basic transactions'),
        'transactions' => $this->t('Use transactions (read only form)'),
      ],
      '#default_value' =>  $this->getSetting('entry_system'),
    ];

    $element['transaction_note'] = [
      '#type' => 'checkbox',
      '#title' => t('Provide note'),
      '#default_value' => $this->getSetting('transaction_note'),
      '#description' => t('Provid an input box for a transaction note.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Get the stock field.
    $field = $items->first();
    // Get the product entity.
    $entity = $items->getEntity();
    // Make sure entity is a product variation.
    if ($entity instanceof ProductVariationInterface) {
      // Get the available stock level.
      $level = $field->available_stock;
    }
    else {
      // No stock if this is not a product variation.
      $level = 0;
      return [];
    }
    $elements = [];


    // Check the Stock Entry system chosen.
    $entry_system = $this->getSetting('entry_system');

    $elements['stock'] = [
      '#type' => 'fieldgroup',
      '#title' => t('Stock'),
    ];

    // Common.
    $elements['stock']['stocked_variation_id'] = [
      '#type' => 'value', '#value' => $entity->id(),
       '#element_validate' => [
         [$this, 'validateSimpleId'],
       ],
    ];

    // Simple entry system = One edit box for stock level
    if ($entry_system == 'simple') {

     $elements['stock']['value'] = [
       '#description' => t('Available stock.'),
       '#type' => 'textfield',
       '#default_value' =>  $level,
       '#size' => 10,
       '#maxlength' => 12,
       '#element_validate' => [
         [$this, 'validateSimple'],
       ],
     ];
    }
    elseif ($entry_system == 'basic') {
      // A lable showing the stock.
      $elements['stock']['stock_label'] = [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#value' => t('Stock level: @stock_level', ['@stock_level' => $level]),
      ];
      // An entry box for entring the a transaction amount.
      $elements['stock']['adjustment'] = [
        '#title' => t('Transaction'),
        '#description' => t('Valid options [number], +[number], -[number]. [number] for a new stock level, +[number] to add stock -[number] to remove stock. e.g. "5" we have 5 in stock, "+2" add 2 to stock or "-1" remove 1 from stock.'),
        '#type' => 'textfield',
        '#default_value' =>  '',
        '#size' => 7,
        '#maxlength' => 7,
        '#element_validate' => [
          array($this, 'validateBasic'),
        ],
      ];
    }
    elseif ($entry_system == 'transactions') {
      // A lable showing the stock.
      $elements['stock']['stock_label'] = [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#value' => t('Stock level: @stock_level', ['@stock_level' => $level]),
      ];
      $elements['stock']['stock_transactions_label'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => t('Please use the @transactions_page page for creating transactions.', ['@transactions_page' => '"To be developed"']),
      ];
    }

    // Add a transaction note if enabled.
    if ($this->getSetting('transaction_note') && ($entry_system != 'transactions')) {
     $elements['stock']['stock_transaction_note'] = [
       '#title' => t('Transaction note'),
       '#description' => t('Type in a note about this transaction.'),
       '#type' => 'textfield',
       '#default_value' =>  '',
       '#size' => 20,
     ];

    }

    return $elements;
  }


  private function multiKeyExists(array $arr, $key) {
          if (array_key_exists($key, $arr)) {
              return $arr[$key];
          }
          foreach ($arr as $element) {
              if (is_array($element)) {
                  if ($found_element = $this->multiKeyExists($element, $key)) {
                      return $found_element;
                  }
              }
          }
          return false;
      }

    /**
     * Save the Entity ID for stock update.
     *
     * This is a hack: As I don't know to get the relevent entity in the element
     * submit for the stock value field. We will store the ID.
     * @todo: This is not go live ready code,
     */
    public function validateSimpleId($element, FormStateInterface $form_state) {
      $variation_id = $element['#value'];
      $commerce_stock_widget_values = &drupal_static('commerce_stock_widget_values', []);
      $commerce_stock_widget_values['variation_id'] = $variation_id;
    }

    /**
     * Simple stock form - Used to update the stock level.
     *
     * @todo: This is not go live ready code,
     */
    public function validateSimple($element, FormStateInterface $form_state) {

      if (!is_numeric($element['#value'])) {
        $form_state->setError($element, t('Stock must be a number.'));
        return;
      }
      $values = $form_state->getValues();
      // Make sure we got variations.
      if (!isset($values['variations'])) {
        return;
      }

      $commerce_stock_widget_values = &drupal_static('commerce_stock_widget_values', []);

      // Get $variation_id using a hack (no live ready).
      $variation_id = $commerce_stock_widget_values['variation_id']['inline_entity_form']['entities'];

      // Init variable in case we can't find it.
      $transaction_note = FALSE;

      // Find the entity deap inside .
      $entities = $values['variations']['form']['inline_entity_form']['entities'];
      foreach ($entities as $entity) {
        $tmp_id = $this->multiKeyExists($entity, 'stocked_variation_id');
        if ($tmp_id == $variation_id) {
          $transaction_note = $this->multiKeyExists($entity, 'stock_transaction_note');
        }
      }

      $commerce_stock_widget_values['update_type'] = 'simple';
      $commerce_stock_widget_values['variation_id'] = $variation_id;
      $commerce_stock_widget_values['stock_level'] = $element['#value'];
      // Do we have a note.
      $commerce_stock_widget_values['transaction_note'] = $transaction_note;
      // Mark as need updating.
      $commerce_stock_widget_values['update'] = TRUE;
    }

    public function validateBasic($element, FormStateInterface $form_state) {
      // @to do.
      return true;
    }


  public static function closeForm($form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message('updated STOCK');
  }

  public function submitAll(array &$form, FormStateInterface $form_state) {
    drupal_set_message('updated STOCK!!');

  }
}
