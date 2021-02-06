<?php

namespace Drupal\Tests\webprofiler\FunctionalJavascript;

/***
 * Tests the JavaScript functionality of webprofiler.
 *
 * @group webprofiler
 * @package Drupal\Tests\webprofiler\FunctionalJavascript
 */
class ToolbarTest extends WebprofilerTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['webprofiler', 'node'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node')
      ->save(TRUE);
  }

  /**
   * Tests if the toolbar appears on front page.
   */
  public function testToolbarOnFrontPage() {
    $this->loginForToolbar();
    $this->drupalGet('<front>');
    $this->waitForToolbar();

    $assert = $this->assertSession();
    $assert->elementExists('css', '.sf-toolbar');
  }

  /**
   * Tests the toolbar report page.
   */
  public function testToolbarReportPage() {
    $this->loginForDashboard();
    $this->drupalGet('<front>');

    $this->drupalGet('admin/reports/profiler/list');

    $assert = $this->assertSession();
    $assert->responseContains('Webprofiler');
    $assert->responseContains('Token');
  }

  /**
   * Tests the toolbar not appears on excluded path.
   */
  public function testToolbarNotAppearsOnExcludedPath() {
    $this->loginForDashboard();
    $this->drupalGet('admin/config/development/devel');
    $this->waitForToolbar();

    $assert = $this->assertSession();
    $assert->elementExists('css', '.sf-toolbar');

    $this->config('webprofiler.settings')
      ->set('exclude_paths', '/admin/config/development/devel')
      ->save();
    $this->drupalGet('admin/config/development/devel');
    $this->assertSession()->elementNotExists('css', '.sf-toolbar');
  }

}
