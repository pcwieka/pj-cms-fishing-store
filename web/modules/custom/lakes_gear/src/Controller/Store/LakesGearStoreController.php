<?php

namespace Drupal\lakes_gear\Controller\Store;

use Drupal;
use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LakesGearStoreController extends ControllerBase {

  public function lakes() {
    $build = [];

    $query = Drupal::database()->select('lakes_gear_lakes', 'l')
      ->fields('l', ['lake_id', 'lake_name', 'image_fid']);

    $result = $query->execute();

    $lakes = [];
    foreach ($result as $record) {
      $file = File::load($record->image_fid);
      $lakes[] = [
        'lake_id' => $record->lake_id,
        'name' => $record->lake_name,
        'image_url' => $file ? file_create_url($file->getFileUri()) : '',
      ];
    }

    // Dodaj listę jezior
    $build['lakes_list'] = [
      '#theme' => 'lakes_gear_lakes_list',
      '#lakes' => $lakes,
    ];

    return $build;
  }

  public function lake($lake_id) {
    $build = [];

    // Pobieranie danych o konkretnym jeziorze
    $lake = Drupal::database()->select('lakes_gear_lakes', 'l')
      ->fields('l', ['lake_id', 'lake_name', 'image_fid', 'description', 'google_maps_url'])
      ->condition('lake_id', $lake_id)
      ->execute()
      ->fetchAssoc();

    if (!$lake) {
      throw new NotFoundHttpException();
    }

    $file = File::load($lake['image_fid']);
    $lake_image_url = $file ? file_create_url($file->getFileUri()) : '';

    // Dodaj dane jeziora do struktury strony
    $build['lake'] = [
      '#theme' => 'lakes_gear_lake_page',
      '#lake' => $lake,
      '#lake_image_url' => $lake_image_url
    ];

    // Pobieranie powiązanych produktów
    $product_ids = Drupal::database()->select('lakes_gear_lakes_products', 'lgp')
      ->fields('lgp', ['product_id'])
      ->condition('lake_id', $lake_id)
      ->execute()
      ->fetchCol();

    if (!empty($product_ids)) {
      $products = Product::loadMultiple($product_ids);

      // Dodaj produkty do struktury strony
      $build['lake']['#products'] = $this->renderProducts($products);
    }

    return $build;
  }

  private function renderProducts($products) {
    $view_builder = Drupal::entityTypeManager()->getViewBuilder('commerce_product');
    return array_map(function($product) use ($view_builder) {
      $element = $view_builder->view($product, 'catalog');
      return [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => render($element)
      ];
    }, $products);
  }

}

