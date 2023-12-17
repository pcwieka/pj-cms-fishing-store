<?php

namespace Drupal\lakes_gear\Form\Admin;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class DeleteLakeForm extends ConfirmFormBase {

  public function getFormId() {
    return 'delete_lake_form';
  }

  public function getQuestion() {
    return $this->t('Czy na pewno chcesz usunąć to jezioro?');
  }

  public function getCancelUrl() {
    return new Url('lakes_gear.admin.lakes');
  }

  public function getDescription() {
    return $this->t('Operacja nie może zostać cofnięta.');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Pobierz ID jeziora z trasy.
    $lake_id = $this->getRequest()->get('lake_id');

    if ($lake_id) {
      $connection = Database::getConnection();

      // Najpierw usuń powiązania produktów z tym jeziorem.
      $connection->delete('lakes_gear_lakes_products')
        ->condition('lake_id', $lake_id)
        ->execute();

      // Następnie usuń jezioro.
      $connection->delete('lakes_gear_lakes')
        ->condition('lake_id', $lake_id)
        ->execute();

      $this->messenger()->addMessage($this->t('Jezioro zostało usunięte.'));
    } else {
      $this->messenger()->addError($this->t('Nie znaleziono jeziora.'));
    }

    // Przekieruj użytkownika z powrotem do listy jezior.
    $form_state->setRedirect('lakes_gear.admin.lakes');
  }
}

