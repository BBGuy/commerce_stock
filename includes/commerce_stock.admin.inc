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
  $content['enabled']['cart_form']['rules']['#empty'] = t('There are no active Stock event rules.');
  // Disabled.
  $content['disabled']['cart_form']['title']['#markup'] = '<h3>' . t('Cart state') . '</h3>';
  $conditions['active'] = FALSE;
  $content['disabled']['cart_form']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['cart_form']['rules']['#empty'] = t('There are no disabled shipping rate calculation rules.');

  // Add to cart action.
  $content['enabled']['cart_action']['title']['#markup'] = '<h3>' . t('Add to cart action') . '</h3>';
  $conditions['event'] = 'commerce_stock_add_to_cart_check_product';
  $conditions['active'] = TRUE;
  $content['enabled']['cart_action']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['enabled']['cart_action']['rules']['#empty'] = t('There are no active Stock event rules.');
  // Disabled.
  $content['disabled']['cart_action']['title']['#markup'] = '<h3>' . t('Add to cart action') . '</h3>';
  $conditions['active'] = FALSE;
  $content['disabled']['cart_action']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['cart_action']['rules']['#empty'] = t('There are no disabled shipping rate calculation rules.');

  // Validate cart / checkout.
  $content['enabled']['cart_validate']['title']['#markup'] = '<h3>' . t('Validate cart / checkout') . '</h3>';
  $conditions['event'] = 'commerce_stock_check_product_checkout';
  $conditions['active'] = TRUE;
  $content['enabled']['cart_validate']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['enabled']['cart_validate']['rules']['#empty'] = t('There are no active Stock event rules.');
  // Disabled.
  $content['disabled']['cart_validate']['title']['#markup'] = '<h3>' . t('Validate cart / checkout') . '</h3>';
  $conditions['active'] = FALSE;
  $content['disabled']['cart_validate']['rules'] = RulesPluginUI::overviewTable($conditions, $options);
  $content['disabled']['cart_validate']['rules']['#empty'] = t('There are no disabled shipping rate calculation rules.');

  return $content;
}
