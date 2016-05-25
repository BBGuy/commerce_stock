<?php

/**
 * Implementation of hook_views_data()
 */
function commerce_stock_s_views_data() {
 // Define the return array.
  $data = array();

  // Stock Location
  // The outermost keys of $data are Views table names, which should usually
  // be the same as the hook_schema() table names.
  $data['cs_inventory_location'] = array();

  $data['cs_inventory_location']['table'] = array();


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
  $data['cs_inventory_location']['table']['base'] = array(
    // Identifier (primary) field in this table for Views.
    'field' => 'locid',
    // Label in the UI.
    'title' => t('Stock location'),
    // Longer description in the UI. Required.
    'help' => t('Table holding the stock locations.'),
    'weight' => -10,
  );


  // Fields.
  $data['cs_inventory_location']['locid'] = array(
    'title' => t('Location ID'),
    'help' => t('Unique Stock Location ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_location']['name'] = array(
    'title' => t('Name'),
    'help' => t('The location name.'),
    'field' => array(
      'id' => 'standard',
    ),
    'argument' => array(
      'id' => 'string',
    ),
    'filter' => array(
      'id' => 'string',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );

  $data['cs_inventory_location']['status'] = array(
    'title' => t('Status'),
    'help' => t('Is the location active.'),

    'field' => array(
      'id' => 'boolean',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'filter' => array(
      'id' => 'boolean',
      'label' => t('Active'),
      'type' => 'yes-no',
      'use_equal' => TRUE,
    ),
  );



  // Stock transactions
  $data['cs_inventory_transaction'] = array();
  $data['cs_inventory_transaction']['table'] = array();
  $data['cs_inventory_transaction']['table']['group'] = t('Stock Transactions');
  $data['cs_inventory_transaction']['table']['provider'] = 'commerce_stock_s';
  $data['cs_inventory_transaction']['table']['base'] = array(
    'field' => 'trid',
    'title' => t('Stock transaction'),
    'help' => t('Table holding the stock transactions.'),
    'weight' => -10,
  );
  // Fields.
  $data['cs_inventory_transaction']['trid'] = array(
    'title' => t('Transactions ID'),
    'help' => t('Unique Stock Transactions ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction']['variation_id'] = array(
    'title' => t('Variation ID'),
    'help' => t('The product variation ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'relationship' => array(
      'base' => 'commerce_product_variation_field_data',
      'base field' => 'variation_id',
      'id' => 'standard',
      'label' => t('Product variation relating the transaction'),
    ),
  );
  $data['cs_inventory_transaction']['qty'] = array(
    'title' => t('Qty'),
    'help' => t('The Quantity.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction']['location_id'] = array(
    'title' => t('Location ID'),
    'help' => t('The Location ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'relationship' => array(
      'base' => 'cs_inventory_location',
      'base field' => 'locid',
      'id' => 'standard',
      'label' => t('Stock Location'),
    ),
  );
  $data['cs_inventory_transaction']['location_zone'] = array(
    'title' => t('Zone'),
    'help' => t('The location zone.'),
    'field' => array(
      'id' => 'standard',
    ),
    'argument' => array(
      'id' => 'string',
    ),
    'filter' => array(
      'id' => 'string',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction']['unit_cost'] = array(
    'title' => t('Unit cost'),
    'help' => t('The cost of a single unit.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction']['transaction_time'] = array(
    'title' => t('Time'),
    'help' => t('The date & time of the transaction.'),
    'field' => array(
      'id' => 'date',
    ),
    'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
  );

  $data['cs_inventory_transaction']['transaction_type_id'] = array(
    'title' => t('Type ID'),
    'help' => t('Transaction type id.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction']['related_tid'] = array(
    'title' => t('Related transaction ID'),
    'help' => t('The Related transaction id.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction']['related_oid'] = array(
    'title' => t('Related Order ID'),
    'help' => t('The Related Order ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'relationship' => array(
      'base' => 'commerce_order',
      'base field' => 'order_id',
      'id' => 'standard',
      'label' => t('Related Order'),
    ),
  );
  $data['cs_inventory_transaction']['related_uid'] = array(
    'title' => t('Related User ID'),
    'help' => t('The Related User ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
    'relationship' => array(
      'base' => 'users_field_data',
      'base field' => 'uid',
      'id' => 'standard',
      'label' => t('User for the transaction'),
    ),
  );
  $data['cs_inventory_transaction']['data'] = array(
    'title' => t('data'),
    'help' => t('Serialized Data.'),
    'field' => array(
      'id' => 'serialized',
      'click sortable' => FALSE,
    ),
    'filter' => array(
      'id' => 'Standard',
    ),
    'argument' => array(
      'id' => 'Standard',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );





  // Stock transaction types
  $data['cs_inventory_transaction_type'] = array();
  $data['cs_inventory_transaction_type']['table'] = array();
  $data['cs_inventory_transaction_type']['table']['group'] = t('Stock Transaction Types');
  $data['cs_inventory_transaction_type']['table']['provider'] = 'commerce_stock_s';
  $data['cs_inventory_transaction_type']['table']['base'] = array(
    'field' => 'ttid',
    'title' => t('Stock transaction Type'),
    'help' => t('Table holding the stock transaction types.'),
    'weight' => -10,
  );



  $data['cs_inventory_transaction_type']['table']['join']['cs_inventory_transaction']['left_field'] = 'transaction_type_id';
  $data['cs_inventory_transaction_type']['table']['join']['cs_inventory_transaction']['field'] = 'ttid';

  // Fields.
  $data['cs_inventory_transaction_type']['ttid'] = array(
    'title' => t('Transactions Type ID'),
    'help' => t('Unique Stock Transactions Type ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction_type']['name'] = array(
    'title' => t('Transaction Type Name'),
    'help' => t('The transaction type name.'),
    'field' => array(
      'id' => 'standard',
    ),
    'argument' => array(
      'id' => 'string',
    ),
    'filter' => array(
      'id' => 'string',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );
  $data['cs_inventory_transaction_type']['parent_ttid'] = array(
    'title' => t('Parent Transactions Type ID'),
    'help' => t('The parent stock transactions type ID.'),
    'field' => array(
      'id' => 'numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'sort' => array(
      'id' => 'standard',
    ),
  );


  return $data;
}
