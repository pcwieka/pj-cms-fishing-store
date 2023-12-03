<?php

namespace Drupal\lakes_gear\Form\Admin;

use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class AddLakeForm extends FormBase {

  public function getFormId() {
    return 'lakes_gear_add_lake_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Istniejące elementy formularza
    $form['lake_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nazwa jeziora'),
      '#required' => TRUE,
    ];

    // Pobieranie listy produktów
    $product_ids = Product::loadMultiple();
    $product_options = [];
    foreach ($product_ids as $product_id => $product) {
      $product_options[$product_id] = $product->label();
    }

    // Pole wyboru produktów
    $form['associated_products'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Powiązane produkty'),
      '#options' => $product_options,
    ];

    // Przycisk submit
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Dodaj jezioro'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $lake_name = $form_state->getValue('lake_name');
    $selected_products = array_filter($form_state->getValue('associated_products'));

    // Dodanie informacji o jeziorze do bazy danych
    $connection = Database::getConnection();
    $lake_id = $connection->insert('lakes_gear_lakes')
      ->fields(['lake_name' => $lake_name])
      ->execute();

    // Zapis powiązań produktów z jeziorem
    foreach ($selected_products as $product_id) {
      $connection->insert('lakes_gear_lakes_products')
        ->fields([
          'lake_id' => $lake_id,
          'product_id' => $product_id,
        ])
        ->execute();
    }

    \Drupal::messenger()->addMessage($this->t('Jezioro @name zotało dodane', ['@name' => $lake_name]));

    $form_state->setRedirect('lakes_gear.admin.list');
  }
}
