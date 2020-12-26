<?php

declare(strict_types=1);

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webprofiler\Panel\RequestPanel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Controller for the Webprofiler dashboard.
 */
class DashboardController extends ControllerBase {

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('webprofiler.profiler')
    );
  }

  /**
   * DashboardController constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The Profiler service.
   */
  final public function __construct(Profiler $profiler) {
    $this->profiler = $profiler;
  }

  /**
   * Controller for the whole dashboard page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A Request.
   *
   * @return array
   *   A render array for webprofiler_dashboard theme.
   */
  public function dashboard(Request $request) {
    $this->profiler->disable();

    $token = $request->get('token');

    /** @var \Symfony\Component\HttpKernel\DataCollector\DataCollector $el */
    $collectors = array_filter($this->profiler->all(), function ($el) {
      return [
        'name' => $el->getName(),
      ];
    });

    return [
      '#theme' => 'webprofiler_dashboard',
      '#collectors' => $collectors,
      '#token' => $token,
      '#attached' => [
        'library' => [
          'webprofiler/dashboard',
        ],
      ],
    ];
  }

  /**
   * Renders a profiler panel for the given token and type.
   *
   * @param string $token
   *   The profiler token.
   * @param string $name
   *   The panel name to render.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A Response instance.
   */
  public function panel($token, $name) {
    if ('empty' === $token || NULL === $token || NULL === $name) {
      return new JsonResponse('');
    }

    $this->profiler->disable();

    if (!$profile = $this->profiler->loadProfile($token)) {
      return new JsonResponse('');
    }

    $panel = new RequestPanel();

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#js-webprofiler-panel', $panel->render($token, $name)));

    return $response;
  }

}
