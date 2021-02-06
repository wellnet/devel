<?php

namespace Drupal\Tests\webprofiler\Unit\DataCollector;

use Drupal\Tests\UnitTestCase;

/**
 * Class DataCollectorBaseTest.
 *
 * @group webprofiler
 * @package Drupal\Tests\webprofiler\Unit\DataCollector
 */
abstract class DataCollectorBaseTest extends UnitTestCase {

  /**
   * Mock Rquest.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Mock Response.
   *
   * @var \Symfony\Component\HttpFoundation\Response
   */
  protected $response;

  /**
   * Mock Exception.
   *
   * @var \Exception
   */
  protected $exception;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $this->response = $this->createMock('Symfony\Component\HttpFoundation\Response');
    $this->exception = $this->createMock('Exception');
  }

}
