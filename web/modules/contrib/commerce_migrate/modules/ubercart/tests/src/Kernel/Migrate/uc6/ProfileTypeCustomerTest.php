<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests customer profile type migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class ProfileTypeCustomerTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['profile'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('profile');
    $this->executeMigration('uc_profile_type');
  }

  /**
   * Test profile migration.
   */
  public function testProfileType() {
    $this->assertProfileType('customer', 'Customer', FALSE, FALSE);
  }

}
