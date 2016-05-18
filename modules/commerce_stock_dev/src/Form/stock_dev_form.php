<?php

/**
 * @file
 * Contains \Drupal\commerce_stock_dev\Form\stock_dev_form.
 */

namespace Drupal\commerce_stock_dev\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_stock\StockAvailabilityChecker;
use Drupal\commerce_stock\CoreStockConfiguration;
use Drupal\commerce\AvailabilityManager;

use Drupal\commerce_stock\StockManager;
use Drupal\commerce_stock_s\StockStorageAPI;

/**
 * Class stock_dev_form.
 *
 * @package Drupal\commerce_stock_dev\Form
 */
class stock_dev_form extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_stock_dev.stock_dev_form'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stock_dev_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_stock_dev.stock_dev_form');

    $form['s_api'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock API'),
    ];

    // Availability.
    $form['s_api']['availability'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Availability'),
    ];
    $form['s_api']['availability']['prod_vid'] = array(
      '#type' => 'number',
      '#title' => t('Product ID'),
      '#step' => '1',
      '#default_value' => '1',
      '#required' => TRUE,
    );
    $form['s_api']['availability']['location_ids'] = array(
      '#type' => 'textfield',
      '#title' => t('Location ID'),
      '#description' => t('A comma separated list of IDs.'),
      '#default_value' => '1,2',
      '#size' => 60,
      '#maxlength' => 50,
      '#required' => TRUE,
    );
    $form['s_api']['availability']['check_stock'] = array(
      '#type' => 'submit',
      '#value' => t('Check Stock using Stock API'),
      '#submit' => ['::submitCheckStockForm'],
    );

    // Locations.
    $form['s_api']['locations'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Locations'),
    ];
    $form['s_api']['locations']['get_list'] = array(
      '#type' => 'submit',
      '#value' => t('Get List of locations'),
      '#submit' => ['::submitGetLocations'],
    );
  $form['s_api']['locations']['active_only'] = array(
    '#type' => 'checkbox',
    '#title' => t('Only show active locations'),
    '#default_value' => TRUE,
  );

  // Transactions
  $form['s_api']['transactions'] = [
    '#type' => 'fieldset',
    '#title' => t('Transactions'),
  ];

  $form['s_api']['transactions']['prod_id'] = array(
    '#type' => 'number',
    '#title' => t('Product ID'),
    '#default_value' => '1',
    '#step' => '1',
    '#required' => TRUE,
  );
  $form['s_api']['transactions']['location'] = array(
    '#type' => 'number',
    '#title' => t('Location'),
    '#default_value' => '1',
    '#step' => '1',
    '#required' => TRUE,
  );
  $form['s_api']['transactions']['zone'] = array(
    '#type' => 'textfield',
    '#title' => t('Zone/Bins'),
    '#description' => t('The location zone (bins)'),
    '#size' => 60,
    '#maxlength' => 50,
  );
  $form['s_api']['transactions']['quentity'] = array(
    '#type' => 'number',
    '#title' => t('Quentity'),
    '#default_value' => '1',
    '#step' => '0.01',
    '#required' => TRUE,
  );
  $form['s_api']['transactions']['create_transaction'] = array(
    '#type' => 'submit',
    '#value' => t('Create transaction'),
      '#submit' => ['::submitCreateTransaction'],
  );
  $form['s_api']['transactions']['update_product_location_level'] = array(
    '#type' => 'submit',
    '#value' => t('Update Product location level'),
      '#submit' => ['::submitupdateProductInventoryLocationLevel'],
  );


  // Typed Transactions
  $form['s_api']['typed_transactions'] = [
    '#type' => 'fieldset',
    '#title' => t('Typed Transactions'),
  ];

  $form['s_api']['typed_transactions']['transaction_notes'] = array(
    '#type' => 'textfield',
    '#title' => t('note'),
    '#description' => t('The location zone (bins)'),
    '#size' => 60,
    '#maxlength' => 50,
  );


  $form['s_api']['typed_transactions']['receiveStock'] = array(
    '#type' => 'submit',
    '#value' => t('receiveStock'),
      '#submit' => ['::submitReceiveStock'],
  );
  $form['s_api']['typed_transactions']['order_id'] = array(
    '#type' => 'number',
    '#title' => t('Order ID'),
    '#default_value' => '1',
    '#step' => '1',
    '#required' => TRUE,
  );
  $form['s_api']['typed_transactions']['user_id'] = array(
    '#type' => 'number',
    '#title' => t('User ID'),
    '#default_value' => '1',
    '#step' => '1',
    '#required' => TRUE,
  );
  $form['s_api']['typed_transactions']['sellStock'] = array(
    '#type' => 'submit',
    '#value' => t('sellStock'),
      '#submit' => ['::submitSellStock'],
  );
  $form['s_api']['typed_transactions']['returnStock'] = array(
    '#type' => 'submit',
    '#value' => t('returnStock'),
      '#submit' => ['::submitReturnStock'],
  );
  $form['s_api']['typed_transactions']['to_location'] = array(
    '#type' => 'number',
    '#title' => t('Move to Location'),
    '#default_value' => '2',
    '#step' => '1',
    '#required' => TRUE,
  );
  $form['s_api']['typed_transactions']['to_zone'] = array(
    '#type' => 'textfield',
    '#title' => t('Zone/Bins'),
    '#description' => t('The location zone (bins)'),
    '#size' => 60,
    '#maxlength' => 50,
    '#title' => t('Move to Zone'),
  );
  $form['s_api']['typed_transactions']['moveStock'] = array(
    '#type' => 'submit',
    '#value' => t('moveStock'),
      '#submit' => ['::submitMoveStock'],
  );



    $form['s_am'] = [
      '#type' => 'fieldset',
      '#title' => t('Availability Manager'),
    ];
    $form['s_am']['check'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Availability'),
    ];
    $form['s_am']['check']['prod_to_check_id'] = array(
      '#type' => 'number',
      '#title' => t('Product ID'),
      '#step' => '1',
      '#default_value' => '1',
      '#required' => TRUE,
    );
    $form['s_am']['check']['prod_to_check_qty'] = array(
      '#type' => 'number',
      '#title' => t('Qty.'),
      '#step' => '1',
      '#default_value' => '1',
      '#required' => TRUE,
    );

    $form['s_am']['check']['check1'] = array(
      '#type' => 'submit',
      '#value' => t('Check Stock using Stock Availability Checker'),
      '#submit' => ['::submitStockAvailabilityCheck'],
    );

    $form['s_am']['check']['check2'] = array(
      '#type' => 'submit',
      '#value' => t('Check Stock using Availability Manager'),
      '#submit' => ['::submitAvailabilityManagerCheck'],
    );

    $form['s_sm'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Manager'),
    ];
    $form['s_sm']['list'] = array(
      '#type' => 'submit',
      '#value' => t('List all services'),
      '#submit' => ['::submitStockManagerList'],
    );


//    $options = [
//      'commerce_cart_form' => $this->t('Shopping cart form (default)'),
//    ];
//    $form['cart_page']['view'] = [
//      '#type' => 'select',
//      '#title' => $this->t('Shopping cart view to be used'),
//      '#options' => $options,
//      '#default_value' => $config->get('cart_page.view'),
//      '#description' => $this->t('Select the order view you want to use for Shopping cart page.'),
//    ];


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
    parent::submitForm($form, $form_state);
    drupal_set_message('Saved configurations');

    $this->config('commerce_stock_dev.stock_dev_form')
      ->save();
  }

  public function submitCheckStockForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Get values.
    $prod_id = $form_state->getValue('prod_vid');
    $location_ids = explode(',', $form_state->getValue('location_ids'));
    // Call the API
    $stock_api = new StockStorageAPI;
    $stock_level = $stock_api->getStockLevel($prod_id, $location_ids);
    drupal_set_message('Stock level is: ' . $stock_level);

    // $stock_api->createTransaction(1, 1, '', 1, 0.0);

  }

  public function submitGetLocations(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $stock_api = new StockStorageAPI;
    $active_only = $form_state->getValue('active_only');
    $locations = $stock_api->getLocationList($active_only);
    drupal_set_message('Locations: ' . print_r($locations, TRUE));

    // $stock_api->createTransaction(1, 1, '', 1, 0.0);

  }

  public function submitCreateTransaction(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    //$variation_id, $location_id, $zone, $quantity,
    $variation_id = $form_state->getValue('prod_id');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quentity');
    $unit_cost = NULL;



    $stock_api = new StockStorageAPI;
    $options = array(
//      'related_tid' => '1',
//      'related_oid' => '1',
//      'related_uid' => '1',
    );
    $stock_api->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, 1, $options);
  }

  public function submitupdateProductInventoryLocationLevel(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $variation_id = $form_state->getValue('prod_id');
    $location_id = $form_state->getValue('location');

    $stock_api = new StockStorageAPI;
    $stock_api->updateProductInventoryLocationLevel($location_id, $variation_id);
  }

  public function submitStockAvailabilityCheck(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Get values.
    $variation_id = $form_state->getValue('prod_to_check_id');
    $prod_qty = $form_state->getValue('prod_to_check_qty');

    // Create needed enities
    $stock_api = new StockStorageAPI;
    $configuration = new CoreStockConfiguration($stock_api);
    // Load the product variation.
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    if (!isset($product_variation)) {
      drupal_set_message('Can not load product!');
      return;
    }

    // Create the checker
    $availability_checker = new StockAvailabilityChecker($stock_api, $configuration);
    /// Check
    if ($availability_checker->check($product_variation, $prod_qty)) {
      drupal_set_message('Available');
    }
    else {
      drupal_set_message('Not Available');
    }
  }


  public function submitAvailabilityManagerCheck(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Test.
    $availabilityManager = \Drupal::service('commerce.availability_manager');


    // Get values.
    $variation_id = $form_state->getValue('prod_to_check_id');
    $prod_qty = $form_state->getValue('prod_to_check_qty');

    // Load the product variation.
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    if (!isset($product_variation)) {
      drupal_set_message('Can not load product!');
      return;
    }



    $availabilityManager = \Drupal::service('commerce.availability_manager');

    $availabe = $availabilityManager->check($product_variation, $prod_qty);

    /// Check
    if ($availabe) {
      drupal_set_message('Available');
    }
    else {
      drupal_set_message('Not Available');
    }
  }

  public function submitStockManagerList(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

      $availabilityManager = \Drupal::service('commerce.stock_manager');
      $services = $availabilityManager->listServices();
      drupal_set_message(print_r($services, TRUE));

  }

  public function submitReceiveStock(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $variation_id = $form_state->getValue('prod_id');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quentity');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;

    // Load the product variation.
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    // Create the transaction.
    $stockManager = \Drupal::service('commerce.stock_manager');
    $stockManager->receiveStock($product_variation, $location_id, $zone, $quantity, $unit_cost, $message);
  }

  public function submitSellStock(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $variation_id = $form_state->getValue('prod_id');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quentity');
    $order_id = $form_state->getValue('order_id');
    $user_id = $form_state->getValue('user_id');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;

    // Load the product variation.
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    // Create the transaction.
    $stockManager = \Drupal::service('commerce.stock_manager');
    $stockManager->sellStock($product_variation, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message);
  }

  public function submitReturnStock(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $variation_id = $form_state->getValue('prod_id');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quentity');
    $order_id = $form_state->getValue('order_id');
    $user_id = $form_state->getValue('user_id');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;

    // Load the product variation.
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    // Create the transaction.
    $stockManager = \Drupal::service('commerce.stock_manager');
    $stockManager->returnStock($product_variation, $location_id,  $zone, $quantity, $unit_cost, $order_id, $user_id, $message);
  }

  public function submitMoveStock(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $variation_id = $form_state->getValue('prod_id');
    $from_location_id = $form_state->getValue('location');
    $to_location_id = $form_state->getValue('to_location');
    $from_zone = $form_state->getValue('zone');
    $to_zone = $form_state->getValue('to_zone');
    $quantity = $form_state->getValue('quentity');
    $order_id = $form_state->getValue('order_id');
    $user_id = $form_state->getValue('user_id');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;

    // Load the product variation.
    $variation_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $product_variation = $variation_storage->load($variation_id);

    // Create the transaction.
    $stockManager = \Drupal::service('commerce.stock_manager');
    $stockManager->moveStock($product_variation, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message);
  }


}
