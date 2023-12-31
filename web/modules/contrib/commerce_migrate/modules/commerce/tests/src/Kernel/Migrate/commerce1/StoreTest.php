<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests store migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class StoreTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_price',
    'commerce_store',
    'path',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->migrateStore();
  }

  /**
   * Test store migration from Drupal 7 to 8.
   */
  public function testStore() {
    $this->assertStoreEntity(1, 'Commerce Kickstart', 'CommerceKickstart@example.com', 'USD', 'online', '1', TRUE);
  }

}
