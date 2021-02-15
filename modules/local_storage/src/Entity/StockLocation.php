<?php

namespace Drupal\commerce_stock_local\Entity;

use Drupal\commerce_stock\StockLocationInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\commerce\EntityOwnerTrait;

/**
 * Defines the stock location entity.
 *
 * @ingroup commerce_stock_local
 *
 * @ContentEntityType(
 *   id = "commerce_stock_location",
 *   label = @Translation("Stock location"),
 *   label_plural = @Translation("Stock locations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count stock location",
 *     plural = "@count stock locations",
 *   ),
 *   bundle_label = @Translation("Stock location type"),
 *   handlers = {
 *     "event" = "Drupal\commerce_stock_local\Event\StockLocationEvent",
 *     "storage" = "Drupal\commerce_stock_local\StockLocationStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_stock_local\StockLocationListBuilder",
 *     "views_data" = "Drupal\commerce_stock_local\StockLocationViewsData",
 *     "translation" = "Drupal\commerce_stock_local\StockLocationTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_stock_local\Form\StockLocationForm",
 *       "add" = "Drupal\commerce_stock_local\Form\StockLocationForm",
 *       "edit" = "Drupal\commerce_stock_local\Form\StockLocationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *        "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *        "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer commerce_stock_location",
 *   permission_granularity = "bundle",
 *   translatable = TRUE,
 *   base_table = "commerce_stock_location",
 *   data_table = "commerce_stock_location_field_data",
 *   entity_keys = {
 *     "id" = "location_id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/commerce_stock_location/{commerce_stock_location}",
 *     "add-page" = "/commerce_stock_location/add",
 *     "add-form" = "/commerce_stock_location/add/{commerce_stock_location_type}",
 *     "edit-form" = "/commerce_stock_location/{commerce_stock_location}/edit",
 *     "delete-form" = "/admin/commerce/commerce_stock_location/{commerce_stock_location}/delete",
 *     "collection" = "/admin/commerce/commerce_stock_location",
 *   },
 *   bundle_entity_type = "commerce_stock_location_type",
 *   field_ui_base_route = "entity.commerce_stock_location_type.edit_form"
 * )
 */
class StockLocation extends ContentEntityBase implements LocalStockLocationInterface, StockLocationInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', $active ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // Explicitly set the owner ID to 0 if the translation owner is anonymous
      // (This will ensure we don't store a broken reference in case the user
      // no longer exists).
      if ($translation->getOwner()->isAnonymous()) {
        $translation->setOwnerId(0);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the stock location entity.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the stock location is active.'))
      ->setDefaultValue(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
