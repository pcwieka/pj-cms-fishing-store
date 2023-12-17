<?php

namespace Drupal\lakes_gear\Controller\Admin;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;

class LakesGearAdminController extends ControllerBase {

  public function listLakes() {

    $header = [
      ['data' => $this->t('ID')],
      ['data' => $this->t('Nazwa jeziora')],
      ['data' => $this->t('Operacje')],
    ];

    $rows = [];
    $query = Database::getConnection()->select('lakes_gear_lakes', 'f')
      ->fields('f', ['lake_id', 'lake_name']);
    $result = $query->execute();

    foreach ($result as $row) {
      // Tworzenie linków do edycji i usuwania
      $edit_url = Url::fromRoute('lakes_gear.admin.lakes.edit', ['lake_id' => $row->lake_id]);
      $edit_link = Link::fromTextAndUrl($this->t('Edit'), $edit_url)->toString();

      // Link do usuwania
      $delete_url = Url::fromRoute('lakes_gear.admin.lakes.delete', ['lake_id' => $row->lake_id]);
      $delete_link = Link::fromTextAndUrl($this->t('Delete'), $delete_url)->toString();

      // Połączenie linków w jednej komórce
      $operations = $edit_link . ' ' . $delete_link;

      $rows[] = [
        'data' => [
          $row->lake_id,
          $row->lake_name,
          ['data' => ['#markup' => $operations]],
        ],
      ];
    }

    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No lakes found'),
    ];

    return $build;
  }
}

