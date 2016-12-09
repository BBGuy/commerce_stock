<?php

namespace Drupal\stock_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StockTransactions.
 *
 * @package Drupal\stock_ui\Form
 */
class StockTransactions1 extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stock_transactions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['product_variation'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Select product'),
      '#target_type' => 'commerce_product_variation',
      '#required' => TRUE,
      '#selection_handler' => 'default',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo - We need to check the product has is managed by a stock service.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the selected product.
    $product_variation_id = $form_state->getValue('product_variation');
    // Send to the second part form.
    $form_state->setRedirect('stock_ui.stock_transactions2', ['commerce_product_v_id' => $product_variation_id]);
  }

}
