<?php

namespace Drupal\stock_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StockTransactions.
 *
 * @package Drupal\stock_ui\Form
 */
class StockTransactions2 extends FormBase {

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

    // Get Product variation.
    $request = \Drupal::request();
    $q = $request->query->all();
    if ($request->query->has('commerce_product_v_id')) {
      $variation_id = $request->query->get('commerce_product_v_id');
    }
    // If no product variation.
    else {
      // Send back to stage 1.
      return $this->redirect('stock_ui.stock_transactions1');
    }

    // Load the product variation.
    /** @var ProductVariationStorage $variation_storage */
    $variation_storage = \Drupal::service('entity_type.manager')
      ->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    // Get a list of locations relevent for our product.
    /** @var Drupal\commerce_stock\StockManager $stockManager */
    $stockManager = \Drupal::service('commerce.stock_manager');
    $stockService = $stockManager->getService($product_variation);
    $locations = $stockService->getStockChecker()->getLocationList(TRUE);
    // Build the list of location options.
    $location_options = [];
    foreach ($locations as $location_id => $location) {
      $location_options[$location_id] = $location['name'];
    }

    $form['transaction_type'] = [
      '#type' => 'select',
      '#title' => $this->t('transaction Type'),
      '#options' => [
        'receiveStock' => $this->t('Receive Stock'),
        'sellStock' => $this->t('Sell Stock'),
        'returnStock' => $this->t('Return Stock'),
        'moveStock' => $this->t('Move Stock'),
      ],
    ];

    $form['product_variation_id'] = [
      '#type' => 'value',
      '#value' => $variation_id,
    ];

    $form['source'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Source location'),
    ];

    $form['source']['source_location'] = [
      '#type' => 'select',
      '#title' => $this->t('Location'),
      '#options' => $location_options,
    ];

    $form['source']['source_zone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zone/Bins'),
      '#description' => $this->t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];

    $form['target'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Move target'),
      '#states' => [
        'visible' => [
          ':input[name="transaction_type"]' => ['value' => 'moveStock'],
        ],
      ],
    ];

    $form['target']['target_location'] = [
      '#type' => 'select',
      '#title' => $this->t('Target Location'),
      '#options' => $location_options,
    ];

    $form['target']['target_zone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zone/Bins'),
      '#description' => $this->t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];

    $form['user'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Optional user'),
      '#target_type' => 'user',
      '#selection_handler' => 'default',
      '#states' => [
        'visible' => [
          ':input[name="transaction_type"]' => [
            ['value' => 'sellStock'],
            ['value' => 'returnStock'],
          ],
        ],
      ],

    ];
    $form['order'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Optional order number'),
      '#target_type' => 'commerce_order',
      '#selection_handler' => 'default',
      '#states' => [
        'visible' => [
          ':input[name="transaction_type"]' => [
            ['value' => 'sellStock'],
            ['value' => 'returnStock'],
          ],
        ],
      ],
    ];

    $form['transaction_qty'] = [
      '#type' => 'number',
      '#title' => $this->t('Quentity'),
      '#default_value' => '1',
      '#step' => '0.01',
      '#required' => TRUE,
    ];

    $form['transaction_note'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Note'),
      '#description' => $this->t('A note for the transaction'),
      '#maxlength' => 64,
      '#size' => 64,
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the details.
    $transaction_type = $form_state->getValue('transaction_type');
    $product_variation_id = $form_state->getValue('product_variation_id');
    $source_location = $form_state->getValue('source_location');
    $source_zone = $form_state->getValue('source_zone');

    $qty = $form_state->getValue('transaction_qty');
    $transaction_note = $form_state->getValue('transaction_note');
    if ($transaction_type == 'moveStock') {
      // Move transaction.
      $target_location = $form_state->getValue('target_location');
      $target_zone = $form_state->getValue('target_zone');
    }

    // Load the product variation.
    /** @var ProductVariationStorage $variation_storage */
    $variation_storage = \Drupal::service('entity_type.manager')
      ->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($product_variation_id);

    /** @var Drupal\commerce_stock\StockManager $stockManager */
    $stockManager = \Drupal::service('commerce.stock_manager');

    if ($transaction_type == 'receiveStock') {
      // Create transaction.
      $stockManager->receiveStock($product_variation, $source_location, $source_zone, $qty, NULL, $transaction_note);
    }
    elseif ($transaction_type == 'sellStock') {
      // Get sell/return specific details.
      $order_id = $form_state->getValue('order');;
      $user_id = $form_state->getValue('user');;
      // Create transaction.
      $stockManager->sellStock($product_variation, $source_location, $source_zone, $qty, NULL, $order_id, $user_id, $transaction_note);
    }
    elseif ($transaction_type == 'returnStock') {
      // Get sell/return specific details.
      $order_id = $form_state->getValue('order');;
      $user_id = $form_state->getValue('user');;
      // Create transaction.
      $stockManager->returnStock($product_variation, $source_location, $source_zone, $qty, NULL, $order_id, $user_id, $transaction_note);
    }
    elseif ($transaction_type == 'moveStock') {
      // Get move specific details.
      $target_location = $form_state->getValue('target_location');
      $target_zone = $form_state->getValue('target_zone');
      // Create transaction.
      $stockManager->moveStock($product_variation, $source_location, $target_location, $source_zone, $target_zone, $qty, NULL, $transaction_note);
    }

  }

}
