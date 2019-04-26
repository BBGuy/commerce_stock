<?php

namespace Drupal\Tests\commerce_stock_field\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides methods to attach and configure a stock level field.
 */
trait StockLevelFieldCreationTrait {

  /**
   * The name of the test field.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Creates a new stock level field.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   * @param string $widget_id
   *   The id of the widget which should be used.
   * @param array $storage_settings
   *   A list of field storage settings that will be added to the defaults.
   * @param array $field_settings
   *   A list of instance settings that will be added to the instance defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   * @param string $formatter_id
   *   The id of the formatter.
   * @param array $formatter_settings
   *   A list of formatter settings that will be added to the formatter
   *   defaults.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\field\Entity\FieldStorageConfig
   *   The field configuration.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createStockLevelField(
    $entity_type,
    $bundle,
    $widget_id,
    array $storage_settings = [],
    array $field_settings = [],
    array $widget_settings = [],
    $formatter_id = 'commerce_stock_level_simple',
    array $formatter_settings = []
  ) {
    $field_name = $this->getFieldname();
    $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => 'commerce_stock_level',
        'settings' => $storage_settings,
      ]);
    }
    $field_storage->save();
    $this->attachStockLevelField($field_storage, $bundle, $field_settings);
    $this->configureFormDisplay($widget_id, $widget_settings, $entity_type, $bundle);
    $this->configureViewDisplay($formatter_id, $formatter_settings, $entity_type, $bundle);
    return $field_storage;
  }

  /**
   * Attaches a stock level field to an entity.
   *
   * @param Drupal\field\Entity\FieldStorageConfig $field_storage
   *   The field storage.
   * @param string $bundle
   *   The bundle this field will be added to.
   * @param array $field_settings
   *   A list of field settings that will be added to the defaults.
   */
  protected function attachStockLevelField(
    FieldStorageConfig $field_storage,
    $bundle,
    array $field_settings
  ) {
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $bundle,
      'label' => $this->getFieldname(),
      'required' => FALSE,
      'settings' => $field_settings,
    ])->save();
  }

  /**
   * Set, update and configure the widget for the stock level field.
   *
   * @param string $widget_id
   *   The id of the widget.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   */
  protected function configureFormDisplay(
    $widget_id,
    array $widget_settings,
    $entity_type,
    $bundle
  ) {

    $entityTypeManager = \Drupal::entityTypeManager();

    $form_display = $entityTypeManager
      ->getStorage('entity_form_display')
      ->load($entity_type . '.' . $bundle . '.default');

    $widget = $form_display->getComponent($this->getFieldname());
    $widget['type'] = $widget_id;
    $widget['settings'] = $widget_settings;
    $form_display->setComponent($this->getFieldname(), $widget)
      ->save();
    $entityTypeManager->getStorage('entity_form_display')
      ->resetCache([$form_display->id()]);
    $entityTypeManager->clearCachedDefinitions();
  }

  /**
   * Set, update and configure the widget for the stock level field.
   *
   * @param string $formatter_id
   *   The id of the formatter.
   * @param array $formatter_settings
   *   A list of formatter settings that will be added to the widget defaults.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   */
  protected function configureViewDisplay(
    $formatter_id,
    array $formatter_settings,
    $entity_type,
    $bundle
  ) {

    $entityTypeManager = \Drupal::entityTypeManager();
    $entityTypeManager->clearCachedDefinitions();

    $view_display = $entityTypeManager->getStorage('entity_view_display');
    $product_variation_display = $view_display->load($entity_type . '.' . $bundle . '.default');
    if (!$product_variation_display) {
      $product_variation_display = $view_display->create([
        'targetEntityType' => $entity_type,
        'bundle' => $bundle,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }
    $product_variation_display->setComponent($this->fieldName, ['type' => 'commerce_stock_level_simple']);
    $product_variation_display->save();

    $display = $entityTypeManager->getStorage('entity_view_display');
    $view_display = $display->load($entity_type . '.' . $bundle . '.default');

    if (!$view_display) {
      $view_display = $display->create([
        'targetEntityType' => $entity_type,
        'bundle' => $bundle,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $formatter = $view_display->getComponent($this->getFieldname());
    $formatter['type'] = $formatter_id;
    $formatter['settings'] = $formatter_settings;
    $view_display->setComponent($this->getFieldname(), $formatter)
      ->save();
    $entityTypeManager->getStorage('entity_form_display')
      ->resetCache([$view_display->id()]);
  }

  /**
   * Return the field name.
   *
   * @return string
   *   The name of the field.
   */
  protected function getFieldname() {
    if (!empty($this->fieldName)) {
      return $this->fieldName;
    }
    return mb_strtolower($this->randomMachineName());
  }

}
