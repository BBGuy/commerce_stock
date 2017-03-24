<?php

namespace Drupal\commerce_stock\Form;

use Drupal\commerce\Form\CommercePluginEntityFormBase;
use Drupal\commerce_stock\StockServiceManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StockServiceConfigForm extends CommercePluginEntityFormBase {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * Constructs a new StockServiceConfigForm object.
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   */
  public function __construct(StockServiceManagerInterface $stock_service_manager) {
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_stock.service_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_stock\Entity\StockServiceConfigInterface $stock_service_config */
    $stock_service_config = $this->entity;
    $services = $this->stockServiceManager->listServiceIds();

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'commerce_checkout/admin';
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $stock_service_config->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $stock_service_config->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_stock\StockServiceManager::getService',
      ],
    ];
    $form['service'] = [
      '#type' => 'select',
      '#title' => $this->t('Service'),
      '#options' => $services,
      '#default_value' => $stock_service_config->getServiceId(),
      '#required' => TRUE,
      '#disabled' => !$stock_service_config->isNew(),
    ];
    if (!$stock_service_config->isNew()) {
      $form['configuration'] = [
        '#parents' => ['configuration'],
      ];
      $form['configuration'] = $stock_service_config->getService()->buildConfigurationForm($form['configuration'], $form_state);
    }

    return $this->protectPluginIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_stock\Entity\StockServiceConfigInterface $entity */
    // The parent method tries to initialize the plugin collection before
    // setting the plugin.
    $entity->setServiceId($form_state->getValue('service'));

    parent::copyFormValuesToEntity($entity, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\commerce_stock\Entity\StockServiceConfigInterface $stock_service_config */
    $stock_service_config = $this->entity;
    if (!$stock_service_config->isNew()) {
      $stock_service_config->getService()->validateConfigurationForm($form['configuration'], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\commerce_stock\Entity\StockServiceConfigInterface $stock_service_config */
    $stock_service_config = $this->entity;
    if (!$stock_service_config->isNew()) {
      $stock_service_config->getService()->submitConfigurationForm($form['configuration'], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    drupal_set_message($this->t('Saved the %label stock service configuration.', ['%label' => $this->entity->label()]));
    if ($status == SAVED_UPDATED) {
      $form_state->setRedirect('entity.commerce_stock_service_config.collection');
    }
    elseif ($status == SAVED_NEW) {
      // Send the user to the Edit form to see the service configuration form.
      $form_state->setRedirect('entity.commerce_stock_service_config.edit_form', [
        'commerce_stock_service_config' => $this->entity->id(),
      ]);
    }
  }

}
