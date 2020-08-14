<?php

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\webprofiler\Csp\ContentSecurityPolicyHandler;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ProfilerController.
 */
class ProfilerController extends ControllerBase {

  private $generator;

  private $profiler;

  private $renderer;

  private $templateManager;

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

  public function __construct(UrlGeneratorInterface $generator, Profiler $profiler, RendererInterface $renderer, TemplateManager $templateManager, ContentSecurityPolicyHandler $cspHandler) {
    $this->generator = $generator;
    $this->profiler = $profiler;
    $this->renderer = $renderer;
    $this->templateManager = $templateManager;
    $this->cspHandler = $cspHandler;
  }

  /**
   * Renders the Web Debug Toolbar.
   *
   * @param Request $request
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
    } catch (\Exception $e) {
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
  public function panelAction($token, $name) {
    if ('empty' === $token || NULL === $token || NULL === $name) {
      return new JsonResponse('');
    }

    $this->profiler->disable();

    if (!$profile = $this->profiler->loadProfile($token)) {
      return new JsonResponse('');
    }

    $templates = $this->templateManager->getNames($profile);
    $template = $templates[$name];

    $content = [
      '#theme' => 'webprofiler_panel',
      '#profile' => $profile,
      '#template' => $template,
      '#name' => $name,
    ];

    $response = new Response('', 200, ['Content-Type' => 'text/html']);
    $response->setContent($this->renderer->renderRoot($content));

    return $response;
  }

}
