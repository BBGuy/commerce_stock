<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;

/**
 * Plugin implementation of the 'commerce_stock_level_simple' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_stock_level_simple",
 *   module = "commerce_stock_field",
 *   label = @Translation("Simple stock level formatter"),
 *   field_types = {
 *     "commerce_stock_level"
 *   }
 * )
 */
class SimpleStockLevelFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    // Get the entity.
    $entity = $items->getEntity();
    // @todo Support any purchasable entity.
    if ($entity instanceof ProductVariationInterface) {
      // Get the available Stock for the product variation.
      $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
      $level = $stockServiceManager->getStockLevel($entity);
    }
    else {
      // No stock if this is not a product variation.
      $level = 0;
    }
    $elements = [];
    // It only makes sense for one item, so we will treat all the same.
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $level,
      ];
    }
    return $elements;
  }

}
