<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldWidget;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_stock\ContextCreatorTrait;
use Drupal\commerce_stock\StockServiceManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base structure for commerce stock level widgets.
 */
abstract class StockLevelWidgetBase extends WidgetBase implements ContainerFactoryPluginInterface {

  use ContextCreatorTrait;

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
    StockServiceManager $stock_service_manager
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->stockServiceManager = $stock_service_manager;
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
      'custom_transaction_note' => FALSE,
      'default_transaction_note' => t('Transaction issued by stock level field.'),
      'step' => '1',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('step') == 1) {
      $summary[] = $this->t('Decimal stock levels not allowed');
    }
    else {
      $summary[] = $this->t('Decimal stock levels allowed');
      $summary[] = $this->t('Step: @step', ['@step' => $this->getSetting('step')]);
    }

    $summary[] = $this->t('Default transaction note: @transaction_note', ['@transaction_note' => $this->getSetting('default_transaction_note')]);
    $summary[] = $this->t('Custom transaction note @allowed allowed.', ['@allowed' => $this->getSetting('custom_transaction_note') ? '' : 'not']);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element = [];
    if ($this->hasHelpText()) {
      $element = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->getHelpText(),
      ];
    }

    $element['default_transaction_note'] = [
      '#title' => $this->t('Default transaction note'),
      '#description' => $this->t('Use this as default transaction note.'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('default_transaction_note'),
      '#size' => 50,
      '#maxlength' => 255,
    ];
    $element['custom_transaction_note'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow custom note per transaction.'),
      '#default_value' => $this->getSetting('custom_transaction_note'),
    ];
    // Shameless borrowed from commerce quantity field.
    $step = $this->getSetting('step');
    $element['#element_validate'][] = [
      get_class($this),
      'validateSettingsForm',
    ];
    $element['allow_decimal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow decimal quantities'),
      '#default_value' => $step != '1',
    ];
    $element['step'] = [
      '#type' => 'select',
      '#title' => $this->t('Step'),
      '#description' => $this->t('Only stock levels that are multiples of the selected step will be allowed. Maximum precision is 2 (0.01).'),
      '#default_value' => $step != '1' ? $step : '0.1',
      '#options' => [
        '0.1' => '0.1',
        '0.01' => '0.01',
        '0.25' => '0.25',
        '0.5' => '0.5',
        '0.05' => '0.05',
      ],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][allow_decimal]"]' => ['checked' => TRUE],
        ],
      ],
      '#required' => TRUE,
    ];
    return $element;
  }

  /**
   * Validates the settings form.
   *
   * @param array $element
   *   The settings form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateSettingsForm(
    array $element,
    FormStateInterface $form_state
  ) {
    $value = $form_state->getValue($element['#parents']);
    if (empty($value['allow_decimal'])) {
      $value['step'] = '1';
    }
    unset($value['allow_decimal']);
    $form_state->setValue($element['#parents'], $value);
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
    /** @var \Drupal\commerce_stock\StockServiceInterface $stock_service */
    $stock_service = $this->stockServiceManager->getService($entity);
    if ($stock_service->getId() === 'always_in_stock') {
      // Return an empty array if service is not supported.
      return [];
    }

    $element['#type'] = 'fieldgroup';
    $element['#attributes'] = ['class' => ['stock-level-field']];

    // If not a valid context.
    if (!$this->isValidContext($entity)) {
      $element['#description'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('We have no valid data to process a stock transaction. This may happen if we cannot determine a store to which the entity belongs to.'),
      ];
      // In case we have no valid context and a new entity, we probably have
      // a inline form at hand.
      if ($entity->isNew) {
        $element['#description']['#value'] = $this->t('We have no valid data to process a stock transaction. Probably because you use an inline form and the parent entity is not saved yet. In such a case you first need to create and save the entity. On the edit form you should be able to set the stock level.');
      }
      return $element;
    }

    // Get the available stock level.
    $level = $field->get('available_stock')->getValue();

    $element['stocked_entity'] = [
      '#type' => 'value',
      '#value' => $entity,
    ];
    $element['adjustment'] = [
      '#title' => $this->t('Stock level adjustment'),
      '#description' => $this->t('A positive number will add stock, a negative number will remove stock.'),
      '#type' => 'number',
      '#step' => $this->getSetting('step'),
      '#default_value' => 0,
      '#size' => 7,
      '#weight' => 20,
    ];
    $element['current level'] = [
      '#markup' => $this->t('Current stock level: @stock_level', ['@stock_level' => $level]),
      '#prefix' => '<div class="stock-level-field-stock-level">',
      '#suffix' => '</div>',
      '#type' => 'markup',
      '#weight' => 10,
    ];
    $custom_note_allowed = $this->getSetting('custom_transaction_note');
    $element['stock_transaction_note'] = [
      '#title' => $this->t('Transaction note'),
      '#description' => $custom_note_allowed ? $this->t('Add a note to this transaction.') : $this->t('Default note for transactions. Configurable in the field widget settings.'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('default_transaction_note'),
      '#size' => 50,
      '#maxlength' => 255,
      '#disabled' => !$custom_note_allowed,
      '#weight' => 50,
    ];

    return $element;
  }

  /**
   * Provides the help text to explain the widgets use case. Used in settings
   * form.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   The help text or NULL.
   */
  abstract protected function getHelpText();

  /**
   * Whether a help text is available.
   *
   * @return bool
   *   TRUE if a help text is availabel, FALSE otherwise.
   */
  private function hasHelpText() {
    return !empty($this->getHelpText());
  }

}
