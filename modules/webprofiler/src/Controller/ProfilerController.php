<?php

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\webprofiler\Csp\ContentSecurityPolicyHandler;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Controller for the Webprofiler toolbar.
 */
class ProfilerController extends ControllerBase {

  /**
   * The Url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  private $generator;

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * The Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * The Template manager service.
   *
   * @var \Drupal\webprofiler\Profiler\TemplateManager
   */
  private $templateManager;

  /**
   * The Content-Security-Policy service.
   *
   * @var \Drupal\webprofiler\Csp\ContentSecurityPolicyHandler
   */
  private $cspHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('url_generator'),
      $container->get('webprofiler.profiler'),
      $container->get('renderer'),
      $container->get('webprofiler.template_manager'),
      $container->get('webprofiler.csp')
    );
  }

  /**
   * ProfilerController constructor.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $generator
   *   The Url generator service.
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The Profiler service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The Renderer service.
   * @param \Drupal\webprofiler\Profiler\TemplateManager $templateManager
   *   The Template manager service.
   * @param \Drupal\webprofiler\Csp\ContentSecurityPolicyHandler $cspHandler
   *   The Content-Security-Policy service.
   */
  final public function __construct(UrlGeneratorInterface $generator, Profiler $profiler, RendererInterface $renderer, TemplateManager $templateManager, ContentSecurityPolicyHandler $cspHandler) {
    $this->generator = $generator;
    $this->profiler = $profiler;
    $this->renderer = $renderer;
    $this->templateManager = $templateManager;
    $this->cspHandler = $cspHandler;
  }

  /**
   * Renders the Web Debug Toolbar.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current HTTP Request.
   * @param string $token
   *   The profiler token.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A Response instance.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function toolbarAction(Request $request, $token) {
    if ('empty' === $token || NULL === $token) {
      return new Response('', 200, ['Content-Type' => 'text/html']);
    }

    $this->profiler->disable();

    if (!$profile = $this->profiler->loadProfile($token)) {
      return new Response('', 404, ['Content-Type' => 'text/html']);
    }

    $url = NULL;
    try {
      $url = $this->generator->generate('webprofiler.toolbar', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
    }
    catch (\Exception $e) {
      // The profiler is not enabled.
    }

    $response = new Response('', 200, ['Content-Type' => 'text/html']);
    $nonces = $this->cspHandler ? $this->cspHandler->getNonces($request, $response) : [];

    $toolbar = [
      '#theme' => 'webprofiler_toolbar',
      '#request' => $request,
      '#profile' => $profile,
      '#templates' => $this->templateManager->getNames($profile),
      '#profiler_url' => $url,
      '#token' => $token,
      '#csp_script_nonce' => isset($nonces['csp_script_nonce']) ? $nonces['csp_script_nonce'] : NULL,
      '#csp_style_nonce' => isset($nonces['csp_style_nonce']) ? $nonces['csp_style_nonce'] : NULL,
    ];

    $response->setContent($this->renderer->renderRoot($toolbar));

    return $response;
  }

}
