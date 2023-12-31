<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests field instance widget settings migration.
 *
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class FieldInstanceWidgetSettingsTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'datetime',
    'file',
    'image',
    'link',
    'menu_ui',
    'migrate_plus',
    'node',
    'path',
    'system',
    'taxonomy',
    'telephone',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $db = Database::getConnection('default', 'migrate');

    // Add a field instance to a node that uses the
    // commerce_product_reference_autocomplete widget.
    $row = $db->select('field_config_instance', 'fci')
      ->fields('fci')
      ->condition('entity_type', 'node')
      ->condition('bundle', 'hats')
      ->condition('field_name', 'field_product')
      ->execute()->fetchAssoc();
    unset($row['id']);
    $row['bundle'] = 'blog_post';
    $row['data'] = 'a:7:{s:13:"default_value";N;s:11:"description";s:0:"";s:7:"display";a:2:{s:7:"default";a:5:{s:5:"label";s:5:"above";s:6:"module";s:13:"commerce_cart";s:8:"settings";a:5:{s:7:"combine";b:1;s:16:"default_quantity";i:1;s:14:"line_item_type";s:7:"product";s:13:"show_quantity";b:0;s:30:"show_single_product_attributes";b:0;}s:4:"type";s:30:"commerce_cart_add_to_cart_form";s:6:"weight";i:2;}s:7:"display";a:5:{s:5:"label";s:6:"hidden";s:6:"module";s:13:"commerce_cart";s:8:"settings";a:5:{s:7:"combine";b:1;s:16:"default_quantity";i:1;s:14:"line_item_type";s:7:"product";s:13:"show_quantity";b:0;s:30:"show_single_product_attributes";b:0;}s:4:"type";s:30:"commerce_cart_add_to_cart_form";s:6:"weight";i:0;}}s:5:"label";s:7:"Product";s:8:"required";b:1;s:8:"settings";a:3:{s:15:"field_injection";b:1;s:19:"referenceable_types";a:0:{}s:18:"user_register_form";b:0;}s:6:"widget";a:4:{s:6:"module";s:26:"commerce_product_reference";s:8:"settings";a:3:{s:18:"autocomplete_match";s:8:"contains";s:17:"autocomplete_path";s:29:"commerce_product/autocomplete";s:4:"size";i:60;}s:4:"type";s:39:"commerce_product_reference_autocomplete";s:6:"weight";i:0;}}';
    $db->insert('field_config_instance')->fields($row)->execute();

    $this->migrateFields();
    $this->executeMigration('d7_field_instance_widget_settings');
  }

  /**
   * Asserts various aspects of a form display entity.
   *
   * @param string $id
   *   The entity ID.
   * @param string $expected_entity_type
   *   The expected entity type to which the display settings are attached.
   * @param string $expected_bundle
   *   The expected bundle to which the display settings are attached.
   */
  protected function assertEntity($id, $expected_entity_type, $expected_bundle) {
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity */
    $entity = EntityFormDisplay::load($id);
    $this->assertInstanceOf(EntityFormDisplayInterface::class, $entity);
    $this->assertSame($expected_entity_type, $entity->getTargetEntityTypeId());
    $this->assertSame($expected_bundle, $entity->getTargetBundle());
  }

  /**
   * Asserts various aspects of a particular component of a form display.
   *
   * @param string $display_id
   *   The form display ID.
   * @param string $component_id
   *   The component ID.
   * @param string $widget_type
   *   The expected widget type.
   * @param string $weight
   *   The expected weight of the component.
   */
  protected function assertComponent($display_id, $component_id, $widget_type, $weight) {
    $component = EntityFormDisplay::load($display_id)->getComponent($component_id);
    $this->assertTrue(is_array($component));
    $this->assertSame($widget_type, $component['type']);
    $this->assertSame($weight, $component['weight']);
  }

  /**
   * Test that migrated view modes can be loaded using D8 APIs.
   */
  public function testWidgetSettings() {
    // Test comment widget settings.
    $this->assertEntity('comment.comment_node_ad_push.default', 'comment', 'comment_node_ad_push');
    $this->assertEntity('comment.comment_node_bags_cases.default', 'comment', 'comment_node_bags_cases');
    $this->assertEntity('comment.comment_node_blog_post.default', 'comment', 'comment_node_blog_post');
    $this->assertEntity('comment.comment_node_drinks.default', 'comment', 'comment_node_drinks');
    $this->assertEntity('comment.comment_node_hats.default', 'comment', 'comment_node_hats');
    $this->assertEntity('comment.comment_node_page.default', 'comment', 'comment_node_page');
    $this->assertEntity('comment.comment_node_shoes.default', 'comment', 'comment_node_shoes');
    $this->assertEntity('comment.comment_node_slideshow.default', 'comment', 'comment_node_slideshow');
    $this->assertEntity('comment.comment_node_storage_devices.default', 'comment', 'comment_node_storage_devices');
    $this->assertEntity('comment.comment_node_tops.default', 'comment', 'comment_node_tops');

    // Test commerce product widget settings.
    $this->assertEntity('commerce_product.bags_cases.default', 'commerce_product', 'bags_cases');
    $this->assertComponent('commerce_product.bags_cases.default', 'title', 'string_textfield', -5);
    $this->assertComponent('commerce_product.bags_cases.default', 'body', 'text_textarea_with_summary', 1);
    $this->assertComponent('commerce_product.bags_cases.default', 'path', 'path', 30);
    $this->assertEntity('commerce_product.drinks.default', 'commerce_product', 'drinks');
    $this->assertEntity('commerce_product.hats.default', 'commerce_product', 'hats');
    $this->assertEntity('commerce_product.shoes.default', 'commerce_product', 'shoes');
    $this->assertEntity('commerce_product.storage_devices.default', 'commerce_product', 'storage_devices');
    $this->assertEntity('commerce_product.tops.default', 'commerce_product', 'tops');

    // Test commerce product variation widget settings.
    $this->assertEntity('commerce_product_variation.bags_cases.default', 'commerce_product_variation', 'bags_cases');
    $this->assertComponent('commerce_product_variation.bags_cases.default', 'title', 'string_textfield', -5);
    $this->assertComponent('commerce_product_variation.bags_cases.default', 'field_images', 'image_image', 37);
    $this->assertComponent('commerce_product_variation.bags_cases.default', 'attribute_color', 'options_select', 39);
    $this->assertComponent('commerce_product_variation.bags_cases.default', 'attribute_bag_size', 'options_select', 40);
    $this->assertEntity('commerce_product_variation.drinks.default', 'commerce_product_variation', 'drinks');
    $this->assertEntity('commerce_product_variation.hats.default', 'commerce_product_variation', 'hats');
    $this->assertEntity('commerce_product_variation.shoes.default', 'commerce_product_variation', 'shoes');
    $this->assertEntity('commerce_product_variation.storage_devices.default', 'commerce_product_variation', 'storage_devices');
    $this->assertEntity('commerce_product_variation.tops.default', 'commerce_product_variation', 'tops');

    // Test node widget settings.
    $this->assertEntity('node.ad_push.default', 'node', 'ad_push');
    $this->assertComponent('node.ad_push.default', 'field_tagline', 'string_textfield', 2);
    $this->assertComponent('node.ad_push.default', 'field_image', 'image_image', 3);
    $this->assertComponent('node.ad_push.default', 'field_link', 'link_default', 4);
    $this->assertComponent('node.ad_push.default', 'path', 'path', 30);
    $this->assertEntity('node.bags_cases.default', 'node', 'bags_cases');
    $this->assertEntity('node.blog_post.default', 'node', 'blog_post');
    $this->assertComponent('node.blog_post.default', 'field_product', 'entity_reference_autocomplete', 0);
    $this->assertEntity('node.drinks.default', 'node', 'drinks');
    $this->assertEntity('node.hats.default', 'node', 'hats');

    $this->assertEntity('node.page.default', 'node', 'page');
    $this->assertComponent('node.page.default', 'body', 'text_textarea_with_summary', 31);
    $this->assertComponent('node.page.default', 'title', 'string_textfield', -5);
    $this->assertComponent('node.page.default', 'path', 'path', 30);

    $this->assertEntity('node.shoes.default', 'node', 'shoes');
    $this->assertEntity('node.slideshow.default', 'node', 'slideshow');
    $this->assertEntity('node.storage_devices.default', 'node', 'storage_devices');
    $this->assertEntity('node.tops.default', 'node', 'tops');

    // Test taxonomy tem  widget settings.
    $this->assertEntity('taxonomy_term.category.default', 'taxonomy_term', 'category');
    $this->assertEntity('taxonomy_term.collection.default', 'taxonomy_term', 'collection');
    $this->assertEntity('taxonomy_term.color.default', 'taxonomy_term', 'color');

    // Test there are no errors in the map table.
    $migration = $this->getMigration('d7_field_instance_widget_settings');
    $errors = $migration->getIdMap()->errorCount();
    $this->assertSame(0, $errors);
  }

}
