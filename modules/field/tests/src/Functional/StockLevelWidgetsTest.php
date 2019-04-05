<?php

namespace Drupal\Tests\commerce_stock_field\Functional;

use Behat\Mink\Exception\ExpectationException;
use Drupal\Tests\commerce_stock\Kernel\StockLevelFieldCreationTrait;
use Drupal\Tests\commerce_stock\Kernel\StockTransactionQueryTrait;

/**
 * Provides tests for the stock level widget.
 *
 * @group commerce_stock
 */
class StockLevelWidgetsTest extends StockLevelFieldTestBase {

  use StockTransactionQueryTrait;
  use StockLevelFieldCreationTrait;

  /**
   * Tests the default simple transaction widget.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testSimpleTransactionStockLevelWidget() {

    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);

    // Ensure the stock part of the form is healty.
    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()
      ->checkboxNotChecked('commerce_stock_always_in_stock[value]');

    // Check the defaults.
    $this->assertSession()->fieldExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldDisabled($this->fieldName . '[0][stock_transaction_note]');

    $widget_id = "commerce_stock_level_simple_transaction";
    $default_note = $this->randomString(200);
    $widget_settings = [
      'custom_transaction_note' => FALSE,
      'default_transaction_note' => $default_note,
      'step' => '1',
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldDisabled($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals($this->fieldName . '[0][stock_transaction_note]', $default_note);

    $this->saveHtmlOutput();

    $adjustment = 6;
    $new_price_amount = '1.11';
    $variations_edit = [
      'price[0][number]' => $new_price_amount,
      $this->fieldName . '[0][adjustment]' => $adjustment,
    ];
    $this->submitForm($variations_edit, 'Save');

    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();

    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    $this->assertEquals($adjustment, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertTrue($default_note, $data['message']);

    $widget_settings = [
      'custom_transaction_note' => TRUE,
      'default_transaction_note' => $default_note,
      'step' => '1',
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    self::setExpectedException(ExpectationException::class);
    $this->assertSession()
      ->fieldDisabled($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals($this->fieldName . '[0][stock_transaction_note]', $default_note);

    $adjustment = -3;
    $edit = [
      $this->fieldName . '[0][adjustment]' => $adjustment,
      $this->fieldName . '[0][stock_transaction_note]' => 'CustomNote',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);

    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    $this->assertEquals($adjustment, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertTrue('CustomNote', $data['message']);

  }

  /**
   * Testing the commerce_stock_level_absolute widget.
   */
  public function testAbsoluteStockLevelWidget() {

    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $default_note = $this->randomString(100);

    $widget_id = "commerce_stock_level_absolute";
    $widget_settings = [
      'custom_transaction_note' => FALSE,
      'default_transaction_note' => $default_note,
      'step' => '1',
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();
    $this->assertSession()->fieldExists($this->fieldName . '[0][stock_level]');
    $this->assertSession()
      ->fieldNotExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldDisabled($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals($this->fieldName . '[0][stock_transaction_note]', $default_note);

    $stock_level = 15;
    $new_price_amount = '1.11';
    $variations_edit = [
      'price[0][number]' => $new_price_amount,
      $this->fieldName . '[0][stock_level]' => $stock_level,
    ];
    $this->saveHtmlOutput();
    $this->submitForm($variations_edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);

    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    // We setup our variation with an initial stock of 10. So setting the
    // absolute level to 15 should result in a transaction with 5.
    $this->assertEquals(5, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertTrue($default_note, $data['message']);

    // If the absolute stock level is the same as before, it shouldn't trigger
    // any transaction.
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $stock_level = 15;
    $variations_edit = [
      $this->fieldName . '[0][stock_level]' => $stock_level,
    ];
    $this->submitForm($variations_edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $transaction2 = $this->getLastEntityTransaction($this->variation->id());
    $this->assertEquals($transaction->id, $transaction2->id);

    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    // Empty absolute stock_level shoudn't trigger any transaction.
    $stock_level = '';
    $edit = [
      $this->fieldName . '[0][stock_level]' => $stock_level,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $transaction3 = $this->getLastEntityTransaction($this->variation->id());
    $this->assertEquals($transaction->id, $transaction3->id);

    $widget_settings = [
      'custom_transaction_note' => TRUE,
      'default_transaction_note' => $default_note,
      'step' => '1',
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists($this->fieldName . '[0][stock_level]');
    $this->assertSession()
      ->fieldNotExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    self::setExpectedException(ExpectationException::class);
    $this->assertSession()
      ->fieldDisabled($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals($this->fieldName . '[0][stock_transaction_note]', $default_note);

    $stock_level = 5;
    $edit = [
      $this->fieldName . '[0][stock_level]' => $stock_level,
      $this->fieldName . '[0][stock_transaction_note]' => 'CustomNote',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();

    $transaction = $this->getLastEntityTransaction($this->variation->id());

    $data = unserialize($transaction->data);
    $this->assertEquals(-10, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertTrue('CustumNote', $data['message']);

    // Testing that zero value, results in a transaction.
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $stock_level = 0;
    $edit = [
      $this->fieldName . '[0][stock_level]' => $stock_level,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $this->assertEquals(-5, $transaction->qty);
  }

  /**
   * Testing the commerce_stock_level_absolute widget.
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

  /**
   * Test the deprecated simple widget.
   */
  public function testDeprecatedWidget() {
    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $widget_id = "commerce_stock_level_simple";
    $widget_settings = [
      'entry_system' => 'basic',
      'transaction_note' => FALSE,
      'context_fallback' => FALSE,
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);

    // Ensure the stock part of the form is healty.
    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()
      ->checkboxNotChecked('commerce_stock_always_in_stock[value]');
    $this->assertSession()->fieldExists($this->fieldName . '[0][adjustment]');

    $adjustment = 5;
    $new_price_amount = '1.11';
    $variations_edit = [
      'price[0][number]' => $new_price_amount,
      $this->fieldName . '[0][adjustment]' => $adjustment,
    ];
    $this->submitForm($variations_edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();

    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $this->assertEquals($adjustment, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);

    $widget_settings = [
      'entry_system' => 'simple',
      'transaction_note' => FALSE,
      'context_fallback' => FALSE,
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists($this->fieldName . '[0][stock_level]');

    $stock_level = 20;
    $edit = [
      $this->fieldName . '[0][stock_level]' => $stock_level,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);

    $transaction = $this->getLastEntityTransaction($this->variation->id());
    // We setup our variation with an initial stock of 15. So setting the
    // absolute level to 20 should result in a transaction with 5.
    $this->assertEquals(5, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);

    $widget_settings = [
      'entry_system' => 'transactions',
      'transaction_note' => FALSE,
      'context_fallback' => FALSE,
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();
    $this->assertSession()->linkExists('New transaction');
  }

}
