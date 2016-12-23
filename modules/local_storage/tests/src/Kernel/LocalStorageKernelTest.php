<?php

namespace Drupal\Tests\commerce_stock_local\Kernel;

use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;

class LocalStorageKernelTest extends CommerceStockKernelTestBase {

  /**
   * The database connection used.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('commerce_stock_local', 'commerce_stock_location');
    $this->database = $this->container->get('database');
  }

  /**
   * Test that the stock location schema gets installed.
   */
  public function testLocationSchema() {
    $table = 'commerce_stock_location';
    $columns = array("id", "name", "status");
    $this->database->schema();
    $this->assertTrue($this->database->schema()->tableExists($table), 'Table exists');
    foreach ($columns as $column) {
      $this->assertTrue($this->database->schema()->fieldExists($table, $column), $column . ' column exists.');
    }
  }

}
