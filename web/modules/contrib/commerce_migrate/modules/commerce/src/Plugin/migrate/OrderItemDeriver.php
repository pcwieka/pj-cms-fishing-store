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
 * Deriver for Commerce 1 line items based on line item types.
 */
class OrderItemDeriver extends DeriverBase implements ContainerDeriverInterface {

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
   * D7NodeDeriver constructor.
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
    $order_item_types = static::getSourcePlugin('commerce1_order_item_type');
    try {
      $order_item_types->checkRequirements();
    }
    catch (RequirementsException $e) {
      // If the d7_order_item_type requirements failed, that means we do not
      // have a  Drupal source database configured - there is nothing to
      // generate.
      return parent::getDerivativeDefinitions($base_plugin_definition);
    }

    try {
      foreach ($order_item_types as $row) {
        $line_item_type = $row->getSourceProperty('type');
        // Ignore shipping line items because they become order adjustments.
        if ($line_item_type !== 'shipping' && $line_item_type !== 'commerce_discount') {
          $values = $base_plugin_definition;

          $values['label'] = $this->t('@label (@type)', [
            '@label' => $values['label'],
            '@type' => $row->getSourceProperty('type'),
          ]);
          $values['source']['line_item_type'] = $line_item_type;
          $values['destination']['default_bundle'] = $line_item_type;

          /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
          $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($values);
          $this->fieldDiscovery->addBundleFieldProcesses($migration, 'commerce_line_item', $line_item_type);
          $this->derivatives[$line_item_type] = $migration->getPluginDefinition();
        }
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
