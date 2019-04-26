<?php

namespace Drupal\Tests\commerce_stock_ui\Functional;

use Drupal\Tests\commerce_stock_field\Kernel\StockLevelFieldCreationTrait;
use Drupal\Tests\commerce_stock_local\Kernel\StockTransactionQueryTrait;
use Drupal\Tests\commerce_stock_field\Functional\StockLevelFieldTestBase;

/**
 * Provides tests for the stock level widget.
 *
 * @group commerce_stock
 */
class StockLevelWidgetsTest extends StockLevelFieldTestBase {

  use StockTransactionQueryTrait;
  use StockLevelFieldCreationTrait;

  /**
   * Testing the commerce_stock_level_transaction_form_link widget.
   */
  public function testLinkToTransactionFormWidget() {
    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $widget_id = "commerce_stock_level_transaction_form_link";
    $widget_settings = [];

    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists(t('transaction form'));
    $this->clickLink(t('transaction form'));
    $this->assertSession()->statusCodeEquals(200);
    $path = '/admin/commerce/config/stock/transactions2';
    $this->assertSession()->addressEquals($path);
  }

}
