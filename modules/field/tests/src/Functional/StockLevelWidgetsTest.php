<?php

namespace Drupal\Tests\commerce_stock_field\Functional;

/**
 * Provides tests for the stock level widget.
 *
 * @group commerce_stock
 */
class StockLevelWidgetsTest extends StockLevelFieldTestBase {

  /**
   * Tests the commerce_stock_level_simple widget.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testWidgetsOnEditProductVariationForm() {

    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $widget_type = 'commerce_stock_level_simple';
    $widget_settings = [
      'entry_system' => 'basic',
      'transaction_note' => FALSE,
      'context_fallback' => FALSE,
    ];
    $this->configureFormDisplay($widget_type, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);

    // Ensure the stock part of the form is healty.
    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()
      ->checkboxNotChecked('commerce_stock_always_in_stock[value]');
    $this->assertSession()->fieldExists($this->fieldName . '[0][stock][adjustment]');
    $this->assertSession()->pageTextContains('Stock level');

    $widget_settings = [
      'entry_system' => 'simple',
      'transaction_note' => FALSE,
      'context_fallback' => FALSE,
    ];
    $this->configureFormDisplay($widget_type, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists($this->fieldName . '[0][stock][value]');
    $this->assertSession()->pageTextContains('Total stock level available for this item.');

    $widget_settings = [
      'entry_system' => 'transactions',
      'transaction_note' => FALSE,
      'context_fallback' => FALSE,
    ];
    $this->configureFormDisplay($widget_type, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();
    $this->assertSession()->linkExists('New transaction');
  }

}
