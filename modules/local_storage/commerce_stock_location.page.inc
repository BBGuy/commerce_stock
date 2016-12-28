<?php

/**
 * @file
 * Contains commerce_stock_location.page.inc.
 *
 * Page callback for Stock Location entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Stock Location templates.
 *
 * Default template: commerce_stock_location.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_stock_location(array &$variables) {
  // Fetch StockLocation Entity Object.
  $commerce_stock_location = $variables['elements']['#commerce_stock_location'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
