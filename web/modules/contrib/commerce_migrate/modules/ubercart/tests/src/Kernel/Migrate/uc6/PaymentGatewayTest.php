<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests payment gateway migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class PaymentGatewayTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'commerce_order',
    'commerce_price',
    'commerce_payment',
    'commerce_store',
    'profile',
    'state_machine',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('uc_payment_gateway');
  }

  /**
   * Tests payment gateway migration.
   */
  public function testPaymentGateway() {
    $this->assertPaymentGatewayEntity('check', 'Check', NULL);
  }

}
