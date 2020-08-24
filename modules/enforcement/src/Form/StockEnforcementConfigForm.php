<?php

namespace Drupal\commerce_stock_enforcement\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The stock enforcement configuration form.
 */
class StockEnforcementConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_stock_enforcement_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the default service.
    $config = $this->config('commerce_stock_enforcement.settings');

    $message_data = [
      'insufficient_stock_cart' => [
        'title' => 'Insufficient stock: Cart page',
        'description' => 'This is shown on the cart page on load and when submitted.',
        'tokens' => [
          '%name: ' . $this->t('The title of the purchased entity.'),
          '%qty: ' . $this->t('The stock level of the item.'),
        ],
      ],
      'insufficient_stock_add_to_cart_zero_in_cart' => [
        'title' => 'Insufficient stock: Add to cart form (0 in cart)',
        'description' => 'This is shown on the add to cart form when the customer doesn\'t have any of this item in their cart already.',
        'tokens' => [
          '%qty: ' . $this->t('The stock level of the item.'),
          '%qty_asked: ' . $this->t('The quantity requested.'),
        ],
      ],
      'insufficient_stock_add_to_cart_quantity_in_cart' => [
        'title' => 'Insufficient stock: Add to cart form (quantity in cart)',
        'description' => 'This is shown on the add to cart form when the customer has this item in their cart already.',
        'tokens' => [
          '%qty: ' . $this->t('The stock level of the item.'),
          '%qty_o: ' . $this->t('The quantity already in cart.'),
        ],
      ],
    ];

    // Message customisation.
    $message_settings = 'message_settings';
    $form[$message_settings] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Message text'),
    ];

    // Add the textarea for each message.
    foreach ($message_data as $element_name => $data) {
      $form[$message_settings][$element_name] = [
        '#type' => 'textarea',
        '#title' => $this->t('@title', ['@title' => $data['title']]),
        '#default_value' => $config->get($element_name) ?? '',
      ];

      // Add the token info.
      $list = [
        '#theme' => 'item_list',
        '#items' => $data['tokens'],
        '#prefix' => ' ' . $this->t('Available tokens:'),
      ];

      $form[$message_settings][$element_name]['#description'][] = [
        '#markup' => $this->t('@data', ['@data' => $data['description']]),
      ];
      $form[$message_settings][$element_name]['#description'][] = $list;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('commerce_stock_enforcement.settings');
    $config->set('insufficient_stock_cart', $values['insufficient_stock_cart']);
    $config->set('insufficient_stock_add_to_cart_zero_in_cart', $values['insufficient_stock_add_to_cart_zero_in_cart']);
    $config->set('insufficient_stock_add_to_cart_quantity_in_cart', $values['insufficient_stock_add_to_cart_quantity_in_cart']);
    $config->save();

    $this->messenger()->addMessage($this->t('Stock enforcement settings updated.'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_stock_enforcement.settings',
    ];
  }

}
