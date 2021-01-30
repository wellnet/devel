<?php

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\webprofiler\Csp\ContentSecurityPolicyHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listen to kernel response event to inject the toolbar.
 */
class WebDebugToolbarListener implements EventSubscriberInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The url generator service.
   *
   * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The Content-Security-Policy handler service.
   *
   * @var \Drupal\webprofiler\Csp\ContentSecurityPolicyHandler
   */
  private $cspHandler;

  /**
   * An immutable config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * WebDebugToolbarListener constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
   *   The url generator service.
   * @param \Drupal\webprofiler\Csp\ContentSecurityPolicyHandler $cspHandler
   *   The Content-Security-Policy handler service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   */
  public function __construct(RendererInterface $renderer, AccountInterface $currentUser, UrlGeneratorInterface $urlGenerator, ContentSecurityPolicyHandler $cspHandler, ConfigFactoryInterface $config) {
    $this->renderer = $renderer;
    $this->currentUser = $currentUser;
    $this->urlGenerator = $urlGenerator;
    $this->cspHandler = $cspHandler;
    $this->config = $config->get('webprofiler.settings');
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse', -128],
    ];
  }

  /**
   * Listen for the kernel.response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   A response event.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();

    if ($response->headers->has('X-Debug-Token') && NULL !== $this->urlGenerator) {
      try {
        $response->headers->set(
          'X-Debug-Token-Link',
          $this->urlGenerator->generate('webprofiler.toolbar', ['token' => $response->headers->get('X-Debug-Token')], UrlGeneratorInterface::ABSOLUTE_URL)
        );
      }
      catch (\Exception $e) {
        $response->headers->set('X-Debug-Error', \get_class($e) . ': ' . preg_replace('/\s+/', ' ', $e->getMessage()));
      }
    }

    if (!$event->isMasterRequest()) {
      return;
    }

    $nonces = $this->cspHandler ? $this->cspHandler->updateResponseHeaders($request, $response) : [];

    // Do not capture redirects or modify XML HTTP Requests.
    if ($request->isXmlHttpRequest()) {
      return;
    }

    if ($response->headers->has('X-Debug-Token') && $response->isRedirect() && $this->config->get('intercept_redirects') && 'html' === $request->getRequestFormat()) {
      $toolbarRedirect = [
        '#theme' => 'webprofiler_toolbar_redirect',
        '#location' => $response->headers->get('Location'),
      ];

      $response->setContent($this->renderer->renderRoot($toolbarRedirect));
      $response->setStatusCode(200);
      $response->headers->remove('Location');
    }

    if (!$response->headers->has('X-Debug-Token')
      || $response->isRedirection()
      || ($response->headers->has('Content-Type') && FALSE === strpos($response->headers->get('Content-Type'), 'html'))
      || 'html' !== $request->getRequestFormat()
      || FALSE !== stripos($response->headers->get('Content-Disposition'), 'attachment;')
    ) {
      return;
    }

    if ($this->currentUser->hasPermission('view webprofiler toolbar')) {
      $this->injectToolbar($response, $request, $nonces);
    }
  }

  /**
   * Injects the web debug toolbar into the given Response.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   A response.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request.
   * @param array $nonces
   *   Nonces used in Content-Security-Policy header.
   */
  protected function injectToolbar(Response $response, Request $request, array $nonces) {
    $content = $response->getContent();
    $pos = strripos($content, '</body>');

    if (FALSE !== $pos) {
      $toolbarJs = [
        '#theme' => 'webprofiler_toolbar_js',
        '#token' => $response->headers->get('X-Debug-Token'),
        '#request' => $request,
        '#csp_script_nonce' => isset($nonces['csp_script_nonce']) ? $nonces['csp_script_nonce'] : NULL,
        '#csp_style_nonce' => isset($nonces['csp_style_nonce']) ? $nonces['csp_style_nonce'] : NULL,
      ];

      $toolbar = "\n" . str_replace("\n", '', $this->renderer->renderRoot($toolbarJs)) . "\n";
      $content = substr($content, 0, $pos) . $toolbar . substr($content, $pos);
      $response->setContent($content);
    }
  }

}
