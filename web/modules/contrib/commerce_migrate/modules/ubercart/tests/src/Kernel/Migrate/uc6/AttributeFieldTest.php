<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests attribute field storage migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class AttributeFieldTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_price',
    'commerce_product',
    'commerce_store',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_store');
    $this->executeMigration('uc_attribute_field');
  }

  /**
   * Asserts various aspects of a field_storage_config entity.
   *
   * @param string $id
   *   The entity ID in the form ENTITY_TYPE.FIELD_NAME.
   * @param string $type
   *   The expected field type.
   * @param bool $translatable
   *   Whether or not the field is expected to be translatable.
   * @param int $cardinality
   *   The expected cardinality of the field.
   * @param array $dependencies
   *   The field's dependencies.
   */
  protected function assertEntity($id, $type, $translatable, $cardinality, array $dependencies) {
    [$entity_type] = explode('.', $id);

    /** @var \Drupal\field\FieldStorageConfigInterface $field */
    $field = FieldStorageConfig::load($id);
    $this->assertTrue($field instanceof FieldStorageConfigInterface);
    $this->assertSame($type, $field->getType());
    $this->assertEquals($translatable, $field->isTranslatable());
    $this->assertSame($entity_type, $field->getTargetEntityTypeId());
    $this->assertSame($dependencies, $field->getDependencies());
    if ($cardinality === 1) {
      $this->assertFalse($field->isMultiple());
    }
    else {
      $this->assertTrue($field->isMultiple());
    }
    $this->assertSame($cardinality, $field->getCardinality());
  }

  /**
   * Test attribute field storage migration.
   */
  public function testAttribute() {
    $dependencies = [
      'module' => ['commerce_product'],
    ];
    $this->assertEntity('commerce_product_variation.attribute_design', 'entity_reference', TRUE, 1, $dependencies);
    $this->assertEntity('commerce_product_variation.attribute_color', 'entity_reference', TRUE, -1, $dependencies);
    $this->assertEntity('commerce_product_variation.attribute_model_size_attribute', 'entity_reference', TRUE, 1, $dependencies);
    $this->assertEntity('commerce_product_variation.attribute_name', 'entity_reference', TRUE, 1, $dependencies);
  }

}
