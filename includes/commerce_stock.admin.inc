<?php

/**
 * @file
 * Administrative callbacks and form builder functions for Commerce Stock.
 */

/**
 * Commerce Stock admin form.
 */
function commerce_stock_admin_form($form, &$form_state) {
  $form['confirmation'] = array(
    '#title' => 'Commerce Stock API',
    '#markup' => 'Commerce stock API enabled',
  );
  return $form;
}

/**
 * Builds the stock events page.
 */
function commerce_stock_admin_event_ruless() {

  // Add a help section.
  $content['help'] = array(
    '#type' => 'fieldset',
    '#title' => t('Stock validation'),
  );
  $content['help']['about']['#markup']
    = t('Manage stock validation rules. Those are rules that manage availability of products to your online shoppers.');

  RulesPluginUI::$basePath = 'admin/commerce/config/stock/validation';
  $options = array('show plugin' => FALSE);

  $content['enabled'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enabled Stock events'),
  );
  $content['disabled'] = array(
    '#type' => 'fieldset',
    '#title' => t('Disabled Stock events'),
  );

  // The conditions array.
  $conditions = array('plugin' => 'reaction rule');

  // Add to cart state.
  $content['enabled']['cart_form']['title']['#markup'] = '<h3>' . t('Cart state') . '</h3>';
  $conditions['event'] = 'commerce_stock_check_add_to_cart_form_state';
  $conditions['active'] = TRUE;
  $content['enabled']['cart_form']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['enabled']['cart_form']['rules']['#empty'] = t('There are no active Cart state rules.');
  // Disabled.
  $content['disabled']['cart_form']['title']['#markup'] = '<h3>' . t('Cart state') . '</h3>';
  $conditions['active'] = FALSE;
  $content['disabled']['cart_form']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['cart_form']['rules']['#empty'] = t('There are no disabled Cart state rules.');

  // Add to cart action.
  $content['enabled']['cart_action']['title']['#markup'] = '<h3>' . t('Add to cart action') . '</h3>';
  $conditions['event'] = 'commerce_stock_add_to_cart_check_product';
  $conditions['active'] = TRUE;
  $content['enabled']['cart_action']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['enabled']['cart_action']['rules']['#empty'] = t('There are no active Add to cart action rules.');
  // Disabled.
  $content['disabled']['cart_action']['title']['#markup'] = '<h3>' . t('Add to cart action') . '</h3>';
  $conditions['active'] = FALSE;
  $content['disabled']['cart_action']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['cart_action']['rules']['#empty'] = t('There are no disabled cart action rules.');

  // Validate cart / checkout.
  $content['enabled']['cart_validate']['title']['#markup'] = '<h3>' . t('Validate cart / checkout') . '</h3>';
  $conditions['event'] = 'commerce_stock_check_product_checkout';
  $conditions['active'] = TRUE;
  $content['enabled']['cart_validate']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['enabled']['cart_validate']['rules']['#empty'] = t('There are no active Validate cart / checkout rules.');
  // Disabled.
  $content['disabled']['cart_validate']['title']['#markup'] = '<h3>' . t('Validate cart / checkout') . '</h3>';
  $conditions['active'] = FALSE;
  $content['disabled']['cart_validate']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['cart_validate']['rules']['#empty'] = t('There are no disabled Validate cart / checkout rules.');

  return $content;
}


/**
 * Builds the stock control rules page.
 */
function commerce_stock_admin_stock_control_ruless() {

  // Add a help section.
  $content['help'] = array(
    '#type' => 'fieldset',
    '#title' => t('Stock control'),
  );
  $content['help']['about']['#markup']
    = t('<p>Manage Stock control / backend rules. Those are rules that effect and act on stock levels.</p>');
  $content['help']['add_existing']['#markup']
    = t('<p>To add existing rules to the stock control management screen, simply tag them with <strong>stock_control</strong>.</p>');

  RulesPluginUI::$basePath = 'admin/commerce/config/stock/control';
  $options = array('show plugin' => FALSE);

  $content['enabled'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enabled Stock control rules'),
  );
  $content['disabled'] = array(
    '#type' => 'fieldset',
    '#title' => t('Disabled Stock control rules'),
  );

  // The conditions array.
  $conditions = array('plugin' => 'reaction rule');
  $conditions['tags'] = array('stock_control');

  // Enabled rules.
  $conditions['active'] = TRUE;
  $content['enabled']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['enabled']['rules']['#empty'] = t('There are no active rules.');

  // Disabled rules.
  $conditions['active'] = FALSE;
  $content['disabled']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['rules']['#empty'] = t('There are no disabled rules.');

  return $content;
}
