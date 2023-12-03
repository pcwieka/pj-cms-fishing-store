<?php

namespace Drupal\lakes_gear\Controller\Store;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LakesGearStoreController extends ControllerBase {

  protected $formBuilder;

  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  public function lakesGear() {
    $form = $this->formBuilder->getForm('Drupal\lakes_gear\Form\Store\LakeSelectForm');
    // Tutaj będziesz chciał załadować i wyświetlić powiązane produkty.

    return [
      'form' => $form,
      // 'products' => Tutaj wyświetl powiązane produkty.
    ];
  }
}

