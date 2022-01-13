<?php

namespace Drupal\Tests\commerce_stock_field\Functional;

use Drupal\commerce_stock\StockTransactionsInterface;

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
    $this->assertSession()->elementContains('css', '.product--variation-field--variation_stock_level_test__1', '10');
  }

  /**
   * Whether the stock level dosn't get cached.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testDefaultFormatterDontCacheStockLevel() {

    $this->drupalGet('product/' . $this->product->id());
    $this->saveHtmlOutput();
    $this->assertSession()->pageTextContains('stock_level_test');
    $this->assertSession()->elementContains('css', '.product--variation-field--variation_stock_level_test__1', '10');
    $this->stockServiceManager->createTransaction($this->variation, $this->locations[1]->getId(), '', 10, 10.10, 'USD', StockTransactionsInterface::STOCK_IN, []);
    $this->drupalGet('product/' . $this->product->id());
    $this->saveHtmlOutput();
    $this->assertSession()->elementContains('css', '.product--variation-field--variation_stock_level_test__1', '20');
  }

}
