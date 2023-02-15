<?php

namespace Drupal\Tests\new_relic_rpm\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests admin UI.
 *
 * @package Drupal\Tests\new_relic_rpm\Functional
 * @group new_relic_rpm
 */
class AdminUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['new_relic_rpm'];

  protected $defaultTheme = 'stark';

  /**
   * The WebAssert.
   *
   * @var \Drupal\Tests\WebAssert
   */
  private $assert;

  /**
   * The DocumentElement.
   *
   * @var \Behat\Mink\Element\DocumentElement
   */
  private $page;

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    $admin = $this->createUser([], NULL, TRUE);
    $admin->addRole('administrator');
    $admin->save();
    $this->drupalLogin($admin);

    if (!isset($this->assert) || !isset($this->page)) {
      $this->assert = $this->assertSession();
      $this->page = $this->getSession()->getPage();
    }
  }

  /**
   * Tests the settings page elements.
   */
  public function testSettingsPage() {
    $this->drupalGet('/admin/config/development/new-relic');
    $this->assert->statusCodeEquals(200);

    // General.
    $this->page->hasField('api_key');

    // Transactions.
    $this->page->hasField('track_drush');
    $this->page->hasField('track_cron');
    $this->page->hasField('ignore_roles[]');
    $this->page->hasField('ignore_urls');
    $this->page->hasField('bg_urls');
    $this->page->hasField('exclusive_urls');

    // Error analytics.
    $this->page->hasField('watchdog_severities[]');
    $this->page->hasField('override_exception_handler');

    // Deployment.
    $this->page->hasField('module_deployment');
    $this->page->hasField('config_import');

    // Insight.
    $this->page->hasField('views_log_slow');
    $this->page->hasField('views_log_threshold');
  }

}
