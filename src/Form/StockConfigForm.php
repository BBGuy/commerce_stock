<?php

namespace Drupal\commerce_stock\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The stock configuration form.
 */
class StockConfigForm extends ConfigFormBase {

  /**
   * A list of purchasable entity types and bundles.
   *
   * @var array
   */
  protected $purchasableEntityTypes;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_stock.service_manager',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_stock_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);

    $entity_type_manager = \Drupal::service('entity_type.manager');
    $entity_type_bundle_info = \Drupal::service('entity_type.bundle.info');

    // Prepare the list of purchasable entity types and bundles.
    $entity_types = $entity_type_manager->getDefinitions();
    $purchasable_entity_types = array_filter($entity_types, function ($entity_type) {
      return $entity_type->isSubclassOf('\Drupal\commerce\PurchasableEntityInterface');
    });
    $purchasable_entity_types = array_map(function ($entity_type) {
      return $entity_type->getLabel();
    }, $purchasable_entity_types);
    foreach ($purchasable_entity_types as $type => $label) {
      $this->purchasableEntityTypes[$type] = [
        'label' => $label,
        'bundles' => [],
      ];
      foreach ($entity_type_bundle_info->getBundleInfo($type) as $bundle_id => $bundle_info) {
        $this->purchasableEntityTypes[$type]['bundles'][$bundle_id] = $bundle_info['label'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the default service.
    $config = $this->config('commerce_stock.service_manager');
    $default_service_id = $config->get('default_service_id');

    $stock_service_manager = \Drupal::service('commerce_stock.service_manager');
    $service_options = $stock_service_manager->listServiceIds();

    $form['service_manager'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Stock services'),
    ];

    $form['service_manager']['default_service_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Default service'),
      '#options' => $service_options,
      '#default_value' => $default_service_id,
    ];

    $form['service_manager']['services'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Services per entity type'),
    ];
    $service_options = array_merge(['use_default' => $this->t('- Use default -')], $service_options);
    foreach ($this->purchasableEntityTypes as $entity_type_id => $entity_type_info) {
      $form['service_manager']['services'][$entity_type_id] = [
        '#type' => 'fieldset',
        '#title' => $entity_type_info['label'],
      ];
      foreach ($entity_type_info['bundles'] as $bundle_id => $bundle_name) {
        $config_key = $entity_type_id . '_' . $bundle_id . '_service_id';
        $form['service_manager']['services'][$entity_type_id][$config_key] = [
          '#type' => 'select',
          '#title' => $bundle_name,
          '#options' => $service_options,
          '#default_value' => $config->get($config_key) ?: 'use_default',
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('commerce_stock.service_manager');
    $config->set('default_service_id', $values['default_service_id']);
    foreach ($this->purchasableEntityTypes as $entity_type_id => $entity_type_info) {
      foreach (array_keys($entity_type_info['bundles']) as $bundle_id) {
        $key = $entity_type_id . '_' . $bundle_id . '_service_id';
        $value = $values[$key];
        if ($value !== 'use_default') {
          $config->set($key, $value);
        }
        else {
          $config->clear($key);
        }
      }
    }
    $config->save();

    drupal_set_message($this->t('Stock configuration updated.'));
  }

}
