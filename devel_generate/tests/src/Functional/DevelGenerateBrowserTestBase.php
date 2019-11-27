<?php

namespace Drupal\Tests\devel_generate\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\devel_generate\Traits\DevelGenerateSetupTrait;

/**
 * Base class for devel_generate functional browser tests.
 *
 * DevelGenerateCommandsTest should not extend this class so that it can remain
 * independent and be used as a cut-and-paste example for other developers.
 */
abstract class DevelGenerateBrowserTestBase extends BrowserTestBase {

  use DevelGenerateSetupTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'devel',
    'devel_generate',
    'menu_ui',
    'node',
    'comment',
    'taxonomy',
    'path',
  ];

  /**
   * Prepares the testing environment.
   */
  public function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(['administer devel_generate']);
    $this->drupalLogin($admin_user);
    $this->setUpData();
  }

}