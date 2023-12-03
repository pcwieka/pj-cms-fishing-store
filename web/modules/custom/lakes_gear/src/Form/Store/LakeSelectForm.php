<?php

namespace Drupal\lakes_gear\Form\Store;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class LakeSelectForm extends FormBase {

  public function getFormId() {
    return 'lakes_gear_lake_select_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $lakes = $this->getLakes();

    // Sprawdź, czy są dostępne jakiekolwiek jeziora
    if (empty($lakes)) {
      $form['no_lakes'] = [
        '#markup' => $this->t('Brak dostępnych jezior.'),
      ];
      return $form;
    }

    $default_lake_id = key($lakes);

    $form['lakes'] = [
      '#type' => 'select',
      '#title' => $this->t('Wybierz jezioro'),
      '#options' => $lakes,
      '#default_value' => $default_lake_id,
      '#ajax' => [
        'callback' => '::updateProducts',
        'wrapper' => 'products-wrapper',
      ],
      '#attributes' => [
        'class' => ['form-select', 'form-control']
      ],
    ];

    $form['products'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'products-wrapper', 'class' => ['lakes-gear-products-grid']],
      '#children' => $this->renderProducts($this->getProductsForLake($default_lake_id)),
      '#attached' => [
        'library' => [
          'lakes_gear/lakes_gear'
        ],
      ],
    ];

    return $form;
  }

  public function updateProducts(array &$form, FormStateInterface $form_state) {
    $lake_id = $form_state->getValue('lakes');
    $form['products']['#children'] = $this->renderProducts($this->getProductsForLake($lake_id));
    return $form['products'];
  }

  private function getLakes() {
    return Database::getConnection()->select('lakes_gear_lakes', 'f')
      ->fields('f', ['lake_id', 'lake_name'])
      ->execute()->fetchAllKeyed();
  }

  private function getProductsForLake($lake_id) {
    $product_ids = Database::getConnection()->select('lakes_gear_lakes_products', 'flp')
      ->fields('flp', ['product_id'])
      ->condition('lake_id', $lake_id)
      ->execute()
      ->fetchCol();

    return empty($product_ids) ? [] : \Drupal\commerce_product\Entity\Product::loadMultiple($product_ids);
  }

  private function renderProducts($products) {
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('commerce_product');
    return array_map(function($product) use ($view_builder) {
      $element = $view_builder->view($product, 'catalog');
      return [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => render($element),
        '#attributes' => ['class' => ['lakes-gear-product-item']],
      ];
    }, $products);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('lakes_gear.store.lakes_gear');
  }

}
