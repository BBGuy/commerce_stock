<?php

/**
 * @file
 * Commerce Stock Storage Views callbacks.
 */

/**
 * Implements hook_views_data().
 */
function commerce_stock_local_views_data() {
  // Define the return array.
  $data = [];

  // Stock transactions.
  $data['commerce_stock_transaction'] = [];
  $data['commerce_stock_transaction']['table'] = [];
  $data['commerce_stock_transaction']['table']['group'] = t('Commerce Stock Local');
  $data['commerce_stock_transaction']['table']['provider'] = 'commerce_stock_local';
  $data['commerce_stock_transaction']['table']['base'] = [
    'field' => 'id',
    'title' => t('Commerce Stock Local transaction'),
    'help' => t('Table holding the stock transactions.'),
    'weight' => -10,
  ];
  // Fields.
  $data['commerce_stock_transaction']['id'] = [
    'title' => t('Transaction ID'),
    'help' => t('The transaction ID.'),
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
  $data['commerce_stock_transaction']['entity_id'] = [
    'title' => t('Purchasable entity ID'),
    'help' => t('The purchasable entity ID.'),
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
  $data['commerce_stock_transaction']['entity_type'] = [
    'title' => t('Purchasable entity type'),
    'help' => t('The purchasable entity type.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['commerce_stock_transaction']['qty'] = [
    'title' => t('Qty'),
    'help' => t('The stock transaction quantity.'),
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
  $data['commerce_stock_transaction']['location_id'] = [
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
      'base' => 'commerce_stock_location_field_data',
      'base field' => 'location_id',
      'id' => 'standard',
      'label' => t('Stock Location'),
    ],
  ];
  $data['commerce_stock_transaction']['location_zone'] = [
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
  $data['commerce_stock_transaction']['unit_cost'] = [
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
  $data['commerce_stock_transaction']['currency_code'] = [
    'title' => t('Currency code'),
    'help' => t('The currency code of a single unit.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['commerce_stock_transaction']['transaction_time'] = [
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

  $data['commerce_stock_transaction']['transaction_type_id'] = [
    'title' => t('Type ID'),
    'help' => t('Transaction type ID.'),
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
  $data['commerce_stock_transaction']['related_tid'] = [
    'title' => t('Related transaction ID'),
    'help' => t('The related transaction ID.'),
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
  $data['commerce_stock_transaction']['related_oid'] = [
    'title' => t('Related order ID'),
    'help' => t('The related order ID.'),
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
  $data['commerce_stock_transaction']['related_uid'] = [
    'title' => t('Related user ID'),
    'help' => t('The related user ID.'),
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
  $data['commerce_stock_transaction']['data'] = [
    'title' => t('data'),
    'help' => t('The serialized transaction data.'),
    'field' => [
      'id' => 'serialized',
      'click sortable' => FALSE,
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  // Stock transaction types.
  $data['commerce_stock_transaction_type'] = [];
  $data['commerce_stock_transaction_type']['table'] = [];
  $data['commerce_stock_transaction_type']['table']['group'] = t('Commerce Stock Local');
  $data['commerce_stock_transaction_type']['table']['provider'] = 'commerce_stock_local';
  $data['commerce_stock_transaction_type']['table']['base'] = [
    'field' => 'id',
    'title' => t('Commerce Stock Local transaction type'),
    'help' => t('Table holding the local stock transaction types.'),
    'weight' => -10,
  ];

  $data['commerce_stock_transaction_type']['table']['join']['commerce_stock_transaction']['left_field'] = 'transaction_type_id';
  $data['commerce_stock_transaction_type']['table']['join']['commerce_stock_transaction']['field'] = 'id';

  // Fields.
  $data['commerce_stock_transaction_type']['id'] = [
    'title' => t('Transaction type ID'),
    'help' => t('The transaction type ID.'),
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
  $data['commerce_stock_transaction_type']['name'] = [
    'title' => t('Transaction type name'),
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
  $data['commerce_stock_transaction_type']['parent_id'] = [
    'title' => t('Parent transaction type ID'),
    'help' => t('The parent transaction type ID.'),
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
