<?php

namespace Drupal\Tests\commerce_stock\Functional;

use Drupal\commerce\EntityHelper;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Test that the product creation form contains the stock settings fields.
 *
 * @group commerce_stock
 */
class ProductAdminTest extends StockBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'commerce_stock_local',
  ];

  /**
   * Test the create form.
   */
  public function testCreateProductVariationForm() {

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

    // Create a variation.
    $this->getSession()->getPage()->clickLink('Add variation');
    $this->assertSession()->pageTextContains(t('Add variation'));
    $this->assertSession()->fieldExists('sku[0][value]');
    $this->assertSession()->fieldExists('price[0][number]');
    $this->assertSession()->fieldExists('status[value]');
    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()->buttonExists('Save');

    $variation_sku = $this->randomMachineName();
    $this->getSession()->getPage()->fillField('sku[0][value]', $variation_sku);
    $this->getSession()->getPage()->fillField('price[0][number]', '9.99');
    $this->getSession()->getPage()->fillField('title[0][value]', $this->randomString());
    $this->submitForm([], t('Save'));
    $this->assertSession()->statusCodeEquals(200);

    $variation = ProductVariation::load(4);
    $this->assertEquals($variation_sku, $variation->getSku());
    $this->assertEquals('0', $variation->get('commerce_stock_always_in_stock')
      ->getValue()[0]['value']);
  }

  /**
   * Tests editing a product variation.
   */
  public function testEditProduct() {
    $product = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => $this->randomMachineName(),
      'stores' => [$this->store],
    ]);
    $original_sku = strtolower($this->randomMachineName());
    $variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'product_id' => $product->id(),
      'sku' => $original_sku,
      'title' => $this->randomString(),
    ]);

    $this->drupalGet($variation->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('sku[0][value]');
    $this->assertSession()->buttonExists('Save');

    $this->assertSession()
      ->fieldExists('commerce_stock_always_in_stock[value]');
    $this->assertSession()
      ->checkboxNotChecked('commerce_stock_always_in_stock[value]');

    $new_sku = strtolower($this->randomMachineName());
    $new_price_amount = '1.11';
    $variations_edit = [
      'sku[0][value]' => $new_sku,
      'price[0][number]' => $new_price_amount,
      'status[value]' => 1,
    ];
    $checkbox = $this->getSession()
      ->getPage()
      ->findField('commerce_stock_always_in_stock[value]');
    if ($checkbox) {
      $checkbox->check();
    }
    $this->submitForm($variations_edit, 'Save');

    \Drupal::service('entity_type.manager')
      ->getStorage('commerce_product_variation')
      ->resetCache([$variation->id()]);
    $variation = ProductVariation::load($variation->id());
    $this->assertEquals($variation->getSku(), $new_sku, 'SKU successfully changed.');
    $this->assertEquals($variation->getPrice()->getNumber(), $new_price_amount);
    $this->assertEquals('1', $variation->get('commerce_stock_always_in_stock')
      ->getValue()[0]['value']);
  }

}
