<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

/**
 * Tests cart settings migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class OrderSettingsTest extends Commerce1TestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_number_pattern',
    'commerce_store',
    'commerce_order',
    'commerce_price',
    'commerce_store',
    'path',
    'profile',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_store');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('profile');
    $this->installSchema('commerce_number_pattern', ['commerce_number_pattern_sequence']);
    $this->installConfig('commerce_order');
    $this->executeMigration('commerce1_cart_settings');
  }

  /**
   * Tests migration of tracker's variables to configuration.
   */
  public function testMigration() {
    $order_settings = $this->config('commerce_order.commerce_order_type.default');
    $this->assertSame('customer', $order_settings->get('refresh_mode'));
    $this->assertSame(12, $order_settings->get('refresh_frequency'));
  }

}
