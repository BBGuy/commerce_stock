<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldWidget;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\ContextCreatorTrait;
use Drupal\commerce_stock\StockServiceManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'commerce_stock_level' widget.
 *
 * @Deprecated: We have now a dedicated widget per use case.
 *
 * @see https://www.drupal.org/project/commerce_stock/issues/2931754
 *
 * @FieldWidget(
 *   id = "commerce_stock_level_simple",
 *   module = "commerce_stock_field",
 *   label = @Translation("Deprecated: Will be removed soon."),
 *   field_types = {
 *     "commerce_stock_level"
 *   }
 * )
 */
class SimpleStockLevelWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  use ContextCreatorTrait;
  use MessengerTrait;

  /**
   * The Stock Service Manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManager
   */
  protected $stockServiceManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    StockServiceManager $simple_stock_manager
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->stockServiceManager = $simple_stock_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('commerce_stock.service_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'transaction_note' => FALSE,
      'entry_system' => 'simple',
      'context_fallback' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * Submits the form.
   */
  public function closeForm($form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->messenger()->addMessage(t('Updated the stock.'));
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Deprecated: This widget is deprecated and will be removed soon. Please choose another widget.');
    $summary[] = $this->t('Entry system: @entry_system', ['@entry_system' => $this->getSetting('entry_system')]);
    if ($this->getSetting('entry_system') != 'transactions') {
      $summary[] = $this->t('Transaction note: @transaction_note', ['@transaction_note' => $this->getSetting('transaction_note') ? 'Yes' : 'No']);
      $summary[] = $this->t('context fallback: @context_fallback', ['@context_fallback' => $this->getSetting('context_fallback') ? 'Yes' : 'No']);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['deprecation_notiz'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Deprecated: This widget is deprecated and will be removed soon. Please choose another widget.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $field = $items->first();
    $entity = $items->getEntity();

    if (!($entity instanceof PurchasableEntityInterface)) {
      // No stock if this is not a purchasable entity.
      return [];
    }
    if ($entity->isNew()) {
      // We can not work with entities before they are fully created.
      return [];
    }

    // We currently only support the Local stock service.
    $stockServiceManager = $this->stockServiceManager;
    $stock_service_name = $stockServiceManager->getService($entity)->getName();
    // @todo - service should be able can determine if it needs an interface.
    if ($stock_service_name != 'Local stock') {
      // Return an empty array if service is not supported.
      return [];
    }

    // If not a valid context.
    try {
      $this->getContext($entity);
    }
    catch (\Exception $e) {
      // If context fallback is not set.
      if (!$this->getSetting('context_fallback')) {
        // Return an empty form.
        return [];
      }
    }

    // Get the available stock level.
    $level = $field->available_stock;

    $entry_system = $this->getSetting('entry_system');
    $element['#type'] = 'fieldgroup';
    $element['#attributes'] = ['class' => ['stock-level-field']];
    $element['#title'] = $this->t('Stock (deprecated)');

    // Set the entry system so we know how to set the value.
    // @see StockLevel::setValue().
    $element['entry_system'] = [
      '#type' => 'value',
      '#value' => $entry_system,
    ];
    if (empty($entity->id())) {
      // We don't have a product ID yet.
      $element['#description'] = [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#value' => $this->t('In order to set the stock level you need to save the product first!'),
      ];
      $element['#disabled'] = TRUE;
    }
    else {
      $element['stocked_entity'] = [
        '#type' => 'value',
        '#value' => $entity,
      ];
      if ($entry_system == 'simple') {
        $element['stock_level'] = [
          '#title' => $this->t('Absolute stock level settings'),
          '#description' => $this->t('Sets the stock level. Current stock level: @stock_level. Note: Under the hood we create a transaction. Setting the absolute stock level may end in unexpected results. Learn more about transaction based inventory management in the docs.', ['@stock_level' => $level]),
          '#type' => 'number',
          '#min' => 0,
          '#step' => 1,
          // We don't use zero as default, because its a valid value and would reset
          // the stock level to 0.
          '#default_value' => NULL,
          '#size' => 7,
        ];
      }
      elseif ($entry_system == 'basic') {
        $element['adjustment'] = [
          '#title' => $this->t('Stock level adjustment'),
          '#description' => $this->t('A positive number will add stock, a negative number will remove stock. Current stock level: @stock_level', ['@stock_level' => $level]),
          '#type' => 'number',
          '#step' => 1,
          '#default_value' => 0,
          '#size' => 7,
        ];
      }
      elseif ($entry_system == 'transactions') {
        $element['stock_level_title'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Current stock level: @stock_level', ['@stock_level' => $level]),
        ];
        $link = Link::createFromRoute(
          $this->t('New transaction'),
          'commerce_stock_ui.stock_transactions2',
          ['commerce_product_v_id' => $entity->id()],
          ['attributes' => ['target' => '_blank']]
        )->toString();
        $element['stock_transactions_form_link'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Please use the @transaction form to create any stock transactions.', ['@transaction' => $link]),
        ];
      }
      if ($this->getSetting('transaction_note')) {
        $element['stock_transaction_note'] = [
          '#title' => $this->t('Transaction note'),
          '#description' => $this->t('Add a note to this transaction.'),
          '#type' => 'textfield',
          '#default_value' => '',
          '#size' => 50,
          '#maxlength' => 255,
        ];
      }
      $element['deprecation_notiz'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Deprecated: This widget is deprecated and will be removed soon. Please choose another widget.'),
      ];
    }

    return $element;
  }

  /**
   * Simple stock form - Used to update the stock level.
   *
   * @todo: This is not go live ready code,
   */
  public function validateSimple($element, FormStateInterface $form_state) {
    if (!is_numeric($element['#value'])) {
      $form_state->setError($element, $this->t('Stock must be a number.'));

      return;
    }
    // @todo Needs to mark element as needing updating? Updated qty??
  }

  /**
   * Validates a basic stock field widget form.
   */
  public function validateBasic($element, FormStateInterface $form_state) {
    // @to do.
    return TRUE;
  }

  /**
   * Submits the form.
   */
  public function submitAll(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage($this->t('Updated stock!'));
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(
    array $values,
    array $form,
    FormStateInterface $form_state
  ) {
    if (isset($values[0]['stock_level'])) {
      if (empty($values[0]['stock_level']) && $values[0]['stock_level'] !== "0") {
        $values[0]['adjustment'] = NULL;

        return $values;
      }
      $new_level = $values[0]['stock_level'];
      $current_level = $this->stockServiceManager->getStockLevel($values[0]['stocked_entity']);
      $values[0]['adjustment'] = $new_level - $current_level;

      return $values;
    }

    return $values;
  }

}
