<?php

/**
 * @file
 * Hooks provided by the Commerce Stock module.
 */


/**
 * Allows modules to return a cart product level for a product id before the
 * Commerce Stock module determines it using its default queries.
 *
 * @param $product_id
 *   The product_id of the product whose cart quantity should be returned.
 *
 * @return
 *   The cart quantity (if a valid quantity was found), FALSE (if the product
 *   should have no cart product level), or NULL (if the implementation cannot
 *   tell if the product has a cart product level or not).
 */
function hook_commerce_stock_cart_product_level_alter($product_id) {
  // No example.
}
