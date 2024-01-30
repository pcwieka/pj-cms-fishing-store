<?php

namespace Drupal\lakes_gear\Form\Admin;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\commerce_product\Entity\Product;
use Drupal\facets\Exception\Exception;
use Drupal\file\Entity\File;

class EditLakeForm extends FormBase {

  public function getFormId() {
    return 'lakes_gear_edit_lake_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $lake_id = NULL) {
    // Pobranie informacji o jeziorze z bazy danych
    $query = Database::getConnection()->select('lakes_gear_lakes', 'f')
      ->condition('lake_id', $lake_id)
      ->fields('f', ['lake_name', 'description', 'image_fid', 'google_maps_url'])
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

    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Opis jeziora'),
      '#default_value' => $query['description'] ?? '',
      '#format' => 'full_html',
      '#required' => TRUE,
    ];

    $form['google_maps_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Maps URL'),
      '#description' => $this->t('Enter the Google Maps iframe src URL for the lake.'),
      '#default_value' => $query['google_maps_url'] ?? '',
      '#maxlength' => 3072,
      '#required' => TRUE,
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Grafika jeziora'),
      '#upload_location' => 'public://lake-images/',
      '#default_value' => $query['image_fid'] ? [$query['image_fid']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
      '#required' => TRUE,
    ];

    $form['product_search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Szukaj produktu'),
      '#attributes' => ['class' => ['product-search'], 'autocomplete' => 'off'],
    ];

    $existing_priorities = Database::getConnection()->select('lakes_gear_lakes_products', 'flp')
      ->fields('flp', ['product_id', 'priority'])
      ->condition('lake_id', $lake_id)
      ->execute()->fetchAllKeyed();

    $form['associated_products'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Powiązane produkty i priorytety'),
      '#prefix' => '<div id="associated-products-wrapper">',
      '#suffix' => '</div>',
    ];

    foreach ($product_options as $product_id => $product_label) {
      $form['associated_products'][$product_id] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['product-priority-container']],
      ];

      $is_checked = in_array($product_id, $existing_products);

      $form['associated_products'][$product_id]['checkbox'] = [
        '#type' => 'checkbox',
        '#title' => $product_label,
        '#default_value' => $is_checked,
        '#return_value' => $product_id,
      ];

      $priority = $existing_priorities[$product_id] ?? 0;

      $form['associated_products'][$product_id]['priority'] = [
        '#type' => 'number',
        '#title' => $this->t('Priorytet'),
        '#title_display' => 'invisible',
        '#default_value' => $priority,
        '#states' => [
          'visible' => [
            ':input[name="associated_products[' . $product_id . '][checkbox]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

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
    // Pobierz ID jeziora, nazwę, opis i ID pliku grafiki
    $lake_id = $form_state->getValue('lake_id');
    $lake_name = $form_state->getValue('lake_name');
    $description = $form_state->getValue('description')['value'];
    $google_maps_url = $form_state->getValue('google_maps_url');
    $associated_products = $form_state->getValue('associated_products');
    $image = reset($form_state->getValue('image')); // ID nowego pliku

    // Załaduj aktualne informacje o jeziorze
    $current_lake = Database::getConnection()->select('lakes_gear_lakes', 'f')
      ->fields('f')
      ->condition('lake_id', $lake_id)
      ->execute()
      ->fetchAssoc();

    // Jeśli plik grafiki został zmieniony, zaktualizuj go
    if ($image && $image != $current_lake['image_fid']) {
      $file = File::load($image);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $file_id = $file->id();
      }
    } else {
      $file_id = $current_lake['image_fid'];
    }

    // Aktualizacja informacji o jeziorze w bazie danych
    Database::getConnection()->update('lakes_gear_lakes')
      ->fields([
        'lake_name' => $lake_name,
        'description' => $description,
        'google_maps_url' => $google_maps_url,
        'image_fid' => $file_id ?? NULL,
      ])
      ->condition('lake_id', $lake_id)
      ->execute();

    // Aktualizacja powiązanych produktów

    // Najpierw usunięcie istniejących powiązań
    Database::getConnection()->delete('lakes_gear_lakes_products')
      ->condition('lake_id', $lake_id)
      ->execute();

    // Dodanie nowych powiązań z priorytetami
    foreach ($associated_products as $product_id => $product_data) {
      if (isset($product_data['checkbox']) && $product_data['checkbox']) {
        Database::getConnection()->insert('lakes_gear_lakes_products')
          ->fields([
            'lake_id' => $lake_id,
            'product_id' => $product_id,
            'priority' => $product_data['priority'] ?? 0,
          ])
          ->execute();
      }
    }

    \Drupal::messenger()->addMessage($this->t('Jezioro @name zostało zaktualizowane', ['@name' => $lake_name]));
  }
}
