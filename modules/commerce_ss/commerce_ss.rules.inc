<?php

/**
 * @file
 * Rules integration for Commerce Simple Stock.
 */

/**
 * Implements hook_rules_condition_info().
 */
function commerce_ss_rules_condition_info() {
  $conditions = array();

  $conditions['commerce_ss_stock_enabled_on_product'] = array(
    'label' => t('Product has simple stock enabled for its type'),
    'parameter' => array(
      'commerce_product' => array(
        'type' => 'commerce_product',
        'label' => t('product'),
      ),
    ),
    'group' => t('Commerce Stock (ss)'),
    'callbacks' => array(
      // @todo add a function that also checked that the "Disable stock
      // for this product" is not on or maybe add another condision form.
      'execute' => 'commerce_ss_product_enabled',
    ),
  );

  $conditions['commerce_ss_stock_not_disabled'] = array(
    'label' => t('Product simple stock is not disabled by an override'),
    'parameter' => array(
      'commerce_product' => array(
        'type' => 'commerce_product',
        'label' => t('product'),
      ),
    ),
    'group' => t('Commerce Stock (ss)'),
    'callbacks' => array(
      // @todo add a function that also checked that the "Disable stock for
      // this product" is not on or maybe add another condision form.
      'execute' => 'commerce_ss_product_not_disabled_by_override',
    ),
  );

  return $conditions;
}

/**
 * Implements hook_rules_action_info().
 */
function commerce_ss_rules_action_info() {
  $actions = array();

  $actions['commerce_ss_decrease_by_line_item'] = array(
    'label' => t('Decrease the product stock level, given a line item'),
    'group' => t('Commerce Stock (ss)'),
    'parameter' => array(
      'commerce_line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line item'),
      ),
    ),
  );

  $actions['commerce_ss_increase_by_line_item'] = array(
    'label' => t('Increase the product stock level, given a line item'),
    'group' => t('Commerce Stock (ss)'),
    'parameter' => array(
      'commerce_line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line item'),
      ),
    ),
  );

  return $actions;
}


/**
 * Substracts from stock the sold amount in a line item.
 *
 * @param $line_item
 *   A line item object.
 */
function commerce_ss_decrease_by_line_item($line_item) {
  if (in_array($line_item->type, commerce_product_line_item_types())) {
    // The product SKU that will have its stock level adjusted.
    $sku = $line_item->line_item_label;
    $product = commerce_product_load_by_sku($sku);
    if (commerce_ss_product_type_enabled($product->type)) {
      if (!(commerce_ss_product_type_override_enabled($product->type)
          && isset($product->commerce_stock_override['und']) && $product->commerce_stock_override['und'][0]['value'] == 1)) {

        $qty = $line_item->quantity;
        // Subtract the sold amount from the available stock level.
        commerce_ss_stock_adjust($product, -$qty);
      }
    }
  }
}


/**
 * Adds the sold amount in a line item to stock.
 *
 * Typically used when a line item is removed from an order (as when items are
 * added to and removed from cart).
 *
 * @param $line_item
 *   A line item object.
 */
function commerce_ss_increase_by_line_item($line_item) {
  if (in_array($line_item->type, commerce_product_line_item_types())) {
    // The product SKU that will have its stock level adjusted.
    $sku = $line_item->line_item_label;
    $product = commerce_product_load_by_sku($sku);
    if (commerce_ss_product_type_enabled($product->type)) {
      if (!(commerce_ss_product_type_override_enabled($product->type)
           && isset($product->commerce_stock_override['und']) && $product->commerce_stock_override['und'][0]['value'] == 1)) {

        $qty = $line_item->quantity;
        // Subtract the sold amount from the available stock level.
        commerce_ss_stock_adjust($product, $qty);
      }
    }
  }
}

/**
 * Adjusts a particular product SKU by a certain value.
 *
 * A positive number will add to stock, a negative number will remove from
 * stock. Somewhat the equivalent of uc_stock_adjust().
 *
 * @param $product
 *   The product for which to change the stock level.
 * @param $qty
 *   The quantity to add to the stock level.
 */
function commerce_ss_stock_adjust($product, $qty) {
  if (!commerce_ss_product_type_enabled($product->type)) {
    return;
  }

  $wrapper = entity_metadata_wrapper('commerce_product', $product);

  $new_stock = $wrapper->commerce_stock->value() + $qty;
  $wrapper->commerce_stock->set($new_stock);
  $result = $wrapper->save();

  // @todo should this be moved to the
  if ($result) {
    watchdog('commerce_stock', 'Modified stock level of product %sku by %amount', array('%sku' => $product->sku, '%amount' => $qty));
  }
  else {
    watchdog('commerce_stock', 'Failed attempt to modify stock level of product %sku by %amount', array('%sku' => $product->sku, '%amount' => $qty), WATCHDOG_ERROR);
  }
}
