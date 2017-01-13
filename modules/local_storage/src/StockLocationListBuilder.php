<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce_stock_local\Entity\StockLocationType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of stock location entities.
 *
 * @ingroup commerce_stock_local
 */
class StockLocationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Stock location ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    /** @var \Drupal\commerce_stock_local\Entity\StockLocationType $location_type */
    $location_type = StockLocationType::load($entity->bundle());

    $row['id'] = $entity->id();
    $row['type'] = $location_type->label();
    $row['status'] = $entity->isActive() ? $this->t('Active') : $this->t('Inactive');
    $row['name']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl()->toRenderArray();

    return $row + parent::buildRow($entity);
  }

}
