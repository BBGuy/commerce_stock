<?php

namespace Drupal\Tests\commerce_product_local\Kernel\Entity;

use Drupal\commerce_stock_local\Entity\StockLocation;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;

/**
 * Test the StockLocation entity.
 *
 * @coversDefaultClass \Drupal\commerce_stock_local\Entity\StockLocation
 *
 * @group commerce_stock
 */
class StockLocationTest extends CommerceStockKernelTestBase {

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_stock_location_type');
    $this->installEntitySchema('commerce_stock_location');
    $this->installConfig(['commerce_stock']);
    $this->installConfig(['commerce_stock_local']);

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
  }

  /**
   * @covers ::getName
   * @covers ::setName
   * @covers ::isActive
   * @covers ::setActive
   */
  public function testStockLocation() {

    $location = StockLocation::create([
      'type' => 'default',
    ]);
    $location->setName('TestName');
    self::assertEquals('TestName', $location->getName());

    self::assertTrue($location->isActive());
    $location->setActive(FALSE);
    self::assertFalse($location->isActive());
    $location->setActive(TRUE);
    self::assertTrue($location->isActive());
  }

  /**
   * Special test for guy_schneerson ;-)
   */
  public function testPerformance() {

    // Create 200 locations. Set each second on inactive.
    for ($i = 0; $i < 200; $i++) {
      $location = StockLocation::create([
        'type' => 'default',
        'name' => 'TestName_' . $i,
      ]);

      if ($i % 2 == 0) {
        $location->setActive(FALSE);
      }
      $location->save();
    }

    for ($i = 0; $i < 5; $i++) {

      $loadstart = microtime();
      $query = $this->entityManager->getStorage('commerce_stock_location')->getQuery();
      $activeLocations = $query->condition('status', 1)
        ->execute();
      $location_info = [];
      $locations = StockLocation::loadMultiple($activeLocations);
      $loadend = microtime();

      $start = microtime();
      foreach ($locations as $location) {
        $location_info[$location->id()] = [
          'name'   => $location->getName(),
          'status' => $location->isActive(),
        ];
      }
      $end = microtime();

      echo printf("\nLocation List has %s Elements", count($location_info));
      echo printf("\nLocation List Building code takes: %s", $end - $start);
      echo printf("\nLocation loading takes: %s", $loadend - $loadstart);
    }
  }

}
