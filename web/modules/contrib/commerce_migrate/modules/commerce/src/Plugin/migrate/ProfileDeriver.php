<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationDeriverTrait;
use Drupal\migrate_drupal\FieldDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver for Commerce 1 profiles.
 */
class ProfileDeriver extends DeriverBase implements ContainerDeriverInterface {

  use MigrationDeriverTrait;
  use StringTranslationTrait;

  /**
   * The base plugin ID this derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * Whether or not to include translations.
   *
   * @var bool
   */
  protected $includeTranslations;

  /**
   * The migration field discovery service.
   *
   * @var \Drupal\migrate_drupal\FieldDiscoveryInterface
   */
  protected $fieldDiscovery;

  /**
   * Commerce profile deriver constructor.
   *
   * @param string $base_plugin_id
   *   The base plugin ID for the plugin ID.
   * @param bool $translations
   *   Whether or not to include translations.
   * @param \Drupal\migrate_drupal\FieldDiscoveryInterface $field_discovery
   *   The migration field discovery service.
   */
  public function __construct($base_plugin_id, $translations, FieldDiscoveryInterface $field_discovery) {
    $this->basePluginId = $base_plugin_id;
    $this->includeTranslations = $translations;
    $this->fieldDiscovery = $field_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    // Translations don't make sense unless we have content_translation.
    return new static(
      $base_plugin_id,
      $container->get('module_handler')->moduleExists('content_translation'),
      $container->get('migrate_drupal.field_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $profile_types = static::getSourcePlugin('commerce1_profile_type');
    try {
      $profile_types->checkRequirements();
    }
    catch (RequirementsException $e) {
      // If the commerce1_profile_type requirements failed, that means we do
      // not have a Drupal source database configured - there is nothing to
      // generate.
      return $this->derivatives;
    }

    try {
      foreach ($profile_types as $row) {
        $profile_type = $row->getSourceProperty('type');
        $values = $base_plugin_definition;

        $values['label'] = $this->t('@label (@type)', [
          '@label' => $values['label'],
          '@type' => $row->getSourceProperty('type'),
        ]);
        $values['source']['profile_type'] = $profile_type;
        $values['destination']['default_bundle'] = $profile_type;

        // If this migration is based on the commerce1_profile_revision
        // migration it should explicitly depend on the corresponding
        // commerce1_profile variant.
        if ($base_plugin_definition['id'] == ['commerce1_profile_revision']) {
          $values['migration_dependencies']['required'][] = 'commerce1_profile:' . $profile_type;
        }
        /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
        $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($values);
        $this->fieldDiscovery->addBundleFieldProcesses($migration, 'commerce_customer_profile', $profile_type);
        $this->derivatives[$profile_type] = $migration->getPluginDefinition();
      }
    }
    catch (DatabaseExceptionWrapper $e) {
      // Once we begin iterating the source plugin it is possible that the
      // source tables will not exist. This can happen when the
      // MigrationPluginManager gathers up the migration definitions but we do
      // not actually have a Drupal 7 source database.
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
