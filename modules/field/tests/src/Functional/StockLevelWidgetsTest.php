<?php

namespace Drupal\Tests\commerce_stock_field\Functional;

use Behat\Mink\Exception\ExpectationException;
use Drupal\commerce\EntityHelper;
use Drupal\commerce_product\Entity\Product;
use Drupal\Tests\commerce_stock_field\Kernel\StockLevelFieldCreationTrait;
use Drupal\Tests\commerce_stock_local\Kernel\StockTransactionQueryTrait;

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
   */
  public function testSimpleTransactionStockLevelWidget() {

    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $widget_id = "commerce_stock_level_simple_transaction";
    $default_note = $this->randomString(200);
    $widget_settings = [
      'custom_transaction_note' => FALSE,
      'default_transaction_note' => $default_note,
      'step' => '1',
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);

    // Test adding a new variation on the variations tab.
    $this->drupalGet('admin/commerce/products');
    $this->getSession()->getPage()->clickLink('Add product');
    // Create a product.
    $store_ids = EntityHelper::extractIds($this->stores);
    $title = $this->randomMachineName();
    $edit = [
      'title[0][value]' => $title,
    ];
    foreach ($store_ids as $store_id) {
      $edit['stores[target_id][value][' . $store_id . ']'] = $store_id;
    }
    $this->submitForm($edit, 'Save and add variations');
    $this->assertNotEmpty($this->getSession()
      ->getPage()
      ->hasLink('Add variation'));
    $this->getSession()->getPage()->clickLink('Add variation');
    $this->assertSession()->pageTextContains(t('Add variation'));
    // Ensure the stock part of the form is healty.
    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()
      ->checkboxNotChecked('commerce_stock_always_in_stock[value]');
    $this->assertSession()->fieldExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldDisabled($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals($this->fieldName . '[0][stock_transaction_note]', $default_note);
    $this->assertSession()->buttonExists('Save');

    $variation_sku = $this->randomMachineName();
    $title = $this->randomString();
    $this->getSession()->getPage()->fillField('sku[0][value]', $variation_sku);
    $this->getSession()->getPage()->fillField('price[0][number]', '9.99');
    $this->getSession()->getPage()->fillField('title[0][value]', $title);
    $adjustment = 2;
    $this->getSession()
      ->getPage()
      ->fillField($this->fieldName . '[0][adjustment]', $adjustment);
    $this->submitForm([], t('Save'));
    $this->assertSession()->statusCodeEquals(200);

    $variation_in_table = $this->getSession()->getPage()->find('xpath', '//table/tbody/tr/td[text()="' . $variation_sku . '"]');
    $this->assertNotEmpty($variation_in_table);
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_product_variation');
    $result = $storage->loadByProperties(['sku' => $variation_sku]);
    $variation = array_shift($result);
    $transaction = $this->getLastEntityTransaction($variation->id());
    $this->assertEquals($adjustment, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);

    // Test the widget on variation edit form.
    $this->drupalGet($this->variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()
      ->checkboxNotChecked('commerce_stock_always_in_stock[value]');
    // Check the defaults.
    $this->assertSession()->fieldExists($this->fieldName . '[0][adjustment]');
    $this->assertSession()
      ->fieldExists($this->fieldName . '[0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals($this->fieldName . '[0][stock_transaction_note]', $default_note);
    $adjustment = 6;
    $new_price_amount = '1.11';
    $variations_edit = [
      'price[0][number]' => $new_price_amount,
      $this->fieldName . '[0][adjustment]' => $adjustment,
    ];
    $this->submitForm($variations_edit, 'Save');

    $this->assertSession()->statusCodeEquals(200);
    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    $this->assertEquals($adjustment, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertEquals($default_note, $data['message']);

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
    self::expectException(ExpectationException::class);
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
    $this->assertEquals('CustomNote', $data['message']);

  }

  /**
   * Tests the default simple transaction widget in single variation mode.
   */
  public function testSimpleTransactionStockLevelWidgetSingleVariationMode() {

    $entity_type = "commerce_product_variation";
    $bundle = 'default';
    $widget_id = "commerce_stock_level_simple_transaction";
    $default_note = $this->randomString(200);
    $widget_settings = [
      'custom_transaction_note' => FALSE,
      'default_transaction_note' => $default_note,
      'step' => '1',
    ];
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);

    $this->drupalGet('admin/commerce/config/product-types/default/edit');
    $edit = [
      'multipleVariations' => FALSE,
    ];
    $this->submitForm($edit, t('Save'));
    $this->drupalGet('admin/commerce/products');
    $this->getSession()->getPage()->clickLink('Add product');
    $this->assertSession()->buttonNotExists('Save and add variations');
    $this->assertSession()->fieldExists('variations[entity][sku][0][value]');

    // On product add form, there should be no stock level input field.
    $this->assertSession()->fieldNotExists('variations[entity][' . $this->fieldName . '][0][adjustment]');
    $this->assertSession()
      ->fieldNotExists('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]');

    $store_id = $this->stores[0]->id();
    $title = $this->randomMachineName();
    $sku = strtolower($this->randomMachineName());
    $edit = [
      'title[0][value]' => $title,
      'stores[target_id][value][' . $store_id . ']' => $store_id,
      'variations[entity][sku][0][value]' => $sku,
      'variations[entity][price][0][number]' => '99.99',
      'variations[entity][title][0][value]' => $title . '_testvariation',
    ];

    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();
    // Check if product was created. Remember we created
    // product 1 in test setup.
    $product = Product::load(2);
    $this->assertNotEmpty($product);
    $this->assertEquals($title, $product->getTitle());

    // On product edit form, we should have a stock widget.
    $this->drupalGet($product->toUrl('edit-form'));
    $this->assertSession()
      ->fieldExists('variations[entity][commerce_stock_always_in_stock][value]');
    $this->assertSession()
      ->checkboxNotChecked('variations[entity][commerce_stock_always_in_stock][value]');
    $this->assertSession()->fieldExists('variations[entity][' . $this->fieldName . '][0][adjustment]');
    $this->assertSession()
      ->fieldExists('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]');
    $this->assertSession()
      ->fieldDisabled('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]', $default_note);
    $adjustment = 6;
    $edit = [
      'title[0][value]' => 'New title',
      'variations[entity][price][0][number]' => '199.99',
      'variations[entity][' . $this->fieldName . '][0][adjustment]' => $adjustment,
      'variations[entity][title][0][value]' => $title . '_testvariation',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->saveHtmlOutput();

    \Drupal::entityTypeManager()->getStorage('commerce_product')->resetCache([2]);
    \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->resetCache([1]);
    $product = Product::load(2);
    $this->assertNotEmpty($product);

    $variation = $product->getDefaultVariation();
    $this->assertNotEmpty($variation);
    $transaction = $this->getLastEntityTransaction($variation->id());
    $this->assertEquals($adjustment, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
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
    $this->submitForm($variations_edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);

    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    // We setup our variation with an initial stock of 10. So setting the
    // absolute level to 15 should result in a transaction with 5.
    $this->assertEquals(5, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertEquals($default_note, $data['message']);

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
    self::expectException(ExpectationException::class);
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
    $transaction = $this->getLastEntityTransaction($this->variation->id());
    $data = unserialize($transaction->data);
    $this->assertEquals(-10, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);
    $this->assertEquals('CustumNote', $data['message']);

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
   * Tests the absolute stock level widget in single variation mode.
   */
  public function testAbsoluteTransactionStockLevelWidgetSingleVariationMode() {

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

    $this->drupalGet('admin/commerce/config/product-types/default/edit');
    $edit = [
      'multipleVariations' => FALSE,
    ];
    $this->submitForm($edit, t('Save'));

    $this->drupalGet('admin/commerce/products');
    $this->getSession()->getPage()->clickLink('Add product');
    $this->assertSession()->buttonNotExists('Save and add variations');
    $this->assertSession()->fieldExists('variations[entity][sku][0][value]');

    // On product add form, there should be no stock level input field.
    $this->assertSession()->fieldNotExists('variations[entity][' . $this->fieldName . '][0][stock_level]');
    $this->assertSession()
      ->fieldNotExists('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]');

    $store_id = $this->stores[0]->id();
    $title = $this->randomMachineName();
    $sku = strtolower($this->randomMachineName());
    $edit = [
      'title[0][value]' => $title,
      'stores[target_id][value][' . $store_id . ']' => $store_id,
      'variations[entity][sku][0][value]' => $sku,
      'variations[entity][price][0][number]' => '99.99',
      'variations[entity][title][0][value]' => $title . '_testvariation',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    // Check if product was created. Remember we created
    // product 1 in test setup.
    $product = Product::load(2);
    $this->assertNotEmpty($product);
    $this->assertEquals($title, $product->getTitle());

    // On product edit form, we should have a stock widget.
    $this->drupalGet($product->toUrl('edit-form'));
    $this->assertSession()
      ->fieldExists('variations[entity][commerce_stock_always_in_stock][value]');
    $this->assertSession()
      ->checkboxNotChecked('variations[entity][commerce_stock_always_in_stock][value]');
    $this->assertSession()->fieldExists('variations[entity][' . $this->fieldName . '][0][stock_level]');
    $this->assertSession()
      ->fieldExists('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]');
    $this->assertSession()
      ->fieldDisabled('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]');
    $this->assertSession()
      ->fieldValueEquals('variations[entity][' . $this->fieldName . '][0][stock_transaction_note]', $default_note);
    $stock_level = 15;
    $edit = [
      'title[0][value]' => 'New title',
      'variations[entity][price][0][number]' => '199.99',
      'variations[entity][' . $this->fieldName . '][0][stock_level]' => $stock_level,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);

    \Drupal::entityTypeManager()->getStorage('commerce_product')->resetCache([2]);
    \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->resetCache([1]);
    $product = Product::load(2);
    $this->assertNotEmpty($product);

    $variation = $product->getDefaultVariation();
    $this->assertNotEmpty($variation);
    $transaction = $this->getLastEntityTransaction($variation->id());
    $this->assertEquals(15, $transaction->qty);
    $this->assertEquals($this->adminUser->id(), $transaction->related_uid);

    $this->drupalGet($product->toUrl('edit-form'));
    $stock_level = 5;
    $edit = [
      'variations[entity][' . $this->fieldName . '][0][stock_level]' => $stock_level,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $transaction = $this->getLastEntityTransaction($variation->id());
    $this->assertEquals(-10, $transaction->qty);
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

    $this->assertSession()->linkExists(t('transaction form'));
    $this->clickLink(t('transaction form'));
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
    $this->assertSession()->fieldExists($this->fieldName . '[0][stock_level]');

    $stock_level = 20;
    $edit = [
      $this->fieldName . '[0][stock_level]' => $stock_level,
    ];
    $this->submitForm($edit, 'Save');

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
    $this->assertSession()->linkExists('New transaction');
  }

}
