<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * DataCollector for HTTP Request.
 */
class RequestDataCollector extends BaseRequestDataCollector {

  use DataCollectorTrait;

  /**
   * The Controller resolver service.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  private $controllerResolver;

  /**
   * RequestDataCollector constructor.
   *
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controllerResolver
   *   The Controller resolver service.
   */
  public function __construct(ControllerResolverInterface $controllerResolver) {
    parent::__construct();

    $this->controllerResolver = $controllerResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response/*, \Throwable $exception = null*/) {
    parent::collect($request, $response);

    $controller = $this->controllerResolver->getController($request);

    $this->data['controller'] = $this->getMethodData($controller[0], $controller[1]);
  }

}
