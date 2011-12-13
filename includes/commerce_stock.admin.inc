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
