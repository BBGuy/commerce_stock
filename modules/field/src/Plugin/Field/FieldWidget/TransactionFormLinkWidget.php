<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldWidget;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Plugin implementation of the 'commerce_stock_level_transaction_form_link' widget.
 *
 * @FieldWidget(
 *   id = "commerce_stock_level_transaction_form_link",
 *   module = "commerce_stock_field",
 *   label = @Translation("Link to stock transaction form"),
 *   description = @Translation("Provides a link to a transaction form, for more complex transactions."),
 *   field_types = {
 *     "commerce_stock_level"
 *   }
 * )
 */
class TransactionFormLinkWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Provides a link to stock transaction form.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {

    $field = $items->first();
    $entity = $items->getEntity();

    // @ToDo Use ::isApplicable instead.
    if (!($entity instanceof PurchasableEntityInterface)) {
      // No stock if this is not a purchasable entity.
      return [];
    }
    // @ToDo Consider how this may change
    // @see https://www.drupal.org/project/commerce_stock/issues/2949569
    if ($entity->isNew()) {
      // We can not work with entities before they are fully created.
      return [];
    }
    // Get the available stock level.
    $level = $field->available_stock;
    $link = Link::createFromRoute(
      $this->t('transaction form'),
      'commerce_stock_ui.stock_transactions2',
      ['commerce_product_v_id' => $entity->id()]
    )->toString();
    $element['stock_level'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Current stock level: @stock_level', ['@stock_level' => $level]),
    ];
    $element['stock_transactions_form_link'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Please use the @transaction to create any stock transactions.', ['@transaction' => $link]),
    ];
    return $element;
  }

}
