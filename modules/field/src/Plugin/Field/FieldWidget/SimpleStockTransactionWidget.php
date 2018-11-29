<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldWidget;

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

}
