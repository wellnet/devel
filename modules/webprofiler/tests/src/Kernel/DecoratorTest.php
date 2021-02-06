<?php

namespace Drupal\Tests\webprofiler\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class DecoratorTest.
 *
 * @group webprofiler
 * @package Drupal\Tests\webprofiler\Kernel
 */
class DecoratorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'views'];

  /**
   * Tests the Entity Type Manager service decoration.
   *
   * @param string $service
   *   The service name.
   * @param string $original
   *   The original class.
   * @param string $decorated
   *   The decorated class.
   *
   * @dataProvider decorators
   */
  public function testEntityTypeDecorator($service, $original, $decorated) {
    $entityTypeManagerOriginal = $this->container->get($service);

    $this->assertInstanceOf($original, $entityTypeManagerOriginal);

    $this->container->get('module_installer')->install(['webprofiler']);

    $entityTypeManagerDecorated = $this->container->get($service);

    $this->assertInstanceOf($decorated, $entityTypeManagerDecorated);
  }

  /**
   * DataProvider for testEntityTypeDecorator.
   *
   * @return array
   *   The array of values to run tests on.
   */
  public function decorators() {
    return [
      ['entity_type.manager', 'Drupal\Core\Entity\EntityTypeManager', 'Drupal\webprofiler\Entity\EntityTypeManagerWrapper'],
    ];
  }

}
