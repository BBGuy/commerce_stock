<?php

namespace Drupal\Tests\commerce_product_local\Kernel\Entity;

use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockServiceConfigInterface;
use Drupal\commerce_stock\StockUpdateInterface;
use Drupal\commerce_stock_local\LocalStockService;
use Drupal\Tests\commerce_stock\Kernel\CommerceStockKernelTestBase;
use Prophecy\Argument;

/**
 * Tests the LocalStockService.
 *
 * @coversDefaultClass \Drupal\commerce_stock_local\LocalStockService
 *
 * @group commerce_stock
 */
class LocalStockServiceTest extends CommerceStockKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('commerce_stock_local', ['commerce_stock_location']);
    $this->installConfig(['commerce_stock']);
    $this->installConfig(['commerce_stock_local']);
  }

  /**
   * @covers ::create
   * @covers ::getStockChecker
   * @covers ::getStockUpdater
   * @covers ::getConfiguration
   * @covers ::getName
   * @covers ::getId
   */
  public function testLocalStockService() {

    // Check if we get back, what we passed to the stock service.
    $prophecy = $this->prophesize(StockCheckInterface::class);
    $prophecy->getLocationList(Argument::any())->willReturn([1 => 'main']);
    $stockChecker = $prophecy->reveal();
    $stockUpdater = $this->prophesize(StockUpdateInterface::class)->reveal();
    $localStockService = new LocalStockService($stockChecker, $stockUpdater);
    self::assertEquals($stockChecker, $localStockService->getStockChecker());
    self::assertEquals($stockUpdater, $localStockService->getStockUpdater());
    self::assertEquals('local_stock', $localStockService->getId());
    self::assertEquals('Local stock', $localStockService->getName());

    // Test that instation through container works.
    $localStockService = LocalStockService::create($this->container);
    self::assertInstanceOf(LocalStockService::class, $localStockService);
    $stockChecker = $localStockService->getStockChecker();
    self::assertInstanceOf(StockCheckInterface::class, $stockChecker);
    $stockUpdater = $localStockService->getStockUpdater();
    self::assertInstanceOf(StockUpdateInterface::class, $stockUpdater);
    $stockConfig = $localStockService->getConfiguration();
    self::assertInstanceOf(StockServiceConfigInterface::class, $stockConfig);
  }

}
