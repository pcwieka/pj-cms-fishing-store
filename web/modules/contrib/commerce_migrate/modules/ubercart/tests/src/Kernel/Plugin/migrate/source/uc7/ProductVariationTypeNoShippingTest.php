<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Plugin\migrate\source\uc7;

use Drupal\Tests\migrate\Kernel\MigrateSqlSourceTestBase;

/**
 * Tests product variation type source plugin without commerce_shipping enabled.
 *
 * @covers \Drupal\commerce_migrate_ubercart\Plugin\migrate\source\uc7\ProductVariationType
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class ProductVariationTypeNoShippingTest extends MigrateSqlSourceTestBase {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_migrate_ubercart',
    'migrate_drupal',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  public function providerSource() {
    $tests = [];

    // The source data.
    $tests[0]['source_data']['node'] = [
      [
        'nid' => 1,
        'vid' => 1,
        'type' => 'page',
        'language' => 'en',
        'title' => 'node title 1',
        'uid' => 1,
        'status' => 1,
        'created' => 1279051598,
        'changed' => 1279051598,
        'comment' => 2,
        'promote' => 1,
        'sticky' => 0,
        'tnid' => 0,
        'translate' => 0,
      ],
      [
        'nid' => 2,
        'vid' => 2,
        'type' => 'product',
        'language' => 'en',
        'title' => 'product 2',
        'uid' => 1,
        'status' => 1,
        'created' => 1279290908,
        'changed' => 1279308993,
        'comment' => 0,
        'promote' => 1,
        'sticky' => 0,
        'tnid' => 0,
        'translate' => 0,
      ],
      [
        'nid' => 6,
        'vid' => 6,
        'type' => 'product_kit',
        'language' => 'en',
        'title' => 'product 5',
        'uid' => 1,
        'status' => 1,
        'created' => 1279291908,
        'changed' => 1279309993,
        'comment' => 0,
        'promote' => 1,
        'sticky' => 0,
        'tnid' => 6,
        'translate' => 0,
      ],
    ];
    $tests[0]['source_data']['node_type'] = [
      [
        'type' => 'page',
        'name' => 'Page',
        'base' => 'node_content',
        'module' => 'node',
        'description' => 'A page',
        'help' => '',
        'title_label' => 'Title',
        'custom' => 1,
        'modified' => 0,
        'locked' => 0,
        'disabled' => 0,
        'orig_type' => 'page',
      ],
      [
        'type' => 'product',
        'name' => 'Product',
        'base' => 'uc_product',
        'module' => 'uc_product',
        'description' => 'product',
        'help' => '',
        'title_label' => 'Title',
        'custom' => 1,
        'modified' => 0,
        'locked' => 0,
        'disabled' => 0,
        'orig_type' => 'product',
      ],
      [
        'type' => 'product_kit',
        'name' => 'Product Kit',
        'base' => 'uc_product',
        'module' => 'uc_product_kit',
        'description' => 'A product group',
        'help' => '',
        'title_label' => 'Title',
        'custom' => 1,
        'modified' => 0,
        'locked' => 0,
        'disabled' => 0,
        'orig_type' => 'product_kit',
      ],
    ];
    $tests[0]['source_data']['uc_products'] = [
      [
        'vid' => '2',
        'nid' => '2',
        'model' => 'Hat',
        'list_price' => '1.00',
        'cost' => '0.50',
        'sell_price' => '2.00',
        'weight' => '1',
        'weight_units' => 'gm',
        'length' => '20',
        'width' => '11',
        'height' => '10',
        'length_units' => 'cm',
        'pkg_qty' => '1',
        'default_qty' => '1',
        'unique_hash' => 'hash',
        'ordering' => 0,
        'shippable' => '1',
      ],
      [
        'vid' => '6',
        'nid' => '6',
        'model' => 'Ship',
        'list_price' => '1.00',
        'cost' => '0.50',
        'sell_price' => '3.00',
        'weight' => '0',
        'weight_units' => 'gm',
        'length' => '0',
        'width' => '0',
        'height' => '0',
        'length_units' => 'cm',
        'pkg_qty' => '1',
        'default_qty' => '1',
        'unique_hash' => 'hash',
        'ordering' => 0,
        'shippable' => '1',
      ],
    ];

    // The expected results.
    $tests[0]['expected_data'] = [
      [
        'type' => 'product',
        'name' => 'Product',
        'base' => 'uc_product',
        'module' => 'uc_product',
        'description' => 'product',
        'help' => '',
        'title_label' => 'Title',
        'custom' => '1',
        'modified' => 0,
        'locked' => 0,
        'disabled' => 0,
        'orig_type' => 'product',
      ],
      [
        'type' => 'product_kit',
        'name' => 'Product Kit',
        'base' => 'uc_product',
        'module' => 'uc_product_kit',
        'description' => 'A product group',
        'help' => '',
        'title_label' => 'Title',
        'custom' => '1',
        'modified' => 0,
        'locked' => 0,
        'disabled' => 0,
        'orig_type' => 'product_kit',
      ],
    ];

    return $tests;
  }

}
