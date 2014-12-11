<?php

/**
 * @file
 * Default rule configurations for Commerce Simple Stock.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_ss_default_rules_configuration() {
  $rules = array();

  $rules_export = '{ "rules_stock_decrease_when_completing_the_order_process" : {
    "LABEL" : "Stock: Decrease when completing the order process",
    "PLUGIN" : "reaction rule",
    "TAGS" : [ "stock_control" ],
    "REQUIRES" : [ "commerce_ss", "commerce_checkout" ],
    "ON" : [ "commerce_checkout_complete" ],
    "DO" : [
      { "LOOP" : {
          "USING" : { "list" : [ "commerce-order:commerce-line-items" ] },
          "ITEM" : { "list_item" : "Current list item" },
          "DO" : [
            { "commerce_ss_decrease_by_line_item" : { "commerce_line_item" : [ "list-item" ] } }
          ]
        }
      }
    ]
  } }';

  $rules['rules_decrement_stock_completing_order'] = rules_import($rules_export);

  $rules_export ='{ "rules_increment_stock_canceling_order" : {
      "LABEL" : "Stock: Increase when canceling the order process",
      "PLUGIN" : "reaction rule",
      "ACTIVE" : false,
      "TAGS" : [ "stock_control" ],
      "REQUIRES" : [ "rules", "commerce_ss", "entity" ],
      "ON" : [ "commerce_order_update" ],
      "IF" : [
        { "data_is" : { "data" : [ "commerce-order:status" ], "value" : "canceled" } },
        { "data_is" : {
            "data" : [ "commerce-order-unchanged:status" ],
            "op" : "IN",
            "value" : { "value" : {
                "pending" : "pending",
                "processing" : "processing",
                "completed" : "completed"
              }
            }
          }
        }
      ],
      "DO" : [
        { "LOOP" : {
            "USING" : { "list" : [ "commerce-order:commerce-line-items" ] },
            "ITEM" : { "list_item" : "Current list item" },
            "DO" : [
              { "commerce_ss_increase_by_line_item" : { "commerce_line_item" : [ "list-item" ] } }
            ]
          }
        }
      ]
    }
  }';
  $rules['rules_increment_stock_canceling_order'] = rules_import($rules_export);

  return $rules;
}
