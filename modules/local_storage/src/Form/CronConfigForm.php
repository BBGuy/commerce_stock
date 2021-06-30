<?php

namespace Drupal\commerce_stock_local\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class CronConfigForm.
 */
class CronConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cron_config_form';
  }


  /**
   * Get the editable configuration names.
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_stock_local.cron',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('commerce_stock_local.cron');

    // Options
    $form['cron_run_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Cron run mode'),
      '#options' => ['optimal' => 'Optimal', 'legacy' => 'Legacy/Full'],
      '#description' => $this->t('Optimal - will only update the stats of products with new transactions. Legacy/Full - is the old way of updating stats and will go through all products regardless of their changed state.'),
      '#default_value' => $config->get('cron_run_mode'),
    ];

    $form['update_interval'] = [
      '#type' => 'number',
      '#title' => $this->t('Update interval'),
      '#description' => $this->t('The number of seconds to wait between each cron operation. Set to Zero to run each time cron runs.'),
      '#default_value' => $config->get('update_interval'),
      '#weight' => '0',
    ];

    $form['legacy'] = [
      '#type' => 'details',
      '#title' => $this->t('Legacy settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE
    ];
    $form['legacy']['update_batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Update batch size'),
      '#default_value' => $config->get('update_batch_size'),
      '#weight' => '0',
    ];
    $form['legacy']['update_batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Update batch size'),
      '#default_value' => $config->get('update_batch_size'),
      '#weight' => '0',
    ];

    $form['legacy']['reset_update_last_id'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reset Last processed ID'),
      '#description' => $this->t('This will cause cron to start from the first product.'),
      '#default_value' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the form values.
    $update_batch_size = $form_state->getValue('update_batch_size');
    $update_interval = $form_state->getValue('update_interval');
    $cron_run_mode = $form_state->getValue('cron_run_mode');
    $reset_update_last_id = $form_state->getValue('reset_update_last_id');


    // Set the submitted configuration setting.
    $this->configFactory->getEditable('commerce_stock_local.cron')
      ->set('update_batch_size', $update_batch_size)
      ->set('update_interval', $update_interval)
      ->set('cron_run_mode', $cron_run_mode)

      ->save();

    if ($reset_update_last_id) {
      \Drupal::state()->set('commerce_stock_local.update_last_id', 0);
    }



    parent::submitForm($form, $form_state);
  }

}
