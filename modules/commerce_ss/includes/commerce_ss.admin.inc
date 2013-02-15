<?php

/**
 * @file
 * Administrative callbacks and form builder functions for Commerce Stock.
 */

/**
 * Commerce Stock admin form.
 */
function commerce_ss_admin_form($form, &$form_state) {
  // Find out what our status is. Use both the state of existing fields
  // and the state of variables to determine what's right.

  $field_name = 'commerce_stock';
  $field = field_info_field($field_name);

  $form['#tree'] = TRUE;

  $form['product_types'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enable stock management for these product types'),
    '#description' => t('Note that disabling stock management removes the Stock field from the product type, deleting any existing stock data for that product type.')
  );

  $form['product_types_override'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enable stock management override for these product types'),
    '#description' => t('Note that disabling stock management override removes the Stock override field from the product type, deleting any existing stock override data for that product type.')
  );

  // Create a checkbox for each product type, set with the current stock-
  // enabled state.
  foreach (commerce_product_types() as $type => $product_type) {
    $instance[$type] = field_info_instance('commerce_product', 'commerce_stock', $type);
    $enabled[$type] = (!empty($instance[$type]));

    $form['product_types'][$type] = array(
      '#type' => 'checkbox',
      '#default_value' => $enabled[$type],
      '#title' => t('@name (@machine_name)', array('@name' => $product_type['name'], '@machine_name' => $type)),
    );

    if ($enabled[$type]) {
      $instance[$type] = field_info_instance('commerce_product', 'commerce_stock_override', $type);
      $enabled[$type] = (!empty($instance[$type]));

      $form['product_types_override'][$type] = array(
        '#type' => 'checkbox',
        '#default_value' => $enabled[$type],
        '#title' => t('Allow stock override for @name (@machine_name)', array('@name' => $product_type['name'], '@machine_name' => $type)),
      );
    }
  }

  // Add a checkbox that requires them to say "I do", but don't show it
  // (#access == FALSE) unless they're deleting.
  if (!empty($form_state['commerce_stock']['delete_instances'])) {
    $type_plural = format_plural(count($form_state['commerce_stock']['delete_instances']), t('type'), t('types'));
    $affirmation = t('I understand that all stock data will be permanently removed from the product @type_plural %product_types.',
      array(
        '@type_plural' => $type_plural,
        '%product_types' => implode(', ', $form_state['commerce_stock']['delete_instances']),
      )
    );
  }
  $form['confirmation'] = array(
    '#type' => 'checkbox',
    '#title' => !empty($affirmation) ? $affirmation : '',
    '#default_value' => FALSE,
    '#access' => FALSE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  // If they're deleting, show the confirmation checkbox.
  if (!empty($form_state['commerce_stock']['delete_instances'])) {
    $form['confirmation']['#access'] = TRUE;
    drupal_set_message(t('You must click the confirmation checkbox to confirm that you want to delete stock data'), 'warning');
  }

  return $form;
}

/**
 * Form validator. If they are deleting and have not checked the confirmation
 * checkbox, make them do so.
 */
function commerce_ss_admin_form_validate($form, &$form_state) {
  if (!empty($form_state['commerce_stock']['delete_instances']) && empty($form_state['values']['confirmation'])) {
    form_set_error('confirmation', t('Please check the "I understand" checkbox to indicate you understand that all stock data in these fields will be deleted: %fields.', array('%fields' => implode(', ', $form_state['commerce_stock']['delete_instances']))));
  }
}

/**
 * Add or remove the Stock field from product types.
 */
function commerce_ss_admin_form_submit($form, &$form_state) {
  $form_state['commerce_stock']['delete_instances'] = array();

  // Prepare a batch in case we need it for enabling stock on product types.
  $batch = array(
    'operations' => array(
      // These are set below if needed.
    ),
    'file' => drupal_get_path('module', 'commerce_ss') . '/includes/commerce_ss.admin.inc',
    'finished' => 'commerce_ss_batch_product_type_init_finished',
    'title' => t('Product stock initialization'),
    'init_message' => t('Product stock initialization is starting.'),
    'progress_message' => t('Processed @current out of @total.'),
    'error_message' => t('Product stock initialization has encountered an error.'),
  );

  foreach ($form_state['values']['product_types'] as $type => $enable) {
    $instance = field_info_instance('commerce_product', 'commerce_stock', $type);

    $currently_enabled = commerce_ss_product_type_enabled($type);
    // If they want us to enable it and it doesn't currently exist, do the work.
    if ($enable && !$currently_enabled) {
      // Create the instance.
      commerce_ss_admin_create_instance('commerce_stock', 'number_decimal', TRUE, 'commerce_product', $type, t('Stock'));
      drupal_set_message(t('Stock field has been added to the %type product type.', array('%type' => $type)));
      // Add the operation to process this type to the batch.
      $batch['operations'][] = array('commerce_ss_batch_product_type_init_process', array($type, 0));
    }
    // Conversely, if they *don't* want it and it's currently enabled,
    // warn them about the consequences or do it.
    else if (!$enable && $currently_enabled) {
      // If they haven't clicked the "confirm" checkbox, rebuild and get them
      // to do it.
      if (empty($form_state['values']['confirmation'])) {
        $form_state['commerce_stock']['delete_instances'][] = $type;
        $form_state['rebuild'] = TRUE;
      }
      // Otherwise they already have clicked it and we can delete.
      else {
        // Remove the instance.
        field_delete_instance($instance);

        // Remove override if enabled
        if (commerce_ss_product_type_override_enabled($type)) {
          $override = field_info_instance('commerce_product', 'commerce_stock_override', $type);
          field_delete_instance($override);
        }

        drupal_set_message(t('Stock management has been disabled on the %type product type', array('%type' => $type)));
      }
    }
  }

  if (!empty($form_state['values']['product_types_override'])) {
    foreach ($form_state['values']['product_types_override'] as $type => $enable) {
      $instance = field_info_instance('commerce_product', 'commerce_stock_override', $type);

      $currently_enabled = commerce_ss_product_type_override_enabled($type);
      $stock_enabled = commerce_ss_product_type_enabled($type);
      // If they want us to enable it and it doesn't currently exist, do the work.
      if ($enable && $stock_enabled && !$currently_enabled) {
        commerce_ss_admin_create_instance('commerce_stock_override', 'list_boolean', FALSE, 'commerce_product', $type, t('Disable stock for this product'));
        drupal_set_message(t('Stock management override has been enabled on the %type product type', array('%type' => $type)));
      }
      // Conversely, if they *don't* want it and it's currently enabled,
      // warn them about the consequences or do it.
      else if (!$enable && $currently_enabled) {
        // If they haven't clicked the "confirm" checkbox, rebuild and get them
        // to do it.
        if (empty($form_state['values']['confirmation'])) {
          $form_state['commerce_stock']['delete_instances'][] = $type;
          $form_state['rebuild'] = TRUE;
        }
        // Otherwise they already have clicked it and we can delete.
        else {
          // Remove the instance.
          field_delete_instance($instance);
          drupal_set_message(t('Stock management override has been disabled on the %type product type', array('%type' => $type)));
        }
      }
    }
  }

  // If our batch has operations, run it now.
  if (count($batch['operations'])) {
    batch_set($batch);
  }
}

/**
 * Batch Operation Callback.
 *
 * @param $type
 *  The product type to process.
 * @param $init_stock_value
 *  The initial value to give to the stock field.
 */
function commerce_ss_batch_product_type_init_process($type, $init_stock_value, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;

    // Get the products to operate on.
    $result = db_query("SELECT product_id FROM {commerce_product} cp WHERE cp.type = :type", array(
      ':type' => $type,
    ));
    $context['sandbox']['product_data'] = $result->fetchAll();
    $context['sandbox']['max'] = count($context['sandbox']['product_data']);
  }

  // We can safely process 10 at a time without a timeout.
  $limit = 25;
  $current_count = 0;

  while ($current_count < $limit && count($context['sandbox']['product_data'])) {
    // Update our counts.
    $current_count++;
    $context['sandbox']['progress']++;

    //ddl("$current_count < $limit, progress: " . $context['sandbox']['progress']);
    // Load the product and get its wrapper.
    $product_data = array_shift($context['sandbox']['product_data']);
    $product = commerce_product_load($product_data->product_id);

    $wrapper = entity_metadata_wrapper('commerce_product', $product);
    $wrapper->commerce_stock = $init_stock_value; //@todo: the name of the stock field should probably be a variable for commerce_stock v2
    $wrapper->save();

    $context['message'] = t('Processing product type %type %progress% complete.', array(
      '%type' => $type,
      '%progress' => intval( $context['sandbox']['progress'] / $context['sandbox']['max'] * 100 ),
    ));
  } // product processing loop

  // Inform the batch engine that we are not finished,
  // and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] >= $context['sandbox']['max'];
  }
}

/**
 * Batch 'finished' callback.
 */
function commerce_ss_batch_product_type_init_finished($success, $results, $operations) {
  if ($success) {
    // We display the number of things we processed...
    drupal_set_message(t('All stock levels have been initialized to zero'));

    // @todo: get the batch process to provide more meaningfull information
    // using $context['results']
//    drupal_set_message(t('@count results processed.', array(
//      '@count' => count($results),
//    )));
//    foreach ($results as $result) {
//      drupal_set_message($result);
//    }
  }
  else {
    // An error occurred.
    // $operations contains the operations that remained unprocessed.
    $error_operation = reset($operations);
    drupal_set_message(t('An error occurred while processing @operation with arguments : @args', array('@operation' => $error_operation[0], '@args' => print_r($error_operation[0], TRUE))));
  }
}

/**
 * Ensures a stock field is present on a product type bundle.
 */
function commerce_ss_admin_configure_product_type($type) {
  commerce_ss_admin_create_instance('commerce_stock', 'number_decimal', TRUE, 'commerce_product', $type, t('Stock'));
}

/**
 * Creates a required instance of a stock field on the specified bundle.
 *
 * @param $field_name
 *   The name of the field; if it already exists, a new instance of the existing
 *   field will be created. For fields governed by the Commerce modules, this
 *   should begin with commerce_.
 * @param $entity_type
 *   The type of entity the field instance will be attached to.
 * @param $bundle
 *   The bundle name of the entity the field instance will be attached to.
 * @param $label
 *   The label of the field instance.
 * @param $weight
 *   The default weight of the field instance widget and display.
 */
function commerce_ss_admin_create_instance($field_name, $field_type, $required, $entity_type, $bundle, $label, $description = NULL, $weight = 0) {
  // If a field type we know should exist isn't found, clear the Field cache.
//  if (!field_info_field_types('commerce_stock')) {
//    field_cache_clear();
//  }

  // Look for or add the specified stock field to the requested entity bundle.
  $field = field_info_field($field_name);
  $instance = field_info_instance($entity_type, $field_name, $bundle);

  if (empty($field)) {
    $field = array(
      'field_name' => $field_name,
      'type' => $field_type,
      'cardinality' => 1,
      'entity_types' => array($entity_type),
      'translatable' => FALSE,
      'locked' => FALSE,
    );
    if ($field_type == 'list_boolean') {
      $field['settings'] = array(
        'allowed_values' => array(0, 1),
        'allowed_values_function' => '',
      );
    }
    $field = field_create_field($field);
  }

  if (empty($instance)) {
    $instance = array(
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'required' => $required,
      'settings' => array(),
      'display' => array(),
      'description' => $description,
      'default_value' => array(array('value' => "0")),
    );

    if ($field_type == 'list_boolean') {
      $instance['widget'] = array(
        'type' => 'options_onoff',
        'settings' => array(
        'display_label' => TRUE,
       ),
     );
    }

    $entity_info = entity_get_info($entity_type);

    // Spoof the default view mode so its display type is set.
    $entity_info['view modes']['default'] = array();

    field_create_instance($instance);
  }
}
