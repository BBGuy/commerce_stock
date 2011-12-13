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

  $conditions['commerce_ss_stock_enabled_on_line_item'] = array(
    'label' => t('Line item has a product with simple stock enabled'),
    'parameter' => array(
      'commerce_line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line Item'),
      ),
    ),
    'group' => t('Commerce Stock (ss)'),
    'callbacks' => array(
      'execute' => 'commerce_ss_line_item_product_enabled',
    ),
  );
  $conditions['commerce_ss_stock_enabled_on_product'] = array(
    'label' => t('Product has simple stock enabled'),

    'parameter' => array(
      'commerce_product' => array(
        'type' => 'commerce_product',
        'label' => t('product'),
      ),
    ),
    'group' => t('Commerce Stock (ss)'),
    'callbacks' => array(
      'execute' => 'commerce_ss_product_enabled',
    ),
  );

  return $conditions;
}
