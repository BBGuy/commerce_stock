<?php

/**
 * @file
 * Contains \Drupal\commerce_stock\Form\StockConfigForm.
 */

namespace Drupal\commerce_stock\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_stock\StockManager;

use Drupal\commerce_stock\StockAvailabilityChecker;
use Drupal\commerce_stock\CoreStockConfiguration;
use Drupal\commerce\AvailabilityManager;

use Drupal\commerce_stock_s\StockStorageAPI;

/**
 * Class StockConfigForm.
 *
 * @package Drupal\commerce_stock\Form
 */
class StockConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_stock.manager'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stock_config_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the default service.
    $config = $this->config('commerce_stock.manager');
    $default_service_id = $config->get('default_service_id');

    // Get a list of available services from the stock manager.
    $stock_manager = \Drupal::service('commerce.stock_manager');
    $stock_services = $stock_manager->listServiceIds();


    $form['stock_manager'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Manager'),
    ];


  $form['stock_manager']['default_service_id'] = [
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
    //parent::submitForm($form, $form_state);
    $values = $form_state->getValues();
    $this->config('commerce_stock.manager')
      ->set('default_service_id', $values['default_service_id'])
      ->save();


    //drupal_set_message('Saved configurations');
  }

}


