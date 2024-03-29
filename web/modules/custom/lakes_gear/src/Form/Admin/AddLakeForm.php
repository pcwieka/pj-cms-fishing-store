<?php

namespace Drupal\lakes_gear\Form\Admin;

use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;

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
      '#default_value' => $query['image_fid'] ?? '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
      '#required' => TRUE,
    ];

    // Pobieranie listy produktów
    $product_ids = Product::loadMultiple();
    $product_options = [];
    foreach ($product_ids as $product_id => $product) {
      $product_options[$product_id] = $product->label();
    }

    $form['product_search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Szukaj produktu'),
      '#attributes' => ['class' => ['product-search'], 'autocomplete' => 'off'],
    ];

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

      $form['associated_products'][$product_id]['checkbox'] = [
        '#type' => 'checkbox',
        '#title' => $product_label,
        '#return_value' => $product_id,
      ];

      $form['associated_products'][$product_id]['priority'] = [
        '#type' => 'number',
        '#title' => $this->t('Priorytet'),
        '#title_display' => 'invisible',
        '#default_value' => 0, // Ustawienie domyślnej wartości na 0
        '#states' => [
          'visible' => [
            ':input[name="associated_products[' . $product_id . '][checkbox]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    // Przycisk submit
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Dodaj jezioro'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $lake_name = $form_state->getValue('lake_name');
    $description = $form_state->getValue('description')['value'];
    $google_maps_url = $form_state->getValue('google_maps_url');
    $image = $form_state->getValue('image');

    if (!empty($image)) {
      $file = File::load(reset($image));
      $file->setPermanent();
      $file->save();

      // Uzyskaj ID pliku
      $file_id = $file->id();
    }

    // Dodanie informacji o jeziorze do bazy danych
    $connection = Database::getConnection();
    $lake_id = $connection->insert('lakes_gear_lakes')
      ->fields(
        [
          'lake_name' => $lake_name,
          'description' => $description,
          'google_maps_url' => $google_maps_url,
          'image_fid' => $file_id ?? NULL,
        ],
      )
      ->execute();

    $associated_products = array_filter($form_state->getValue('associated_products'));
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

    \Drupal::messenger()->addMessage($this->t('Jezioro @name zotało dodane', ['@name' => $lake_name]));

    $form_state->setRedirect('lakes_gear.admin.lakes');
  }
}
