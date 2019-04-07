<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'commerce_stock_level_simple_transaction' widget.
 *
 * @FieldWidget(
 *   id = "commerce_stock_level_simple_transaction",
 *   label = @Translation("Simple stock transaction"),
 *   description = @Translation("Do simple stock transactions (add, remove) on
 *   the edit form of a purchasable entity."),
 *   field_types = {
 *     "commerce_stock_level"
 *   }
 * )
 */
class SimpleStockTransactionWidget extends StockLevelWidgetBase {

  /**
   * @inheritdoc
   */
  protected function getHelpText() {
    return $this->t('Simple stock adjustments right on the product edit form. We recommend using this widget. Learn in the docs why.');
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    // @ToDo ASAP we should add a link to some documentation to provide some
    // @ToDo Background why we don't support default values.
    if ($this->isDefaultValueWidget($form_state)) {
      $element['#description'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Default values for stock transactions are not supported.'),
      ];
      return $element;
    }
    return parent::formElement($items, $delta, $element, $form, $form_state);
  }

}
