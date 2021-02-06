<?php

namespace Drupal\Tests\webprofiler\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Class WebprofilerTestBase.
 *
 * @group webprofiler
 * @package Drupal\Tests\webprofiler\FunctionalJavascript
 */
abstract class WebprofilerTestBase extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Flush cache.
   */
  protected function flushCache() {
    $module_handler = \Drupal::moduleHandler();
    $module_handler->invokeAll('cache_flush');
  }

  /**
   * Login with a user that can see the toolbar and the dashboard.
   */
  protected function loginForDashboard() {
    $admin_user = $this->drupalCreateUser(
      [
        'view webprofiler toolbar',
        'access webprofiler',
      ]
    );
    $this->drupalLogin($admin_user);
  }

  /**
   * Login with a user that can see the toolbar.
   */
  protected function loginForToolbar() {
    $admin_user = $this->drupalCreateUser(
      [
        'view webprofiler toolbar',
      ]
    );
    $this->drupalLogin($admin_user);
  }

  /**
   * Wait until the toolbar is present on page.
   */
  protected function waitForToolbar() {
    $assert_session = $this->assertSession();
    $assert_session->waitForElement('css', 'sf-toolbar');
  }

}
