<?php

/**
 * @file
 * Default rule configurations for Commerce Simple Stock.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_ssr_default_rules_configuration() {
  $rules = array();

  $rules_export = '{ "rules_stock_disable_add_to_cart" : {
    "LABEL" : "Stock: disable add to cart ",
    "PLUGIN" : "reaction rule",
    "REQUIRES" : [ "commerce_ss", "rules", "commerce_stock" ],
    "ON" : [ "commerce_stock_check_add_to_cart_form_state" ],
    "IF" : [
      { "commerce_ss_stock_enabled_on_product" : { "commerce_product" : [ "commerce_product" ] } },
      { "commerce_ss_stock_not_disabled" : { "commerce_product" : [ "commerce_product" ] } },
      { "NOT data_is" : {
          "data" : [ "commerce-product:commerce-stock" ],
          "op" : "\u003e",
          "value" : "0"
        }
      }
    ],
    "DO" : [
      { "commerce_stock_set_add_to_cart_form_state" : { "disabled" : 1, "text" : "Out of stock", "class_name" : "out-of-stock" } }
    ]
  }}';

  $rules['rules_stock_disable_add_to_cart'] = rules_import($rules_export);

  $rules_export = '{ "rules_stock_validate_add_to_cart" : {
    "LABEL" : "Stock: validate add to cart",
    "PLUGIN" : "reaction rule",
    "REQUIRES" : [ "commerce_ss", "rules", "commerce_stock" ],
    "ON" : [ "commerce_stock_add_to_cart_check_product" ],
    "IF" : [
      { "commerce_ss_stock_enabled_on_product" : { "commerce_product" : [ "commerce_product" ] } },
      { "commerce_ss_stock_not_disabled" : { "commerce_product" : [ "commerce_product" ] } },
      { "data_is" : {
          "data" : [ "stock-requested-total" ],
          "op" : "\u003E",
          "value" : [ "commerce-product:commerce-stock" ]
        }
      }
    ],
    "DO" : [
      { "data_calc" : {
          "USING" : {
            "input_1" : [ "commerce-product:commerce-stock" ],
            "op" : "-",
            "input_2" : [ "stock-already-ordered" ]
          },
          "PROVIDE" : { "result" : { "approved_quantity" : "approved quantity" } }
        }
      },
      { "commerce_stock_add_to_cart_set_state" : {
          "stock_action" : "1",
          "message" : "The maximum quantity for [commerce-product:title] that can be ordered is [commerce-product:commerce-stock-int].\r\n",
          "approved_quantity" : [ "approved-quantity" ]
        }
      }
    ]
  }}';

  $rules['rules_stock_validate_add_to_cart'] = rules_import($rules_export);

  $rules_export = '{ "rules_stock_validate_checkout" : {
    "LABEL" : "Stock: validate checkout",
    "PLUGIN" : "reaction rule",
    "REQUIRES" : [ "commerce_ss", "rules", "commerce_stock" ],
    "ON" : [ "commerce_stock_check_product_checkout" ],
    "IF" : [
      { "commerce_ss_stock_enabled_on_product" : { "commerce_product" : [ "commerce_product" ] } },
      { "commerce_ss_stock_not_disabled" : { "commerce_product" : [ "commerce_product" ] } },
      { "data_is" : {
          "data" : [ "stock-already-ordered" ],
          "op" : "\u003E",
          "value" : [ "commerce-product:commerce-stock" ]
        }
      }
    ],
    "DO" : [
      { "commerce_stock_checkout_state" : {
          "stock_action" : "1",
          "message" : "The maximum quantity for [commerce-product:title] that can be ordered is [commerce-product:commerce-stock-int].",
          "approved_quantity" : [ "commerce-product:commerce-stock" ]
        }
      }
    ]
  }}';

  $rules['rules_stock_validate_checkout'] = rules_import($rules_export);

  return $rules;
}
