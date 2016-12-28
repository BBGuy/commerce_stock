<?php

namespace Drupal\commerce_stock_local;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of stock location entities.
 *
 * @ingroup commerce_stock_local
 */
class StockLocationListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

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
    /* @var $entity \Drupal\commerce_stock_local\Entity\StockLocation */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.commerce_stock_location.edit_form', array(
          'commerce_stock_location' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
