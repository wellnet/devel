<?php

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\webprofiler\Csp\ContentSecurityPolicyHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class WebDebugToolbarListener.
 */
class WebDebugToolbarListener implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @var \Drupal\webprofiler\Csp\ContentSecurityPolicyHandler
   */
  private $cspHandler;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  public function __construct(RendererInterface $renderer, AccountInterface $currentUser, UrlGeneratorInterface $urlGenerator, ContentSecurityPolicyHandler $cspHandler, ConfigFactoryInterface $config) {
    $this->renderer = $renderer;
    $this->currentUser = $currentUser;
    $this->urlGenerator = $urlGenerator;
    $this->cspHandler = $cspHandler;
    $this->config = $config->get('webprofiler.settings');
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   */
  public function onKernelResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();

    if ($response->headers->has('X-Debug-Token') && NULL !== $this->urlGenerator) {
      try {
        $response->headers->set(
          'X-Debug-Token-Link',
          $this->urlGenerator->generate('webprofiler.toolbar', ['token' => $response->headers->get('X-Debug-Token')], UrlGeneratorInterface::ABSOLUTE_URL)
        );
      } catch (\Exception $e) {
        $response->headers->set('X-Debug-Error', \get_class($e) . ': ' . preg_replace('/\s+/', ' ', $e->getMessage()));
      }
    }

    if (!$event->isMasterRequest()) {
      return;
    }

    $nonces = $this->cspHandler ? $this->cspHandler->updateResponseHeaders($request, $response) : [];

    // do not capture redirects or modify XML HTTP Requests
    if ($request->isXmlHttpRequest()) {
      return;
    }

    if ($response->headers->has('X-Debug-Token') && $response->isRedirect() && $this->config->get('intercept_redirects') && 'html' === $request->getRequestFormat()) {
      $session = $request->getSession();
      if (NULL !== $session && $session->isStarted() && $session->getFlashBag() instanceof AutoExpireFlashBag) {
        // keep current flashes for one more request if using AutoExpireFlashBag
        $session->getFlashBag()->setAll($session->getFlashBag()->peekAll());
      }

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
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param array $nonces
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

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse', -128],
    ];
  }

}
