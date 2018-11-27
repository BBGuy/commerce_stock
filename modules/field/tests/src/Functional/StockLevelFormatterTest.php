<?php

namespace Drupal\Tests\commerce_stock_field\Functional;

/**
 * Provides tests for stock level field default formatter.
 *
 * @group commerce_stock
 */
class StockLevelFormatterTest extends StockLevelFieldTestBase {

  /**
   * Setting the field name.
   */
  public function setup() {
    $this->fieldName = "Stock Level Test";
    parent::setup();
  }

  /**
   * Whether the default formatter appears on product add to cart forms.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testDefaultFormatter() {

    $this->drupalGet('product/' . $this->product->id());
    $this->saveHtmlOutput();
    $this->assertSession()->pageTextContains('stock_level_test');
    $this->assertSession()->elementContains('css', '.field--name-stock-level-test p', '10');
  }

}
