<?php

namespace Drupal\commerce_stock\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class StockConfigForm extends ConfigFormBase {

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the default service.
    $config = $this->config('commerce_stock.service_manager');
    $default_service_id = $config->get('default_service_id');

    // Get a list of available services from the stock manager.
    $stock_service_manager = \Drupal::service('commerce_stock.service_manager');
    $stock_services = $stock_service_manager->listServiceIds();

    $form['service_manager'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Stock Service Manager'),
    ];

    $form['service_manager']['default_service_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Default service'),
      '#options' => $stock_services,
      '#default_value' => $default_service_id,
    ];

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
    $this->config('commerce_stock.service_manager')
      ->set('default_service_id', $values['default_service_id'])
      ->save();
  }

}
