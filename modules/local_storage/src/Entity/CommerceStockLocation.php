<?php

namespace Drupal\commerce_stock_local\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

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
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_stock_local\CommerceStockLocationListBuilder",
 *     "views_data" = "Drupal\commerce_stock_local\Entity\CommerceStockLocationViewsData",
 *     "translation" = "Drupal\commerce_stock_local\CommerceStockLocationTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_stock_local\Form\CommerceStockLocationForm",
 *       "add" = "Drupal\commerce_stock_local\Form\CommerceStockLocationForm",
 *       "edit" = "Drupal\commerce_stock_local\Form\CommerceStockLocationForm",
 *       "delete" = "Drupal\commerce_stock_local\Form\CommerceStockLocationDeleteForm",
 *     },
 *     "route_provider" = {
 *        "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *        "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_stock_location",
 *   data_table = "commerce_stock_location_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer commerce_stock location entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/commerce_stock_location/{commerce_stock_location}",
 *     "add-page" = "/admin/commerce/commerce_stock_location/add",
 *     "add-form" = "/admin/commerce/commerce_stock_location/add/{commerce_stock_location_type}",
 *     "edit-form" = "/admin/commerce/commerce_stock_location/{commerce_stock_location}/edit",
 *     "delete-form" = "/admin/commerce/commerce_stock_location/{commerce_stock_location}/delete",
 *     "collection" = "/admin/commerce/commerce_stock_location",
 *   },
 *   bundle_entity_type = "commerce_stock_location_type",
 *   field_ui_base_route = "entity.commerce_stock_location_type.edit_form"
 * )
 */
class CommerceStockLocation extends ContentEntityBase implements CommerceStockLocationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'uid' => \Drupal::currentUser()->id(),
    );
  }

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
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the stock location entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the stock location entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the stock location is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
