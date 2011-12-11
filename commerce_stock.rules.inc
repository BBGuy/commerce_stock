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

  $events['commerce_stock_check_product'] = array(
    'label' => t('Check if a product is in stock'),
    'group' => t('Commerce Stock'),
    'variables' => commerce_stock_rules_event_variables(),
    'access callback' => 'commerce_stock_rules_access',
  );

  $events['commerce_stock_check_add_to_cart_state'] = array(
    'label' => t('Check if a product add to cart should be enabled (is in stock)'),
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
  $conditions['commerce_stock_stock_enabled_on_line_item'] = array(
    'label' => t('Line item has a product of product type that has stock management enabled'),
    'parameter' => array(
      'type' => 'commerce_line_item',
      'label' => t('Product'),
    ),
    'group' => t('Commerce Stock'),
    'callbacks' => array(
      'execute' => 'commerce_stock_line_item_product_enabled',
    ),
  );

  return $conditions;
}

/**
 *
 * Rules condition: checks to see if the given order is in a cart status.
 */
function commerce_stock_rules_order_has_out_of_stock($order) {
  return commerce_stock_order_has_out_of_stock($order);
}

/**
 *
 * Implementation of hook_rules_action_info().
 */
function commerce_stock_rules_action_info() {
  $actions = array();

  $actions['commerce_stock_decrease_by_line_item'] = array(
    'label' => t('Decrease the product stock level, given a line item'),
    'group' => t('Commerce Stock'),
    'parameter' => array(
      'commerce_line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line item')
      ),
    ),
  );

  // 'Adjust by line item' was the old name. To avoid breaking existing
  // installations we'll keep a copy around. Sad. Couldn't figure out
  // a way to update it using hook_update_N().
  $actions['commerce_stock_adjust_by_line_item'] = $actions['commerce_stock_decrease_by_line_item'];
  $actions['commerce_stock_adjust_by_line_item']['label'] = t('(legacy) Decrease the product stock level');

  $actions['commerce_stock_increase_by_line_item'] = array(
    'label' => t('Increase the product stock level, given a line item'),
    'group' => t('Commerce Stock'),
    'parameter' => array(
      'commerce_line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line item')
      ),
    ),
  );

  // The Stock check action
  $actions['commerce_stock_check_state'] = array(
    'label' => t('Set the result of a stock check'),
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
      'execute' => 'commerce_stock_rules_update_state',
    ),
  );



  // The Stock enable add to cart check action
  $actions['commerce_stock_add_to_cart_state'] = array(
    'label' => t('Set the state of the add to cart'),
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
      'execute' => 'commerce_stock_rules_set_add_to_cart_state',
    ),
  );


  // The Stock checkout check action
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
 * If the line item is stock-enabled, subtract the sold amount in a line item
 * from stock.
 *
 * @param $line_item
 *   A line item object.
 */
function commerce_stock_decrease_by_line_item($line_item) {
  if(in_array($line_item->type, commerce_product_line_item_types())) {
    // The product SKU that will have its stock level adjusted.
    $sku = $line_item->line_item_label;
    $product = commerce_product_load_by_sku($sku);
    if (commerce_stock_product_type_enabled($product->type)) {
      if (!(commerce_stock_product_type_override_enabled($product->type)
          && isset($product->commerce_stock_override['und']) && $product->commerce_stock_override['und'][0]['value'] == 1)) {

        $qty = $line_item->quantity;
        // Subtract the sold amount from the available stock level.
        commerce_stock_adjust($product, -$qty);
      }
    }
  }
}

/**
 * For backward compatibility, keep a function by the old name.
 *
 * @todo: Remove this after a decent interval.
 *
 * @param $line_item
 */
function commerce_stock_adjust_by_line_item($line_item) {
  commerce_stock_decrease_by_line_item($line_item);
}

/**
 * If the line item is stock-enabled, add the sold amount in a line item
 * to stock. Typically used when a line item is removed from an order
 * (as when items are added to and removed from cart).
 *
 * @param $line_item
 *   A line item object.
 */
function commerce_stock_increase_by_line_item($line_item) {
  if(in_array($line_item->type, commerce_product_line_item_types())) {
    // The product SKU that will have its stock level adjusted.
    $sku = $line_item->line_item_label;
    $product = commerce_product_load_by_sku($sku);
    if (commerce_stock_product_type_enabled($product->type)) {
      if (!(commerce_stock_product_type_override_enabled($product->type)
           && isset($product->commerce_stock_override['und']) && $product->commerce_stock_override['und'][0]['value'] == 1)) {

        $qty = $line_item->quantity;
        // Subtract the sold amount from the available stock level.
        commerce_stock_adjust($product, $qty);
      }
    }
  }
}

/**
 * Adjusts a particular product SKU by a certain value.
 * A positive number will add to stock, a negative number will remove from
 * stock. Somewhat the equivalent of uc_stock_adjust().
 *
 * @param $product
 *   The product for which to change the stock level.
 * @param $qty
 *   The quantity to add to the stock level.
 */
function commerce_stock_adjust($product, $qty) {
  if (!commerce_stock_product_type_enabled($product->type)) {
    return;
  }

  $wrapper = entity_metadata_wrapper('commerce_product', $product);

  $new_stock = $wrapper->commerce_stock->value() + $qty;
  $wrapper->commerce_stock->set($new_stock);
  $result = $wrapper->save();

  if ($result) {
    watchdog('commerce_stock', 'Modified stock level of product %sku by %amount', array('%sku' => $product->sku, '%amount' => $qty));
  } else {
    watchdog('commerce_stock', 'Failed attempt to modify stock level of product %sku by %amount', array('%sku' => $product->sku, '%amount' => $qty), WATCHDOG_ERROR);
  }
}




/**
 * Rules integration access callback.
 */
function commerce_stock_rules_access() {
  // @todo
  return TRUE;
}


/**
 * Rules action: updates an order's status to the default status of the given
 *   order state.
 */
function commerce_stock_rules_update_state($stock_action, $message, $approved_quantity) {

  if ($approved_quantity < 0) {
    $approved_quantity = 0;
    // if zero transection must be blocked as it you are not allowed
    // to add zero quantity to the cart
    $stock_action = 1;
  }
  // set the global stock check array
  global $stock_check_data;
  $stock_check_data['state'] = $stock_action;
  $stock_check_data['message'] = $message;
  $stock_check_data['qty'] = $approved_quantity;

}


/**
 * Rules action:
 *
 */
function commerce_stock_rules_set_add_to_cart_state($disabled, $text, $class_name) {
   // drupal_set_message('test 2');

  global $stock_cart_check_data;
  $stock_cart_check_data['form']['submit']['#value'] = $text;
  $stock_cart_check_data['form']['submit']['#disabled'] = $disabled;
  $stock_cart_check_data['form']['#attributes']['class']['stock'] = $class_name;

//  $stock_cart_check_data['form']['submit']['#value'] = t('Out of stock');
//  $stock_cart_check_data['form']['submit']['#disabled'] = TRUE;
//  $stock_cart_check_data['form']['#attributes'][] = array('class' => array('out-of-stock'));


}



/**
 * Rules action:
 *
 */
function commerce_stock_rules_set_checkout_state($stock_action, $message, $approved_quantity) {
if ($approved_quantity < 0) {
    $approved_quantity = 0;
    // if zero transection must be blocked as it you are not allowed
    // to add zero quantity to the cart
    $stock_action = 1;
  }
//  if ($stock_action > $stock_check_data['state']) {
//    $stock_check_data['state'] = $stock_action;
//  }
  // set the global stock check array
  global $stock_check_data;
  $stock_check_data['state'] = $stock_action;
  $stock_check_data['message'] = $message;
  $stock_check_data['qty'] = $approved_quantity;

}




function commerce_stock_check_state_options_list() {
  return array( 0 => 'do nothing' , 1 => 'block transection', 2 => 'display message only');

}