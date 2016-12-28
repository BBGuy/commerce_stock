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

    $this->installSchema('commerce_stock_local', 'commerce_stock_location_custom');
    $this->database = $this->container->get('database');
  }

  /**
   * Test that the stock location schema gets installed.
   */
  public function testLocationSchema() {
    $table = 'commerce_stock_location_custom';
    $columns = array("id", "name", "status");
    $this->database->schema();
    $this->assertTrue($this->database->schema()->tableExists($table), 'Table exists');
    foreach ($columns as $column) {
      $this->assertTrue($this->database->schema()->fieldExists($table, $column), $column . ' column exists.');
    }
  }

  /**
   * Special test for guy_schneerson ;-)
   */
  public function testPerformance() {

    for ($i = 0; $i < 200; $i++) {
      $status = ($i % 2 == 0) ? 0 : 1;
      $this->database->insert('commerce_stock_location_custom')
        ->fields([
          'name'   => 'TestName_' . $i,
          'status' => $status,
        ])
        ->execute();
    }

    for ($i = 0; $i < 5; $i++) {
      $loadstart = microtime();
      $query = $this->database->select('commerce_stock_location_custom', 'loc')
        ->fields('loc');
      $query->condition('status', 1);
      $result = $query->execute()->fetchAll();
      $loadend = microtime();
      $location_info = [];
      $start = microtime();
      if ($result) {
        foreach ($result as $record) {
          $location_info[$record->id] = [
            'name'   => $record->name,
            'status' => $record->status,
          ];
        }
      }

      $end = microtime();
      echo printf("\nLocation List has %s Elements.", count($location_info));
      echo printf("\nLocation List Building code takes: %s", $end - $start);
      echo printf("\nLocation loading takes: %s", $loadend - $loadstart);
    }
  }

}
