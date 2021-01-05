<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\webprofiler\Panel\PanelInterface;
use Drupal\webprofiler\Panel\RequestPanel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * DataCollector for HTTP Request.
 */
class RequestDataCollector extends BaseRequestDataCollector implements DrupalDataCollectorInterface {

  use DataCollectorTrait;

  public const SERVICE_ID = 'service_id';

  public const CALLABLE = 'callable';

  /**
   * The Controller resolver service.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  private ControllerResolverInterface $controllerResolver;

  /**
   * The list of access checks applied to this request.
   *
   * @var array
   */
  private array $accessChecks;

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
  public function collect(
    Request $request,
    Response $response
    /*, \Throwable $exception = null*/
  ) {
    parent::collect($request, $response);

    if ($controller = $this->controllerResolver->getController($request)) {
      $this->data['controller'] = $this->getMethodData(
        $controller[0], $controller[1]
      );
      $this->data['access_checks'] = $this->accessChecks;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): PanelInterface {
    return new RequestPanel();
  }

  /**
   * Save an access check.
   *
   * @param string $service_id
   *   The service id of the service implementing the access check.
   * @param array $callable
   *   The callable that implement the access check.
   */
  public function addAccessCheck(
    string $service_id,
    array $callable
  ) {
    $this->accessChecks[] = [
      self::SERVICE_ID => $service_id,
      self::CALLABLE => $this->getMethodData($callable[0], $callable[1]),
    ];
  }

  /**
   * Return the list of access checks as ParameterBag.
   *
   * @return \Symfony\Component\HttpFoundation\ParameterBag
   *   The list of access checks.
   */
  public function getAccessChecks(): ParameterBag {
    return new ParameterBag($this->data['access_checks']->getValue());
  }

}
