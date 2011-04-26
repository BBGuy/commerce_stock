<?php

/**
 * @file
 * Rules integration for stock.
 */


/**
* Implements hook_rules_action_info().
*
* Provides an action to adjust stock level
*   of a certain product
*   by a certain quantity.
*/
function commerce_stock_rules_action_info() {
  $actions = array();
/*
  $actions['commerce_stock_adjust'] = array(
    'label' => t('Adjust the product stock level by a set amount'),
    'group' => t('Commerce Stock'),
    'parameter' => array(
      'commerce_product' => array(
        'type' => 'commerce_product',
        'label' => t('Product'),
        'save' => TRUE //save the changes after executing the rule
      ),
      'amount' => array(
        'type' => 'integer',
        'label' => t('Amount')
      ),
    ),
  );

  $actions['commerce_stock_adjust_by_order'] = array(
    'label' => t('Adjust the product stock level, given an order'),
    'group' => t('Commerce Stock'),
    'parameter' => array(
      'commerce_order' => array(
        'type' => 'commerce_order',
        'label' => t('Order')
      ),
    ),
  );
*/
  //TODO provide "setting"/parameter to ameliorate or de- the stock level, + or -
  $actions['commerce_stock_adjust_by_line_item'] = array(
    'label' => t('Adjust the product stock level, given a line item'),
    'group' => t('Commerce Stock'),
    'parameter' => array(
      'commerce_line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line item')
      ),
    ),
  );

  return $actions;
}

/**
 * TODO finish
function commerce_stock_adjust_by_order($order) {
watchdog('commerce_stock',var_export($order,true));
 */
//loop order line items

//commerce_order
//each (product) line item, adjust stock

//}

/**
 * If the line item is stock-enabled, subtract the sold amount in a line item
 * from stock.
 * @param $line_item
 */
function commerce_stock_adjust_by_line_item($line_item) {
  if ($line_item->type == 'product') {
    // The product SKU that will have its stock level adjusted.
    $sku = $line_item->line_item_label;
    $product = commerce_product_load_by_sku($sku);
    if (commerce_stock_product_type_enabled($product->type)) {
      $qty = intval($line_item->quantity);
      // Subtract the sold amount from the available stock level.
      commerce_stock_adjust($product, -$qty);
    }
  }
}

/**
 * Somewhat the equivalent of uc_stock_adjust().
 *  This function adjust a particular product SKU by a certain value
 *  (a positive integer will add to stock, a negative integer will remove from stock).
 *
 * @param $product The product for which to change the stock level
 * @param $qty The quantity to add to the stock level
 */
function commerce_stock_adjust($product, $qty) {
  if (!commerce_stock_product_type_enabled($product->type)) {
    return;
  }
//drupal_set_message(var_export($product,true));
  //TODO: dynamically get stock field,
  // can't remain a hardcoded untranslated field name, I guess
  $product->field_stock['und'][0]['value'] += intval($qty);
  $result = commerce_product_save($product);
  if ($result) {
    watchdog('commerce_stock', 'Modified stock level of product %sku by %amount', array('%sku' => $product->sku, '%amount' => $qty));
  } else {
    watchdog('commerce_stock', 'Failed attempt to modify stock level of product %sku by %amount', array('%sku' => $product->sku, '%amount' => $qty), WATCHDOG_ERROR);
  }
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
 * Rules condition: checks to see if the given order is in a cart status.
 */
function commerce_stock_rules_order_has_out_of_stock($order) {
  return commerce_stock_order_has_out_of_stock($order);
}

