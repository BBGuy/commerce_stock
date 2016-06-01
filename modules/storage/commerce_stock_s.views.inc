<?php

/**
 * @file
 * Views integration for the stock storage module.
 */

/**
 * Implements hook_views_data().
 */
function commerce_stock_s_views_data() {
  // Define the return array.
  $data = [];

  // Stock Location
  // The outermost keys of $data are Views table names, which should usually
  // be the same as the hook_schema() table names.
  $data['cs_inventory_location'] = [];

  $data['cs_inventory_location']['table'] = [];

  // Within 'table', the value of 'group' (translated string) is used as a
  // prefix in Views UI for this table's fields, filters, etc. When adding
  // a field, filter, etc. you can also filter by the group.
  $data['cs_inventory_location']['table']['group'] = t('Stock Location');

  // Within 'table', the value of 'provider' is the module that provides schema
  // or the entity type that causes the table to exist. Setting this ensures
  // that views have the correct dependencies. This is automatically set to the
  // module that implements hook_views_data().
  $data['cs_inventory_location']['table']['provider'] = 'commerce_stock_s';

  // Some tables are "base" tables, meaning that they can be the base tables
  // for views. Non-base tables can only be brought in via relationships in
  // views based on other tables. To define a table to be a base table, add
  // key 'base' to the 'table' array:
  $data['cs_inventory_location']['table']['base'] = [
    // Identifier (primary) field in this table for Views.
    'field' => 'locid',
    // Label in the UI.
    'title' => t('Stock location'),
    // Longer description in the UI. Required.
    'help' => t('Table holding the stock locations.'),
    'weight' => -10,
  ];

  // Fields.
  $data['cs_inventory_location']['locid'] = [
    'title' => t('Location ID'),
    'help' => t('Unique Stock Location ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_location']['name'] = [
    'title' => t('Name'),
    'help' => t('The location name.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['cs_inventory_location']['status'] = [
    'title' => t('Status'),
    'help' => t('Is the location active.'),

    'field' => [
      'id' => 'boolean',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'boolean',
      'label' => t('Active'),
      'type' => 'yes-no',
      'use_equal' => TRUE,
    ],
  ];

  // Stock transactions.
  $data['cs_inventory_transaction'] = [];
  $data['cs_inventory_transaction']['table'] = [];
  $data['cs_inventory_transaction']['table']['group'] = t('Stock Transactions');
  $data['cs_inventory_transaction']['table']['provider'] = 'commerce_stock_s';
  $data['cs_inventory_transaction']['table']['base'] = [
    'field' => 'trid',
    'title' => t('Stock transaction'),
    'help' => t('Table holding the stock transactions.'),
    'weight' => -10,
  ];
  // Fields.
  $data['cs_inventory_transaction']['trid'] = [
    'title' => t('Transactions ID'),
    'help' => t('Unique Stock Transactions ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction']['variation_id'] = [
    'title' => t('Variation ID'),
    'help' => t('The product variation ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'relationship' => [
      'base' => 'commerce_product_variation_field_data',
      'base field' => 'variation_id',
      'id' => 'standard',
      'label' => t('Product variation relating the transaction'),
    ],
  ];
  $data['cs_inventory_transaction']['qty'] = [
    'title' => t('Qty'),
    'help' => t('The Quantity.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction']['location_id'] = [
    'title' => t('Location ID'),
    'help' => t('The Location ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'relationship' => [
      'base' => 'cs_inventory_location',
      'base field' => 'locid',
      'id' => 'standard',
      'label' => t('Stock Location'),
    ],
  ];
  $data['cs_inventory_transaction']['location_zone'] = [
    'title' => t('Zone'),
    'help' => t('The location zone.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction']['unit_cost'] = [
    'title' => t('Unit cost'),
    'help' => t('The cost of a single unit.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction']['transaction_time'] = [
    'title' => t('Time'),
    'help' => t('The date & time of the transaction.'),
    'field' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
    'filter' => [
      'id' => 'date',
    ],
  ];

  $data['cs_inventory_transaction']['transaction_type_id'] = [
    'title' => t('Type ID'),
    'help' => t('Transaction type id.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction']['related_tid'] = [
    'title' => t('Related transaction ID'),
    'help' => t('The Related transaction id.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction']['related_oid'] = [
    'title' => t('Related Order ID'),
    'help' => t('The Related Order ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'relationship' => [
      'base' => 'commerce_order',
      'base field' => 'order_id',
      'id' => 'standard',
      'label' => t('Related Order'),
    ],
  ];
  $data['cs_inventory_transaction']['related_uid'] = [
    'title' => t('Related User ID'),
    'help' => t('The Related User ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'relationship' => [
      'base' => 'users_field_data',
      'base field' => 'uid',
      'id' => 'standard',
      'label' => t('User for the transaction'),
    ],
  ];
  $data['cs_inventory_transaction']['data'] = [
    'title' => t('data'),
    'help' => t('Serialized Data.'),
    'field' => [
      'id' => 'serialized',
      'click sortable' => FALSE,
    ],
    'filter' => [
      'id' => 'Standard',
    ],
    'argument' => [
      'id' => 'Standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  // Stock transaction types.
  $data['cs_inventory_transaction_type'] = [];
  $data['cs_inventory_transaction_type']['table'] = [];
  $data['cs_inventory_transaction_type']['table']['group'] = t('Stock Transaction Types');
  $data['cs_inventory_transaction_type']['table']['provider'] = 'commerce_stock_s';
  $data['cs_inventory_transaction_type']['table']['base'] = [
    'field' => 'ttid',
    'title' => t('Stock transaction Type'),
    'help' => t('Table holding the stock transaction types.'),
    'weight' => -10,
  ];

  $data['cs_inventory_transaction_type']['table']['join']['cs_inventory_transaction']['left_field'] = 'transaction_type_id';
  $data['cs_inventory_transaction_type']['table']['join']['cs_inventory_transaction']['field'] = 'ttid';

  // Fields.
  $data['cs_inventory_transaction_type']['ttid'] = [
    'title' => t('Transactions Type ID'),
    'help' => t('Unique Stock Transactions Type ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction_type']['name'] = [
    'title' => t('Transaction Type Name'),
    'help' => t('The transaction type name.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['cs_inventory_transaction_type']['parent_ttid'] = [
    'title' => t('Parent Transactions Type ID'),
    'help' => t('The parent stock transactions type ID.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}
