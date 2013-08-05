<?php

/**
 * @file
 * Rules integration for Commerce Stock.
 */

/**
 * Implements hook_rules_action_info().
 *
 * Provides an action to adjust stock level of a certain product
 * by a certain quantity.
 */

/**
 * Implements hook_rules_event_info().
 */
function commerce_stock_rules_event_info() {
  $events = array();

  $events['commerce_stock_add_to_cart_check_product'] = array(
    'label' => t('Check if a product is in stock when adding to cart'),
    'group' => t('Commerce Stock'),
    'variables' => commerce_stock_rules_event_variables(),
    'access callback' => 'commerce_stock_rules_access',
  );

  $events['commerce_stock_check_add_to_cart_form_state'] = array(
    'label' => t('Check if a product add to cart form should be enabled (is in stock)'),
    'group' => t('Commerce Stock'),
    'variables' => commerce_stock_rules_cart_event_variables(),
    'access callback' => 'commerce_stock_rules_access',
  );

  $events['commerce_stock_check_product_checkout'] = array(
    'label' => t('Check if a product is in stock before continuing to checkout'),
    'group' => t('Commerce Stock'),
    'variables' => commerce_stock_rules_cart_event_variables(),
    'access callback' => 'commerce_stock_rules_access',
  );

  return $events;
}

/**
 * Returns a variables array for stock check event.
 */
function commerce_stock_rules_event_variables() {
  $variables = array(
    'commerce_product' => array(
      'label' => t('Product'),
      'type' => 'commerce_product',
    ),
    'stock_requested_quantity' => array(
      'label' => t('Requested Quantity'),
      'type' => 'decimal',
    ),
    'stock_already_ordered' => array(
      'label' => t('Quantity already ordered (in the cart)'),
      'type' => 'decimal',
    ),
    'stock_requested_total' => array(
      'label' => t('Quantity requested + already ordered'),
      'type' => 'decimal',
    ),
  );
  return $variables;
}

/**
 * Returns a variables array for stock enable cart check event.
 */
function commerce_stock_rules_cart_event_variables() {
  $variables = array(
    'commerce_product' => array(
      'label' => t('Product'),
      'type' => 'commerce_product',
    ),
    'stock_already_ordered' => array(
      'label' => t('Quantity already ordered (in the cart)'),
      'type' => 'decimal',
    ),
  );
  return $variables;
}

/**
 * Implements hook_rules_condition_info().
 */
function commerce_stock_rules_condition_info() {
  $conditions = array();

  $conditions['commerce_stock_order_has_out_of_stock'] = array(
    'label' => t('Order has products that are out of stock'),
    'parameter' => array(
      'order' => array(
        'type' => 'commerce_order',
        'label' => t('Order'),
      ),
    ),
    'group' => t('Commerce Stock'),
    'callbacks' => array(
      'execute' => 'commerce_stock_rules_order_has_out_of_stock',
    ),
  );
  return $conditions;
}

/**
 * Rules condition: checks to see if the given order is in a cart status.
 */
function commerce_stock_rules_order_has_out_of_stock($order) {
  return commerce_stock_order_has_out_of_stock($order);
}

/**
 * Implementation of hook_rules_action_info().
 */
function commerce_stock_rules_action_info() {
  $actions = array();

  // The Stock cart state action.
  $actions['commerce_stock_add_to_cart_set_state'] = array(
    'label' => t('Set the result of an add to cart stock check'),
    'parameter' => array(
      'stock_action' => array(
        'type' => 'decimal',
        'label' => t('Stock Action'),
        'description' => t('the action to take .'),
        'options list' => 'commerce_stock_check_state_options_list',
      ),
      'message' => array(
        'type' => 'text',
        'label' => t('User message'),
      ),
      'approved_quantity' => array(
        'type' => 'decimal',
        'label' => t('Approved Quantity'),
      ),
    ),
    'group' => t('Commerce Stock'),
    'callbacks' => array(
      'execute' => 'commerce_stock_rules_add_to_cart_set_state',
    ),
  );

  // The stock disable cart action.
  $actions['commerce_stock_set_add_to_cart_form_state'] = array(
    'label' => t('Set the state of the add to cart form'),
    'parameter' => array(
      'disabled' => array(
        'type' => 'boolean',
        'label' => t('Disable the add to cart?'),
      ),
      'text' => array(
        'type' => 'text',
        'label' => t('The text to set the action to'),
      ),
      'class_name' => array(
        'type' => 'text',
        'label' => t('add a class to the add to cart form'),
      ),
    ),
    'group' => t('Commerce Stock'),
    'callbacks' => array(
      'execute' => 'commerce_stock_rules_set_add_to_cart_form_state',
    ),
  );

  // The stock custom cart action.
  $actions['commerce_stock_custom_cart_form_state'] = array(
    'label' => t('Advanced configuration of the add to cart form'),
    'parameter' => array(
      'hide_qty' => array(
        'type' => 'boolean',
        'label' => t('Hide the Quantity field if it is visible'),
      ),
      'text' => array(
        'type' => 'text',
        'label' => t('The text to set the action to'),
      ),
      'class_name' => array(
        'type' => 'text',
        'label' => t('Add a class to the add to cart form'),
      ),
      'action_prefix' => array(
        'type' => 'text',
        'label' => t('Prefix'),
        'optional' => TRUE,
      ),
      'action_suffix' => array(
        'type' => 'text',
        'label' => t('Suffix'),
        'optional' => TRUE,
      ),
      'stock_action' => array(
        'type' => 'decimal',
        'label' => t('Stock Action'),
        'description' => t('The action to take.'),
        'options list' => 'commerce_stock_custom_cart_form_state_action_options_list',
        'default value' => 0,
      ),
      'disabled_cart' => array(
        'type' => 'boolean',
        'label' => t('Disable the add to cart?'),
        'description' => t('Only aplicable if Normal Submit was chosen as the Stock Action'),
      ),
      'custom_submit' => array(
        'type' => 'text',
        'label' => t('A new custom submit function'),
        'optional' => TRUE,
        'description' => t('Only aplicable if Custom Submit was chosen as the Stock Action'),
      ),
      'custom_submit_clear' => array(
        'type' => 'boolean',
        'label' => t('Clear the submit array first'),
        'description' => t('Only aplicable if Custom Submit was chosen as the Stock Action'),
      ),
      'custom_validate' => array(
        'type' => 'text',
        'label' => t('a new of a custom validation function'),
        'description' => t('Only aplicable if Custom Submit was chosen as the Stock Action'),
        'optional' => TRUE,
      ),
      'custom_validate_clear' => array(
        'type' => 'boolean',
        'label' => t('Clear the validation array first'),
        'description' => t('Only aplicable if Custom Submit was chosen as the Stock Action'),
      ),
      'custom_url' => array(
        'type' => 'text',
        'label' => t('Custom URL'),
        'description' => t('Only aplicable if URL Action was chosen as the Stock Action  (all submit and validation will be ignored). You can use the [product_id] token and [url] for a return path'),
        'optional' => TRUE,

      ),
      'custom_html' => array(
        'type' => 'text',
        'label' => t('Custom HTML'),
        'description' => t('Only aplicable if Custom HTML was chosen as the Stock Action  (all submit and validation will be ignored). You can use the [product_id] token and [url] for a return path'),
        'optional' => TRUE,
      ),
    ),
    'group' => t('Commerce Stock'),
    'callbacks' => array(
      'execute' => 'commerce_stock_custom_cart_form_state',
    ),
  );

  // The Stock checkout check action.
  $actions['commerce_stock_checkout_state'] = array(
    'label' => t('Set the state of the checkout process (called one per an item)'),
    'parameter' => array(
      'stock_action' => array(
        'type' => 'decimal',
        'label' => t('Stock Action'),
        'description' => t('the action to take .'),
        'options list' => 'commerce_stock_check_state_options_list',
      ),
      'message' => array(
        'type' => 'text',
        'label' => t('User message'),
      ),
      'approved_quantity' => array(
        'type' => 'decimal',
        'label' => t('Approved Quantity'),
      ),
    ),
    'group' => t('Commerce Stock'),
    'callbacks' => array(
      'execute' => 'commerce_stock_rules_set_checkout_state',
    ),
  );

  return $actions;
}

/**
 * Rules integration access callback.
 */
function commerce_stock_rules_access() {
  return user_access('Make rule based changes to commerce stock');
}

/**
 * Rules action: updates an order's status.
 *
 * updates an order's status to the default status of the given order state.
 */
function commerce_stock_rules_add_to_cart_set_state($stock_action, $message, $approved_quantity) {
  if ($approved_quantity < 0) {
    $approved_quantity = 0;
    // If zero transection must be blocked as it you are not allowed
    // to add zero quantity to the cart.
    $stock_action = 1;
  }
  // Set the global stock check array.
  global $stock_check_data;
  $stock_check_data['state'] = $stock_action;
  $stock_check_data['message'] = $message;
  $stock_check_data['qty'] = $approved_quantity;
}

/**
 * Rules action:
 * @todo: Define this function.
 */
function commerce_stock_rules_set_add_to_cart_form_state($disabled, $text, $class_name) {
  global $stock_cart_check_data;
  $stock_cart_check_data['form']['submit']['#value'] = $text;
  $stock_cart_check_data['form']['submit']['#disabled'] = $disabled;
  $stock_cart_check_data['form']['#attributes']['class']['stock'] = $class_name;
}

/**
 * @todo: Define this function.
 */
function commerce_stock_custom_cart_form_state($hide_qty, $text, $class_name,
          $action_prefix, $action_suffix, $stock_action, $disabled_cart,
          $custom_submit, $custom_submit_clear, $custom_validate,
          $custom_validate_clear, $custom_url, $custom_html) {
  global $stock_cart_check_data;

  // Read form values.
  $form_action = $stock_cart_check_data['form']['#action'];
  if (isset ($stock_cart_check_data['form']['product_id']['#value'])) {
    $prod_id = $stock_cart_check_data['form']['product_id']['#value'];
  }
  elseif (isset ($stock_cart_check_data['form']['product_id']['#default_value'])) {
    $prod_id = $stock_cart_check_data['form']['product_id']['#default_value'];
  }

  // Class.
  $stock_cart_check_data['form']['#attributes']['class']['stock'] = $class_name;

  // Quantity field.
  if ($hide_qty) {
    $stock_cart_check_data['form']['quantity']['#access'] = FALSE;
  }

  // Prefix & sufix.
  $stock_cart_check_data['form']['submit']['#prefix'] = $action_prefix;
  $stock_cart_check_data['form']['submit']['#suffix'] = $action_suffix;

  switch ($stock_action) {

    // Normal Submit.
    case 0:
      $stock_cart_check_data['form']['submit']['#value'] = $text;
      $stock_cart_check_data['form']['submit']['#disabled'] = $disabled_cart;
      break;

    // Custom Submit.
    case 1:
      $stock_cart_check_data['form']['submit']['#value'] = $text;
      // Should we clear the submit array.
      if ($custom_submit_clear) {
        $stock_cart_check_data['form']['#submit'] = array();
      }
      // Should we clear the validate array.
      if ($custom_validate_clear) {
        $stock_cart_check_data['form']['#validate'] = array();
      }
      // Add custom submit function.
      if (!empty($custom_submit)) {
        $stock_cart_check_data['form']['#submit'][] = $custom_submit;
      }
      // Sdd custom validation function.
      if (!empty($custom_validate)) {
        $stock_cart_check_data['form']['#validate'][] = $custom_validate;
      }
      break;

    // URL Action.
    case 2:
      if (!empty($custom_url)) {
        // Custom url.
        $custom_url = str_replace('[product_id]', $prod_id, $custom_url);
        $custom_url = str_replace('[url]', $form_action, $custom_url);
        $stock_cart_check_data['form']['submit']['#type'] = 'link';
        $stock_cart_check_data['form']['submit']['#title'] = $text;
        $stock_cart_check_data['form']['submit']['#href'] = $custom_url;
      }
      break;

    // Custom HTML.
    case 3:
      if (!empty($custom_html)) {
        // Custom url.
        $custom_html = str_replace('[product_id]', $prod_id, $custom_html);
        $custom_html = str_replace('[url]', $form_action, $custom_html);
        $stock_cart_check_data['form']['submit']['#type'] = 'markup';
        $stock_cart_check_data['form']['submit']['#markup'] = $custom_html;
      }
      break;

    default:
      break;
  }
}

/**
 * Rules action: Set the state of the checkout process (called one per an item).
 */
function commerce_stock_rules_set_checkout_state($stock_action, $message, $approved_quantity) {
  if ($approved_quantity < 0) {
    $approved_quantity = 0;
    // If zero transection must be blocked as it you are not allowed
    // to add zero quantity to the cart.
    $stock_action = 1;
  }
  // Set the global stock check array.
  global $stock_check_data;
  $stock_check_data['state'] = $stock_action;
  $stock_check_data['message'] = $message;
  $stock_check_data['qty'] = $approved_quantity;
}

/**
 * Options for stock actions.
 */
function commerce_stock_check_state_options_list() {
  return array(
    0 => 'do nothing' ,
    1 => 'block transection',
    2 => 'display message only');
}

/**
 * Options for Advanced configuration of the add to cart form actions.
 */
function commerce_stock_custom_cart_form_state_action_options_list() {
  return array(
    0 => 'Normal Submit' ,
    1 => 'Custom Submit',
    2 => 'URL Action',
    3 => 'Custom HTML');

}
