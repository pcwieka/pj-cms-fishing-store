<?php

namespace Drupal\lakes_gear\Form\Admin;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\commerce_product\Entity\Product;

class EditLakeForm extends FormBase {

  public function getFormId() {
    return 'lakes_gear_edit_lake_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $lake_id = NULL) {
    // Pobranie informacji o jeziorze z bazy danych
    $query = Database::getConnection()->select('lakes_gear_lakes', 'f')
      ->condition('lake_id', $lake_id)
      ->fields('f', ['lake_name'])
      ->execute()->fetchAssoc();

    // Pobieranie listy produktów
    $product_ids = Product::loadMultiple();
    $product_options = [];
    foreach ($product_ids as $product_id => $product) {
      $product_options[$product_id] = $product->label();
    }

    // Pobieranie powiązanych produktów
    $existing_products = Database::getConnection()->select('lakes_gear_lakes_products', 'flp')
      ->fields('flp', ['product_id'])
      ->condition('lake_id', $lake_id)
      ->execute()->fetchCol();

    $form['lake_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nazwa jeziora'),
      '#default_value' => $query['lake_name'],
      '#required' => TRUE,
    ];

    $form['associated_products'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Powiązane produkty'),
      '#options' => $product_options,
      '#default_value' => $existing_products,
    ];


    $form['lake_id'] = [
      '#type' => 'hidden',
      '#value' => $lake_id,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Zapisz'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $lake_id = $form_state->getValue('lake_id');
    $lake_name = $form_state->getValue('lake_name');
    $selected_products = array_filter($form_state->getValue('associated_products'));

    // Aktualizacja informacji o jeziorze w bazie danych
    Database::getConnection()->update('lakes_gear_lakes')
      ->fields(['lake_name' => $lake_name])
      ->condition('lake_id', $lake_id)
      ->execute();

    // Aktualizacja powiązanych produktów
    // Najpierw usunięcie istniejących powiązań
    Database::getConnection()->delete('lakes_gear_lakes_products')
      ->condition('lake_id', $lake_id)
      ->execute();

    // Następnie dodanie nowych powiązań
    foreach ($selected_products as $product_id) {
      Database::getConnection()->insert('lakes_gear_lakes_products')
        ->fields([
          'lake_id' => $lake_id,
          'product_id' => $product_id,
        ])
        ->execute();
    }

    \Drupal::messenger()->addMessage($this->t('Jezioro @name zostało zaktualizowane', ['@name' => $lake_name]));
  }
}
