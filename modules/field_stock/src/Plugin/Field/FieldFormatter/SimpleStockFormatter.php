<?php

namespace Drupal\field_stock\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;

/**
 * Plugin implementation of the 'field_stock_level_simple' formatter.
 *
 * @FieldFormatter(
 *   id = "field_stock_level_simple",
 *   module = "field_stock",
 *   label = @Translation("Simple stock formatter"),
 *   field_types = {
 *     "field_stock_level"
 *   }
 * )
 */
class SimpleStockFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    // Get the entity.
    $entity = $items->getEntity();
    // Make sure entity is a product variation.
    if ($entity instanceof ProductVariationInterface) {
      // Get the available Stock for the product variation.
      $stockManager = \Drupal::service('commerce.stock_manager');
      $level = $stockManager->getStockLevel($entity);
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
