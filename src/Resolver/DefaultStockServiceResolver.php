<?php

namespace Drupal\commerce_stock\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Returns the resolved stock service, if known.
 */
class DefaultStockServiceResolver implements StockServiceResolverInterface {

  /**
   * The stock service storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a new DefaultStockServiceResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->storage = $entity_type_manager->getStorage('commerce_stock_service');
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, Context $context, $quantity = NULL) {
    $active_services = $this->storage->loadByProperties(['active' => TRUE]);

    // @todo Consider more than the 'active' property... consider weight, config.

    return reset($active_services);
  }

}
